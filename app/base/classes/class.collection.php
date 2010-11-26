<?php
  class Collection implements Iterator, Countable, ArrayAccess {
    private $dbConn;
    private $strTablePrefix;

    private $strModelName;
    private $objResultsFilter;

    private $objSchema;

    private $arrMembers = array();
    private $intPosition = 0;
    private $blnJustUnsetCurrent = false;

    public function __construct (ResultFilter $objResultsFilter) {
      $this->objResultsFilter = $objResultsFilter;
      $this->strModelName = $this->objResultsFilter->getModelName();
      $this->objSchema = new Schema(strtolower($this->strModelName));

      $objEnvConfig = Config::get('environment');

      try {
        $objDBConfig = Config::get($objEnvConfig->dbconfig);
      } catch (ConfigSettingNotFoundException $exException) {
        $objDBConfig = Config::get('db');
      }//try

      $strClassName = $objDBConfig->driver . "Driver";
      $this->dbConn = new $strClassName($objDBConfig->host, $objDBConfig->user, $objDBConfig->pass, $objDBConfig->name);

      $this->objResultsFilter->setDBConn($this->dbConn);

      $this->strTablePrefix = $objDBConfig->prefix;

      $this->fetch();
    }//function

    private function fetch () {
      $strModelName = $this->strModelName;

      $strSQL = "SELECT * FROM `" . $this->strTablePrefix . $this->objSchema->getTableName() . "`";

      $strSQL = trim($strSQL) . " " . $this->objResultsFilter->getConditionString();
      $strSQL = trim($strSQL) . " " . $this->objResultsFilter->getOrderByString();
      $strSQL = trim($strSQL) . " " . $this->objResultsFilter->getLimitString();

      $strSQL .= ";";

      echo $strSQL;

      $dbResults = $this->dbConn->query($strSQL);

      $arrMembers = array();

      while ($arrResult = $dbResults->fetch_assoc()) {
        $objModel = new $strModelName;
        $objModel->loadFromDBArray($arrResult);
        $arrMembers[] = $objModel;
      }//while

      $this->arrMembers = $arrMembers;
    }//function

    private function addModel ($objModel) {
      $this->arrMembers[] = $objModel;
    }//function

    private function prepareData ($strData, $strFieldName) {
      $strDataType = $this->objSchema->getDataType($strFieldName);

      switch ($strDataType) {
        case 'int':
        case 'tinyint':
        case 'decimal':
          return $strData;
          break;

        case 'date':
        case 'datetime':
        case 'text':
        case 'varchar':
          return "'" . $this->dbConn->escape_string($strData) . "'";
          break;
      }//switch
    }//function

    /************************************/
    /* Abstract Methods from Interfaces */
    /************************************/

    /* Iterator */

    public function current () {
      return current($this->arrMembers);
    }//function

    public function key () {
      return key($this->arrMembers);
    }//function

    public function next () {
      if ($this->blnJustUnsetCurrent) {
        $this->blnJustUnsetCurrent = false;
        return current($this->arrMembers);
      } else {
        return next($this->arrMembers);
      }//if
    }//function

    public function rewind () {
      return reset($this->arrMembers);
    }//function

    public function valid () {
      return (isset($this->arrMembers[$this->key()]));
    }//function

    /* Countable */

    public function count () {
      return count($this->arrMembers);
    }//function

    /* ArrayAccess */

    public function offsetSet ($mixOffset, $mixValue) {
      if (is_null($mixOffset)) {
        $this->addModel($mixValue);
      } else {
        $this->arrMembers[$mixOffset] = $mixValue;
      }//if
    }//function

    public function offsetExists ($mixOffset) {
      return isset($this->arrMembers[$mixOffset]);
    }//function

    public function offsetUnset ($mixOffset) {
      if ($this->key() == $mixOffset) {
        $this->blnJustUnsetCurrent = true;
      }//if

      unset($this->arrMembers[$mixOffset]);
    }//function

    public function offsetGet ($mixOffset) {
      return $this->arrMembers[$mixOffset];
    }//function
  }//class