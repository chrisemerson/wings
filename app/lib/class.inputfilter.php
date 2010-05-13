<?php
  define('INPUT_TYPE_POST', 1);
  define('INPUT_TYPE_GET', 2);
  define('INPUT_TYPE_SESSION', 3);
  define('INPUT_TYPE_COOKIE', 4);
  define('INPUT_TYPE_SERVER', 5);

  define('INPUT_ERROR_NO_ERROR', 0);
  define('INPUT_ERROR_REQUIRED_FIELD_NOT_PASSED', 1);
  define('INPUT_ERROR_INVALID_FORMAT', 2);

  class InputFilter {
    private $arrInput = array();
    private $blnRequired = false;
    private $arrErrors = array();

    const INPUT_FORMAT_INTEGER = "/^[+-]?[0-9]+\$/";
    const INPUT_FORMAT_DECIMAL = "/^[+-]?([0-9]+|[0-9]*\\.[0-9]+)\$/";
    const INPUT_FORMAT_STRING = "/^.*\$/si";
    const INPUT_FORMAT_EMAIL = "/^[a-z0-9!#\$%&'*+\\/=?^_`{|}~-]+(?:\\.[a-z0-9!#\$%&'*+\\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\$/i";
    const INPUT_FORMAT_DATE = "/^\\d{4}-\\d{2}-\\d{2}\$/";

    public function __construct ($conInputType = INPUT_TYPE_POST) {
      switch ($conInputType) {
        case INPUT_TYPE_POST:
          $this->arrInput = $_POST;
          break;

        case INPUT_TYPE_GET:
          $this->arrInput = $_GET;
          break;

        case INPUT_TYPE_SESSION:
          $this->arrInput = $_SESSION;
          break;

        case INPUT_TYPE_COOKIE:
          $this->arrInput = $_COOKIE;
          break;

        case INPUT_TYPE_SERVER:
          $this->arrInput = $_SERVER;
          break;
      }//switch
    }//function

    private function getField ($strFieldName, $strRegex) {
      $strInput = trim($this->arrInput[$strFieldName]);

      if (!isset($strInput) || (empty($strInput) && ($strInput !== 0) && ($strInput !== "0"))) {
        if ($this->blnRequired) {
          $this->addError($strFieldName, INPUT_ERROR_REQUIRED_FIELD_NOT_PASSED, $strInput);
        }//if
      } else if (!preg_match($strRegex, $strInput)) {
        $this->addError($strFieldName, INPUT_ERROR_INVALID_FORMAT, $strInput);
      }//if

      return $strInput;
    }//function

    private function addError ($strFieldName, $conError, $strValue = '') {
      $arrError = array('error' => $conError,
                        'value' => $strValue);

      $this->arrErrors[$strFieldName] = $arrError;
    }//function

    public function getErrorForField ($strFieldName) {
      if (isset($this->arrErrors[$strFieldName]) && !empty($this->arrErrors[$strFieldName])) {
        return $this->arrErrors[$strFieldName]['error'];
      }//if

      return INPUT_ERROR_NO_ERROR;
    }//function

    public function getAllErrors () {
      return $this->arrErrors;
    }//function

    public function isError () {
      return (!empty($this->arrErrors));
    }//function

    public function setRequired ($blnRequired = true) {
      $this->blnRequired = $blnRequired;
    }//function

    public function getInteger ($strFieldName) {
      return $this->getField($strFieldName, self::INPUT_FORMAT_INTEGER);
    }//function

    public function getDecimal ($strFieldName) {
      return $this->getField($strFieldName, self::INPUT_FORMAT_DECIMAL);
    }//function

    public function getString ($strFieldName) {
      return $this->getField($strFieldName, self::INPUT_FORMAT_STRING);
    }//function

    public function getDate ($strFieldName) {
      return $this->getField($strFieldName, self::INPUT_FORMAT_DATE);
    }//if

    public function getEmailAddress ($strFieldName) {
      return $this->getField($strFieldName, self::INPUT_FORMAT_EMAIL);
    }//function
  }//class
?>