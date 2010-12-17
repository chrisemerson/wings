<?php
  class Session {
    private static $arrData = array();

    private $strContext;

    public function __construct ($strContext = '') {
      $this->strContext = $strContext;

      $this->readSession();
    }//function

    public function setContext ($strContext) {
      $this->strContext = $strContext;
    }//function

    public function __get ($strName) {
      if (empty($this->strContext)) {
        if (isset(self::$arrData['default'][$strName])) {
          return self::$arrData['default'][$strName];
        }//if
      } else {
        if (isset(self::$arrData['contexts'][$this->strContext][$strName])) {
          return self::$arrData['contexts'][$this->strContext][$strName];
        }//if
      }//if

      return null;
    }//function

    public function __set ($strName, $mixValue) {
      if (empty($this->strContext)) {
        self::$arrData['default'][$strName] = $mixValue;
      } else {
        self::$arrData['contexts'][$this->strContext][$strName] = $mixValue;
      }//if

      $this->writeSession();
    }//function

    public function __isset ($strName) {
      if (empty($this->strContext)) {
        return isset(self::$arrData['default'][$strName]);
      } else {
        return isset(self::$arrData['contexts'][$this->strContext][$strName]);
      }//if
    }//function

    public function __unset ($strName) {
      if (empty($this->strContext)) {
        unset(self::$arrData['default'][$strName]);
      } else {
        unset(self::$arrData['contexts'][$this->strContext][$strName]);
      }//if

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