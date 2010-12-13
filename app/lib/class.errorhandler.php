<?php
  class ErrorHandler {
    private static $arrErrors;

    private $strContext;

    public function __construct ($strContext = '') {
      $this->strContext = $strContext;
    }//function

    public function addError ($strMessage, $strField = '') {
      $arrError = array('error' => $strMessage);

      if (!empty($strField)) {
        $arrError['field'] = $strField;
      }//if

      if (empty($this->strContext)) {
        self::$arrErrors['default'][] = $arrError;
      } else {
        self::$arrErrors['contexts'][$this->strContext][] = $arrError;
      }//if
    }//function

    public function getAllErrorMessages () {
      $arrErrorMessages = array();

      $arrErrors = $this->getErrorsForCurrentContext();

      foreach ($arrErrors as $arrErrorInfo) {
        $arrErrorMessages[] = $arrErrorInfo['error'];
      }//foreach

      return $arrErrorMessages;
    }//function

    public function isFieldError ($strField) {
      return (count($this->getErrorsForField($strField)) != 0);
    }//function

    public function getErrorFields () {
      $arrErrorMessages = array();

      $arrErrors = $this->getErrorsForCurrentContext();

      foreach ($arrErrors as $arrErrorInfo) {
        $arrErrorMessages[] = $arrErrorInfo['field'];
      }//foreach

      return array_unique($arrErrorMessages);
    }//function

    public function getErrorsForField ($strField) {
      $arrErrorMessages = array();

      $arrErrors = $this->getErrorsForCurrentContext();

      foreach ($arrErrors as $arrErrorInfo) {
        if ($arrErrorInfo['field'] == $strField) {
          $arrErrorMessages[] = $arrErrorInfo['error'];
        }//if
      }//foreach

      return $arrErrorMessages;
    }//function

    public function getNumberOfErrors () {
      return count($this->getErrorsForCurrentContext());
    }//function

    private function getErrorsForCurrentContext () {
      if (empty($this->strContext)) {
        if (!isset(self::$arrErrors['default'])) {
          return array();
        }//if

        return self::$arrErrors['default'];
      } else {
        if (!isset(self::$arrErrors['contexts'][$this->strContext])) {
          return array();
        }//if

        return self::$arrErrors['contexts'][$this->strContext];
      }//if
    }//function
  }//class