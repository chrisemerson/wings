<?php
  class CURL {
    private $strURL = '';
    private $curlHandler;
    private $arrHeaders = array();

    public $errno;
    public $error;

    public function __construct ($strURL = '') {
      $this->strURL = $strURL;

      if (!empty($this->strURL)) {
        $this->curlHandler = curl_init($strURL);
      } else {
        $this->curlHandler = curl_init();
      }//if

      $this->CURLOPT_RETURNTRANSFER = true;
    }//function

    public function __destruct () {
      curl_close($this->curlHandler);
    }//function

    public function set ($arrOptions) {
      curl_setopt_array($this->curlHandler, $arrOptions);
    }//function

    public function __set ($strName, $mixValue) {
      curl_setopt($this->curlHandler, constant(uppercase($strName)), $mixValue);
    }//function

    public function addHeader ($strName, $strValue) {
      $this->arrHeaders[$strName] = $strName . ": " . $strValue;
    }//function

    public function exec () {
      if (count($this->arrHeaders) > 0) {
        $this->CURLOPT_HTTPHEADER = array_values($this->arrHeaders);
      }//if

      $mixReturn = curl_exec($this->curlHandler);

      $this->errno = curl_errno($this->curlHandler);
      $this->error = curl_error($this->curlHandler);

      return $mixReturn;
    }//function

    public function __get ($strName) {
      return curl_getinfo($this->curlHandler, constant($strName));
    }//function
  }//class