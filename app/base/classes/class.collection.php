<?php
  class Collection extends Schema implements Iterator, Countable, ArrayAccess {
    private $objResultsFilter;

    private $arrMembers = array();
    private $intPosition = 0;
    private $blnJustUnsetCurrent = false;

    public function __construct ($mixParameter) {
      if ($mixParameter instanceof ResultsFilter) {
        $this->objResultsFilter = $mixParameter;

        $this->strModelName = $this->objResultsFilter->getModelName();
        parent::__construct();

        $this->objResultsFilter->setDBConn($this->dbConn);

        $this->fetch();
      } else if (is_string($mixParameter)) {
        $this->strModelName = $mixParameter;
        parent::__construct();
      }//if
    }//function

    public function __set ($strName, $strValue) {
      foreach ($this->arrMembers as $objModel) {
        $objModel->$strName = $strValue;
      }//foreach
    }//function

    public function save () {
      foreach ($this->arrMembers as $objModel) {
        $objModel->save();
      }//foreach
    }//function

    public function delete () {
      foreach ($this->arrMembers as $objModel) {
        $objModel->delete();
      }//foreach
    }//function

    private function fetch () {
      $strSQL = "SELECT * FROM `" . $this->getTableName() . "`";

      $strSQL = trim($strSQL) . " " . $this->objResultsFilter->getConditionString();
      $strSQL = trim($strSQL) . " " . $this->objResultsFilter->getOrderByString();
      $strSQL = trim($strSQL) . " " . $this->objResultsFilter->getLimitString();

      $strSQL .= ";";

      $dbResults = $this->dbConn->query($strSQL);

      $arrMembers = array();

      while ($arrResult = $dbResults->fetch_assoc()) {
        $strModelName = $this->objResultsFilter->getModelName();

        $objModel = new $strModelName;
        $objModel->loadFromDBArray($arrResult);
        $arrMembers[] = $objModel;
      }//while

      $this->arrMembers = $arrMembers;
    }//function

    private function addModel ($objModel) {
      $this->arrMembers[] = $objModel;
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