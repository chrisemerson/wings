<?php
  class Collection implements Iterator, Countable, ArrayAccess {
    private $dbConn;
    private $strTablePrefix;

    private $strModelName;

    private $objSchema;

    private $arrWhere = array();
    private $arrNulls = array();
    private $arrOrderBy = array();
    private $intLimit = 0;

    private $arrMembers = array();
    private $intPosition = 0;
    private $blnJustUnsetCurrent = false;

    public function __construct ($strModelName) {
      $this->strModelName = $strModelName;
      $this->objSchema = new Schema(strtolower($strModelName));

      $objEnvConfig = Config::get('environment');

      try {
        $objDBConfig = Config::get($objEnvConfig->dbconfig);
      } catch (ConfigSettingNotFoundException $exException) {
        $objDBConfig = Config::get('db');
      }//try

      $strClassName = $objDBConfig->driver . "Driver";
      $this->dbConn = new $strClassName($objDBConfig->host, $objDBConfig->user, $objDBConfig->pass, $objDBConfig->name);

      $this->strTablePrefix = $objDBConfig->prefix;
    }//function

    public function fetch () {
      $arrWhere = $this->arrWhere;
      $arrNulls = $this->arrNulls;
      $arrOrderBy = $this->arrOrderBy;

      $strModelName = $this->strModelName;

      $strSQL = "SELECT * FROM `" . $this->strTablePrefix . $this->objSchema->getTableName() . "`";

      if (count($arrWhere) > 0) {
        $strSQL .= " WHERE ";

        $arrWhereStrings = array();

        foreach ($arrWhere as $strFieldName => $mixData) {
          $arrWhereStrings[] .= "`" . $strFieldName . "` = " . $this->prepareData($mixData, $strFieldName);
        }//foreach

        foreach ($arrNulls as $strFieldName) {
          $arrWhereStrings[] .= "`" . $strFieldName . "` IS NULL";
        }//foreach

        $strSQL .= implode(" AND ", $arrWhereStrings);
      }//if

      if (count($arrOrderBy) > 0) {
        $strSQL .= " ORDER BY ";

        $arrOrderByStrings = array();

        foreach ($arrOrderBy as $arrOrderByInfo) {
          $strOrderByString = $arrOrderByInfo['field'] . " ";

          if ($arrOrderByInfo['dir'] == ORDER_BY_ASC) {
            $strOrderByString .= "ASC";
          } else if ($arrOrderByInfo['dir'] == ORDER_BY_DESC) {
            $strOrderByString .= "DESC";
          }//if

          $arrOrderByStrings[] = $strOrderByString;
        }//foreach

        $strSQL .= implode(", ", $arrOrderByStrings);
      }//if

      if ($this->intLimit != 0) {
        $strSQL .= " LIMIT " . $this->intLimit;
      }//if

      $strSQL .= ";";

      $dbResults = $this->dbConn->query($strSQL);

      $arrMembers = array();

      while ($arrResult = $dbResults->fetch_assoc()) {
        $objModel = new $strModelName;
        $objModel->loadFromDBArray($arrResult);
        $arrMembers[] = $objModel;
      }//while

      $this->arrMembers = $arrMembers;
    }//function

    public function addCondition ($strField, $mixValue) {
      if (is_null($mixValue)) {
        $this->arrNulls[] = $strField;
      } else {
        $this->arrWhere[$strField] = $mixValue;
      }//if
    }//function

    public function addOrderBy ($strField, $conDirection = ORDER_BY_ASC) {
      $this->arrOrderBy[] = array('field' => $strField, 'dir' => $conDirection);
    }//function

    public function setLimit ($intLimit) {
      $this->intLimit = $intLimit;
    }//function

    public function addModel ($objModel) {
      $this->arrMembers[] = $objModel;
    }//function

    public function asArray () {
      return $this->arrMembers;
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