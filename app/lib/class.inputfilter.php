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

    const REGEX_ALPHA = "/^[a-z]+\$/i";
    const REGEX_ALPHANUMERIC = "/^[a-z0-9]+\$/i";
    const REGEX_INTEGER = "/^([-+]?[0-9]+|[0-9]*)\$/";
    const REGEX_EMAIL_ADDRESS = "/^[a-z0-9!#\$%&'*+\\/=?^_`{|}~-]+(\\.[a-z0-9!#\$%&'*+\\/=?^_`{|}~-]+)*@([a-z0-9]([a-z0-9-]*[a-z0-9])?\\.)+[a-z0-9]([a-z0-9-]*[a-z0-9])?\$/i";

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
      } else if (is_callable(array($this, "CHK" . $strCall))) {
        if (!call_user_func_array(array($this, "CHK" . $strCall), array_merge(array($this->mixValue), $arrArguments))) {
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

    private function CHKrequired ($strValue) {
      return (!empty($strValue) || ($strValue === '0'));
    }//function

    private function CHKrequiredIf ($strValue, $strControlField) {
      return (empty($this->arrInputArray[$strControlField]) || (!empty($strValue) || ($strValue === '0')));
    }//function

    private function CHKis ($strValue, $strStringToMatch) {
      return ($strValue == $strStringToMatch);
    }//function

    private function CHKmatches ($strValue, $strMatchField) {
      return ((!isset($this->arrInputArray[$strMatchField]) && empty($strValue)) || ($strValue == $this->arrInputArray[$strMatchField]));
    }//function

    private function CHKlength ($strValue, $intLength) {
      return (strlen($strValue) == $intLength);
    }//function

    private function CHKlengthMin ($strValue, $intLength) {
      return (strlen($strValue) >= $intLength);
    }//function

    private function CHKlengthMax ($strValue, $intLength) {
      return (strlen($strValue) <= $intLength);
    }//function

    private function CHKlengthBetween ($strValue, $intMinLength, $intMaxLength) {
      return (strlen($strValue) >= $intMinLength) && (strlen($strValue) <= $intMaxLength);
    }//function

    private function CHKalpha ($strValue) {
      return $this->CHKregex($strValue, self::REGEX_ALPHA);
    }//function

    private function CHKnumeric ($strValue) {
      return is_numeric($strValue);
    }//function

    private function CHKalphaNumeric ($strValue) {
      return $this->CHKregex($strValue, self::REGEX_ALPHANUMERIC);
    }//function

    private function CHKinteger ($strValue) {
      return $this->CHKregex($strValue, self::REGEX_INTEGER);
    }//function

    private function CHKintegerMin ($strValue, $intValue) {
      return ($this->CHKinteger($strValue) && $strValue >= $intValue);
    }//function

    private function CHKintegerMax ($strValue, $intValue) {
      return ($this->CHKinteger($strValue) && $strValue <= $intValue);
    }//function

    private function CHKintegerBetween ($strValue, $intMinValue, $intMaxValue) {
      return ($this->CHKinteger($strValue) && $strValue >= $intMinValue && $strValue <= $intMaxValue);
    }//function

    private function CHKintegerPositive ($strValue) {
      return ($this->CHKinteger($strValue) && $this->CHKintegerMin($strValue, 1));
    }//function

    private function CHKintegerNegative ($strValue) {
      return ($this->CHKinteger($strValue) && $this->CHKintegerMax($strValue, -1));
    }//function

    private function CHKintegerNonPositive ($strValue) {
      return ($this->CHKinteger($strValue) && !$this->CHKintegerPositive($strValue));
    }//function

    private function CHKintegerNonNegative ($strValue) {
      return ($this->CHKinteger($strValue) && !$this->CHKintegerNegative($strValue));
    }//function

    private function CHKintegerNonZero ($strValue) {
      return ($this->CHKinteger($strValue) && ($this->CHKintegerMax($strValue, -1) || $this->CHKintegerMin($strValue, 1)));
    }//function

    private function CHKregex ($strValue, $strRegex) {
      return preg_match($strRegex, $strValue);
    }//function

    private function CHKvalidEmail ($strValue) {
      return $this->CHKregex($strValue, self::REGEX_EMAIL_ADDRESS);
    }//function

    private function CHKunique ($strValue, $strModel, $strField) {
      $objResultsFilter = new ResultsFilter();

      $objResultsFilter->model($strModel)
                       ->conditions($strField . " = '" . $strValue . "'");

      $objCollection = new Collection($objResultsFilter);

      return (count($objCollection) == 0);
    }//function
  }//class