<?php
  class ErrorHandler {
    private static $arrErrors;

    public function addError ($strMessage, $strField = '') {
      $arrError = array('error' => $strMessage);

      if (!empty($strField)) {
        $arrError['field'] = $strField;
      }//if

      self::$arrErrors[] = $arrError;
    }//function

    public function getAllErrorMessages () {
      $arrErrorMessages = array();

      foreach (self::$arrErrors as $arrErrorInfo) {
        $arrErrorMessages[] = $arrErrorInfo['error'];
      }//foreach

      return $arrErrorMessages;
    }//function

    public function isFieldError ($strField) {
      return (count($this->getErrorsForField($strField)) != 0);
    }//function

    public function getErrorFields () {
      $arrErrorMessages = array();

      foreach (self::$arrErrors as $arrErrorInfo) {
        $arrErrorMessages[] = $arrErrorInfo['field'];
      }//foreach

      return array_unique($arrErrorMessages);
    }//function

    public function getErrorsForField ($strField) {
      $arrErrorMessages = array();

      foreach (self::$arrErrors as $arrErrorInfo) {
        if ($arrErrorInfo['field'] == $strField) {
          $arrErrorMessages[] = $arrErrorInfo['error'];
        }//if
      }//foreach

      return $arrErrorMessages;
    }//function
  }//class