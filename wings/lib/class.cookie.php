<?php
  class Cookie {
    private $arrData;

    public function __construct () {

    }//function

    public function __get ($strName) {
      return $this->arrData[$strName];
    }//function

    public function __isset ($strName) {
      return isset($this->arrData[$strName]);
    }//function

    public function __set ($strName, $mixValue) {
      $this->arrData[$strName] = $mixValue;
    }//function

    public function write () {

    }//function
  }//class