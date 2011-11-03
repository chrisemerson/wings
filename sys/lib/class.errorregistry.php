<?php
  class ErrorRegistry {
    private static $arrErrors = array();

    public function addError ($strMessage, $strField = '') {
      $arrError = array('error' => $strMessage);

      if (!empty($strField)) {
        $arrError['field'] = $strField;
      }//if

      self::$arrErrors[] = $arrError;
    }//function

    public function getErrors () {
      $arrErrorMessages = array();

      $arrErrors = $this->getAllErrors();

      foreach ($arrErrors as $arrErrorInfo) {
        $arrErrorMessages[] = $arrErrorInfo['error'];
      }//foreach

      return $arrErrorMessages;
    }//function

    public function getFieldErrors ($strField) {
      $arrErrorMessages = array();

      $arrErrors = $this->getAllErrors();

      foreach ($arrErrors as $arrErrorInfo) {
        if ($arrErrorInfo['field'] == $strField) {
          $arrErrorMessages[] = $arrErrorInfo['error'];
        }//if
      }//foreach

      return $arrErrorMessages;
    }//function

    public function isError () {
      return (count($this->getErrors()) != 0);
    }//function

    public function isFieldError ($strField) {
      return (count($this->getFieldErrors($strField)) != 0);
    }//function

    public function getErroredFields () {
      $arrErroredFields = array();

      $arrErrors = $this->getAllErrors();

      foreach ($arrErrors as $arrErrorInfo) {
        $arrErroredFields[] = $arrErrorInfo['field'];
      }//foreach

      return array_unique($arrErroredFields);
    }//function

    private function getAllErrors () {
      return self::$arrErrors;
    }//function

    public function clearErrors () {
      self::$arrErrors = array();
    }//function
  }//class