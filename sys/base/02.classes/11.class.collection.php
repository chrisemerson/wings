<?php
  abstract class Collection extends Schema implements Iterator, Countable, ArrayAccess {
    private $arrMembers = array();
    private $intPosition = 0;
    private $blnJustUnsetCurrent = false;

    public function __construct ($strModelName, $strSQLString = '') {
      $this->strModelName = $strModelName;

      $objModelRegistry = new ModelRegistry();

      if ($objModelRegistry->isModel($strModelName)) {
        parent::__construct();
      }//if

      $this->fetch($strSQLString);
    }//if

    private function fetch ($strSQLString = '') {
      if (!empty($strSQLString)) {
        $strSQL = "SELECT * FROM `" . $this->getTableName() . "`";

        if ($strSQLString != '*') {
          $strSQL .= " " . trim($strSQLString);
        }//if

        $strSQL = rtrim($strSQL, ";") . ";";

        $this->populate($strSQL);
      }//if
    }//function

    private function populate ($strQuery) {
      $dbResults = $this->dbConn->query($strQuery);

      while ($arrResult = $dbResults->fetch_assoc()) {
        $objModel = new $this->strModelName();
        $objModel->loadFromDBArray($arrResult);

        $this->arrMembers[] = $objModel;
      }//while
    }//function

    public function __set ($strName, $strValue) {
      foreach ($this->arrMembers as $objModel) {
        $objModel->$strName = $strValue;
      }//foreach
    }//function

    public function __call ($strName, $arrArgs) {
      foreach ($this->arrMembers as $objModel) {
        return call_user_func_array(array($objModel, $strName), $arrArgs);
      }//foreach
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
        $this->arrMembers[] = $mixValue;
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