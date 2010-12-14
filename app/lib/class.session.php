<?php
  class Session {
    private $arrData = array();

    public function __construct () {
      $this->readSession();
    }//function

    public function __get ($strName) {
      if (isset($this->arrData[$strName])) {
        return $this->arrData[$strName];
      }//if

      return null;
    }//function

    public function __set ($strName, $mixValue) {
      $this->arrData[$strName] = $mixValue;
      $this->writeSession();
    }//function

    public function __isset ($strName) {
      return isset($this->arrData[$strName]);
    }//function

    public function __unset ($strName) {
      unset($this->arrData[$strName]);
      $this->writeSession();
    }//function

    public function destroy () {
      session_destroy();
    }//function

    public function regenerateID () {
      session_regenerate_id();
    }//function

    private function readSession () {
      $this->arrData = $_SESSION;
    }//function

    private function writeSession () {
      $_SESSION = $this->arrData;
    }//function
  }//class