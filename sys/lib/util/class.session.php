<?php
  namespace Wings\Lib\Util;

  class Session {
    private static $arrData = array();

    public function __construct () {
      $this->readSession();
    }//function

    public function __get ($strName) {
      if (isset(self::$arrData[$strName])) {
        return self::$arrData[$strName];
      }//if

      return null;
    }//function

    public function __set ($strName, $mixValue) {
      self::$arrData[$strName] = $mixValue;

      $this->writeSession();
    }//function

    public function __isset ($strName) {
      return isset(self::$arrData[$strName]);
    }//function

    public function __unset ($strName) {
      unset(self::$arrData[$strName]);

      $this->writeSession();
    }//function

    public function destroy () {
      session_destroy();
    }//function

    public function regenerateID () {
      session_regenerate_id(true);
    }//function

    private function readSession () {
      self::$arrData = $_SESSION;
    }//function

    private function writeSession () {
      $_SESSION = self::$arrData;
    }//function
  }//class