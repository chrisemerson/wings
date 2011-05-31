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

      $this->objErrorRegistry = new ErrorRegistry();
    }//function

    public function __get ($strName) {
      switch ($strName) {
        case 'h':
          $mixValue = $this->mixValue;

          $this->reset();

          return $mixValue;
          break;

        case 'i':
          $mixValue = $this->mixValue;

          $this->reset();

          return intval($mixValue);
          break;

        case 's':
          $mixValue = $this->mixValue;

          $this->reset();

          return $mixValue;
          break;

        case 't':
          $mixValue = $this->mixValue;

          $this->reset();

          return $mixValue;
          break;

        default:
          return isset($this->arrInputArray[$strName]) ? $this->arrInputArray[$strName] : null;
          break;
      }//switch
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

    private function reset () {
      $this->strName = null;
      $this->strLabel = null;
      $this->mixValue = null;
    }//function

    private function getErrorText ($strError, $strField, $arrParams = array()) {
      $objErrorTextConfig = new Config('errors');
      $objAppConfig = new Config('app');

      $strError = strtolower($strError);

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
      return (!empty($strValue) || ($strValue === '0'));
    }//function

    private function requiredIf ($strValue, $strControlField) {
      return (empty($this->arrInputArray[$strControlField]) || (!empty($strValue) || ($strValue === '0')));
    }//function

    private function is ($strValue, $strStringToMatch) {
      return ($strValue == $strStringToMatch);
    }//function

    private function matches ($strValue, $strMatchField) {
      return ((!isset($this->arrInputArray[$strMatchField]) && empty($strValue)) || ($strValue == $this->arrInputArray[$strMatchField]));
    }//function

    private function length ($strValue, $intLength) {
      return (strlen($strValue) == $intLength);
    }//function

    private function lengthMin ($strValue, $intLength) {
      return (strlen($strValue) >= $intLength);
    }//function

    private function lengthMax ($strValue, $intLength) {
      return (strlen($strValue) <= $intLength);
    }//function

    private function lengthBetween ($strValue, $intMinLength, $intMaxLength) {
      return (strlen($strValue) >= $intMinLength) && (strlen($strValue) <= $intMaxLength);
    }//function

    private function alpha ($strValue) {
      return preg_match("/^[a-z]+\$/i", $strValue);
    }//function

    private function numeric ($strValue) {
      return is_numeric($strValue);
    }//function

    private function alphaNumeric ($strValue) {
      return preg_match("/^[a-z0-9]+\$/i", $strValue);
    }//function

    private function integer ($strValue) {
      return preg_match("/^([-+]?[0-9]+|[0-9]*)\$/", $strValue);
    }//function

    private function integerMin ($strValue, $intValue) {
      return ($this->integer($strValue) && $strValue >= $intValue);
    }//function

    private function integerMax ($strValue, $intValue) {
      return ($this->integer($strValue) && $strValue <= $intValue);
    }//function

    private function integerBetween ($strValue, $intMinValue, $intMaxValue) {
      return ($this->integer($strValue) && $strValue >= $intMinValue && $strValue <= $intMaxValue);
    }//function

    private function integerPositive ($strValue) {
      return ($this->integer($strValue) && $this->integerMin($strValue, 1));
    }//function

    private function integerNegative ($strValue) {
      return ($this->integer($strValue) && $this->integerMax($strValue, -1));
    }//function

    private function integerNonPositive ($strValue) {
      return ($this->integer($strValue) && !$this->integerPositive($strValue));
    }//function

    private function integerNonNegative ($strValue) {
      return ($this->integer($strValue) && !$this->integerNegative($strValue));
    }//function

    private function integerNonZero ($strValue) {
      return ($this->integer($strValue) && ($this->integerMax($strValue, -1) || $this->integerMin($strValue, 1)));
    }//function

    private function regex ($strValue, $strRegex) {
      return preg_match($strRegex, $strValue);
    }//function

    private function validEmail ($strValue) {
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