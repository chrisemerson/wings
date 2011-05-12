<?php
  if (!defined('INPUT_TYPE_POST')) {
    define('INPUT_TYPE_POST', 1);
  }//if

  if (!defined('INPUT_TYPE_GET')) {
    define('INPUT_TYPE_GET', 2);
  }//if

  class InputFilter {
    private $arrInputArray = array();
    private $objErrorRegistry;

    private $strName = null;
    private $strLabel = null;
    private $mixValue = null;

    public function __construct ($conInputType = INPUT_TYPE_POST) {
      switch ($conInputType) {
        case INPUT_TYPE_GET:
          $this->arrInputArray = $_GET;
          break;

        case INPUT_TYPE_POST:
        default:
          $this->arrInputArray = $_POST;
          break;
      }//switch

      $this->objErrorRegistry = new ErrorRegistry('form');
    }//function

    public function __get ($strName) {
      if (is_null($this->strName)) {
        $this->strName = $strName;
        $this->strLabel = $strName;

        if (isset($this->arrInputArray[$strName])) {
          $this->mixValue = $this->arrInputArray[$strName];
        }//if
      } else {
        switch ($strName) {
          case 'h':
            return $this->mixValue;
            break;

          case 'i':
            return intval($this->mixValue);
            break;

          case 's':
            return $this->mixValue;
            break;

          case 't':
            return $this->mixValue;
            break;
        }//switch
      }//if

      return $this;
    }//function

    public function __set ($strName, $mixValue) {
      $this->arrInputArray[$strName] = $mixValue;
    }//function

    public function __isset ($strName) {
      return isset($this->arrInputArray[$strName]);
    }//function

    public function __unset ($strName) {
      if (isset($this->arrInputArray[$strName])) {
        unset($this->arrInputArray[$strName]);
      }//if
    }//function

    public function __call ($strCall, $arrArguments) {
      if (is_null($this->strName)) {
        $this->strName = $strCall;
        $this->mixValue = isset($this->arrInputArray[$strCall]) ? $this->arrInputArray[$strCall] : null;

        if (count($arrArguments) == 1) {
          $this->strLabel = $arrArguments[0];
        } else if (count($arrArguments) == 0) {
          $this->strLabel = $strCall;
        }//if
      } else if (is_callable(array($this, $strCall))) {
        if (!call_user_func_array(array($this, $strCall), array_merge(array($this->mixValue), $arrArguments))) {
          $this->objErrorRegistry->addError($this->getErrorText($strCall, $this->strName, $arrArguments), $this->strName);
        }//if
      }//if

      return $this;
    }//function

    public function isError () {
      return $this->objErrorRegistry->isError();
    }//function

    public function getErrors () {
      return $this->objErrorRegistry->getErrors();
    }//function

    private function getErrorText ($strError, $strField, $arrParams = array()) {
      $objErrorTextConfig = new Config('errors');
      $objAppConfig = new Config('app');

      if (isset($objErrorTextConfig->$strError->fields->$strField)) {
        $strError = $objErrorTextConfig->$strError->fields->$strField;
      } else if (isset($objErrorTextConfig->$strError->default)) {
        $strError = $objErrorTextConfig->$strError->default;
      } else if (isset($objErrorTextConfig->$strError)) {
        $strError = $objErrorTextConfig->$strError;
      } else if (isset($objAppConfig->errors->defaultformerror)) {
        $strError = $objAppConfig->errors->defaultformerror;
      } else {
        Application::showError('general', 'Error Text Not Found');
      }//if

      $strError = str_replace('{field}', $this->strLabel, $strError);

      foreach (array_values($arrParams) as $intParamNumber => $mixParamValue) {
        $strError = str_replace('{' . ($intParamNumber + 1) . '}', $mixParamValue, $strError);
      }//foreach

      return $strError;
    }//function

    /* Validation Functions */

    private function required ($strValue) {
      return (!empty($strValue));
    }//function

    private function required_if ($strValue, $strControlField) {
      return (empty($this->arrFormValues[$strControlField]) || !empty($strValue));
    }//function

    private function is ($strValue, $strStringToMatch) {
      return ($strValue == $strStringToMatch);
    }//function

    private function matches ($strValue, $strMatchField) {
      return ((!isset($this->arrFormValues[$strMatchField]) && empty($strValue)) || ($strValue == $this->arrFormValues[$strMatchField]));
    }//function

    private function length ($strValue, $intLength) {
      return (strlen($strValue) == $intLength);
    }//function

    private function length_min ($strValue, $intLength) {
      return (strlen($strValue) >= $intLength);
    }//function

    private function length_max ($strValue, $intLength) {
      return (strlen($strValue) <= $intLength);
    }//function

    private function length_between ($strValue, $intMinLength, $intMaxLength) {
      return (strlen($strValue) >= $intMinLength) && (strlen($strValue) <= $intMaxLength);
    }//function

    private function alpha ($strValue) {
      return preg_match("/^[a-z]+\$/i", $strValue);
    }//function

    private function numeric ($strValue) {
      return is_numeric($strValue);
    }//function

    private function alpha_numeric ($strValue) {
      return preg_match("/^[a-z0-9]+\$/i", $strValue);
    }//function

    private function integer ($strValue) {
      return preg_match("/^[-+]?[0-9]+\$/", $strValue);
    }//function

    private function integer_min ($strValue, $intValue) {
      return ($this->integer($strValue) && $strValue >= $intValue);
    }//function

    private function integer_max ($strValue, $intValue) {
      return ($this->integer($strValue) && $strValue <= $intValue);
    }//function

    private function integer_between ($strValue, $intMinValue, $intMaxValue) {
      return ($this->integer($strValue) && $strValue >= $intMinValue && $strValue <= $intMaxValue);
    }//function

    private function integer_positive ($strValue) {
      return ($this->integer($strValue) && $this->integer_min($strValue, 1));
    }//function

    private function integer_negative ($strValue) {
      return ($this->integer($strValue) && $this->integer_max($strValue, -1));
    }//function

    private function integer_nonpositive ($strValue) {
      return !$this->integer_positive($strValue);
    }//function

    private function integer_nonnegative ($strValue) {
      return !$this->integer_negative($strValue);
    }//function

    private function integer_nonzero ($strValue) {
      return ($this->integer($strValue) && ($this->integer_max($strValue, -1) || $this->integer_min($strValue, 1)));
    }//function

    private function regex ($strValue, $strRegex) {
      return preg_match($strRegex, $strValue);
    }//function

    private function valid_email ($strValue) {
      return preg_match("/^[a-z0-9!#\$%&'*+\\/=?^_`{|}~-]+(\\.[a-z0-9!#\$%&'*+\\/=?^_`{|}~-]+)*@([a-z0-9]([a-z0-9-]*[a-z0-9])?\\.)+[a-z0-9]([a-z0-9-]*[a-z0-9])?\$/i", $strValue);
    }//function

    private function unique ($strValue, $strModel, $strField) {
      $objResultsFilter = new ResultsFilter();

      $objResultsFilter->model($strModel)
                       ->conditions($strField . " = '" . $strValue . "'");

      $objCollection = new Collection($objResultsFilter);

      return (count($objCollection) == 0);
    }//function
  }//class