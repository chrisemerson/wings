<?php
  define('INPUT_TYPE_POST', 1);
  define('INPUT_TYPE_GET', 2);

  class FormValidator {
    private $arrValidationRules;
    private $arrLabels;

    private $arrInputArray;
    private $arrFormValues;

    private $objErrorHandler;
    private $objController = null;

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

      $this->objErrorHandler = new ErrorHandler('form');

      $arrBacktrace = debug_backtrace(true);

      foreach ($arrBacktrace as $arrBacktraceStep) {
        if ($arrBacktraceStep['object'] instanceof BaseController) {
          $this->objController = $arrBacktraceStep['object'];
          break;
        }//if
      }//foreach
    }//function

    public function addValidationRules ($mixNameOrArray, $arrFormValidationInfo = array()) {
      if (is_array($mixNameOrArray)) {
        foreach ($mixNameOrArray as $strName => $arrThisFieldValidationRules) {
          $this->addValidationRules($strName, $arrThisFieldValidationRules);
        }//foreach
      } else {
        $this->arrLabels[$mixNameOrArray] = $arrFormValidationInfo['label'];

        if (isset($this->arrValidationRules[$mixNameOrArray]) && is_array($this->arrValidationRules[$mixNameOrArray])) {
          $this->arrValidationRules[$mixNameOrArray] = array_merge($this->arrValidationRules[$mixNameOrArray], $arrFormValidationInfo['rules']);
        } else {
          $this->arrValidationRules[$mixNameOrArray] = $arrFormValidationInfo['rules'];
        }//if
      }//if
    }//function

    public function validate () {
      if (empty($this->arrInputArray)) {
        return false;
      }//if

      foreach ($this->arrValidationRules as $strFieldName => $arrValidationRules) {
        if (isset($this->arrInputArray[$strFieldName])) {
          $this->arrFormValues[$strFieldName] = $this->arrInputArray[$strFieldName];
        } else {
          $this->arrFormValues[$strFieldName] = null;
        }//if

        foreach ($arrValidationRules as $strValidationRule) {
          echo $strValidationRule;

          if (preg_match('/^regex\[(.*)\]$/i', $strValidationRule, $arrMatches)) {
            if (!preg_match($arrMatches[1], $this->arrFormValues[$strFieldName])) {
              $this->objErrorHandler->addError($this->getErrorText('regex', $strFieldName), $strFieldName);
            }//if
          } else if (preg_match('/^callback\[(.*)\]$/i', $strValidationRule, $arrMatches)) {
            if (!is_null($this->objController) && !call_user_func_array(array($this->objController, $arrMatches[1]), array($this->arrFormValues[$strFieldName]))) {
              $this->objErrorHandler->addError($this->getErrorText($arrMatches[1], $strFieldName), $strFieldName);
            }//if
          } else if (preg_match('/^([a-z_][a-z0-9_]+)(?:\[([a-z0-9_](?:,[a-z0-9_])*)\])?$/i', $strValidationRule, $arrMatches)) {
            $strValidationFunction = $arrMatches[1];

            $arrValidationParams = array($this->arrFormValues[$strFieldName]);

            if (isset($arrMatches[2])) {
              $arrValidationParams = array_merge($arrValidationParams, array_map('trim', explode(',', $arrMatches[2])));
            }//if

            if (is_callable(array($this, $strValidationFunction))) {
              if (!call_user_func_array(array($this, $strValidationFunction), $arrValidationParams)) {
                $this->objErrorHandler->addError($this->getErrorText($strValidationFunction, $strFieldName), $strFieldName);
              }//if
            } else if (is_callable($strValidationFunction)) {
              $this->arrFormValues[$strFieldName] = call_user_func_array($strValidationFunction, $arrValidationParams);
            }//if
          }//if

          echo "<br>";
        }//foreach
      }//foreach

      return ($this->objErrorHandler->getNumberOfErrors() == 0);
    }//function

    public function getValue ($strName) {
      if (isset($this->arrFormValues[$strName])) {
        return $this->arrFormValues[$strName];
      } else {
        return null;
      }//if
    }//function

    private function getErrorText ($strError, $strField, $arrParams = array()) {
      $objErrorTextConfig = Config::get('errors');
      $objAppConfig = Config::get('app');

      try {
        $strError = $objErrorTextConfig->$strError->fields->$strField;
      } catch (ConfigSettingNotFoundException $ex) {
        try {
          $strError = $objErrorTextConfig->$strError->default;
        } catch (ConfigSettingNotFoundException $ex) {
          try {
            $strError = $objErrorTextConfig->$strError;
          } catch (ConfigSettingNotFoundException $ex) {
            try {
              $strError = $objAppConfig->errors->defaultformerror;
            } catch (ConfigSettingNotFoundException $ex) {
              Application::showError('general', 'Error Text Not Found');
            }//try
          }//try
        }//try
      }//try

      $strError = str_replace('{field}', $this->arrLabels[$strField], $strError);

      foreach ($arrParams as $strParamName => $mixParamValue) {
        $strError = str_replace('{' . strtolower($strParamName) . '}', $mixParamValue, $strError);
      }//foreach

      return $strError;
    }//function

    /* Validation Functions */

    private function required ($strValue) {
      echo __FUNCTION__;
      return (!empty($strValue));
    }//function

    private function required_if ($strValue) {
      echo __FUNCTION__;
      return true;
    }//function

    private function matches ($strValue) {
      echo __FUNCTION__;
      return true;
    }//function

    private function length ($strValue, $intLength) {
      echo __FUNCTION__;
      return true;
    }//function

    private function length_min ($strValue, $intLength) {
      echo __FUNCTION__;
      return true;
    }//function

    private function length_max ($strValue, $intLength) {
      echo __FUNCTION__;
      return true;
    }//function

    private function length_between ($strValue, $intMinLength, $intMaxLength) {
      echo __FUNCTION__;
      return true;
    }//function

    private function alpha ($strValue) {
      echo __FUNCTION__;
      return true;
    }//function

    private function numeric ($strValue) {
      echo __FUNCTION__;
      return true;
    }//function

    private function alpha_numeric ($strValue) {
      echo __FUNCTION__;
      return true;
    }//function

    private function integer ($strValue) {
      echo __FUNCTION__;
      return true;
    }//function

    private function integer_min ($strValue, $intValue) {
      echo __FUNCTION__;
      return true;
    }//function

    private function integer_max ($strValue, $intValue) {
      echo __FUNCTION__;
      return true;
    }//function

    private function integer_between ($strValue, $intMinValue, $intMaxValue) {
      echo __FUNCTION__;
      return true;
    }//function

    private function valid_email ($strValue) {
      echo __FUNCTION__;
      return true;
    }//function

    private function unique ($strValue, $strModel, $strField) {
      echo __FUNCTION__;
      return true;
    }//function
  }//class