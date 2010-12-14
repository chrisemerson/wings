<?php
  define('INPUT_TYPE_POST', 1);
  define('INPUT_TYPE_GET', 2);

  class FormValidator {
    private $arrValidationRules;
    private $arrLabels;

    private $arrInputArray;
    private $arrFormValues;

    private $objErrorRegistry;
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

      $this->objErrorRegistry = new ErrorRegistry('form');

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
          if (preg_match('/^regex\s*\[\s*(.*)\s*\]$/i', $strValidationRule, $arrMatches)) {
            if (!preg_match($arrMatches[1], $this->arrFormValues[$strFieldName])) {
              $this->objErrorRegistry->addError($this->getErrorText('regex', $strFieldName), $strFieldName);
            }//if
          } else if (preg_match('/^callback\s*\[\s*(.*)\s*\]$/i', $strValidationRule, $arrMatches)) {
            if (!is_null($this->objController) && !call_user_func_array(array($this->objController, $arrMatches[1]), array($this->arrFormValues[$strFieldName]))) {
              $this->objErrorRegistry->addError($this->getErrorText($arrMatches[1], $strFieldName), $strFieldName);
            }//if
          } else if (preg_match('/^([a-z_][a-z0-9_]+)\s*(?:\[\s*([a-z0-9_]+(?:\s*,\s*[a-z0-9_]+)*)\s*\])?$/i', $strValidationRule, $arrMatches)) {
            $strValidationFunction = $arrMatches[1];

            $arrValidationParams = array($this->arrFormValues[$strFieldName]);

            if (isset($arrMatches[2])) {
              $arrValidationParams = array_merge($arrValidationParams, array_map('trim', explode(',', $arrMatches[2])));
            }//if

            if (is_callable(array($this, $strValidationFunction))) {
              if (!call_user_func_array(array($this, $strValidationFunction), $arrValidationParams)) {
                $this->objErrorRegistry->addError($this->getErrorText($strValidationFunction, $strFieldName, array_slice($arrValidationParams, 1)), $strFieldName);

                //Only 1 error per field at a time, so if this validation failed, go to next field
                break;
              }//if
            } else if (is_callable($strValidationFunction)) {
              $this->arrFormValues[$strFieldName] = call_user_func_array($strValidationFunction, $arrValidationParams);
            }//if
          }//if
        }//foreach
      }//foreach

      return !$this->objErrorRegistry->isError();
    }//function

    public function getValue ($strName) {
      if (isset($this->arrFormValues[$strName])) {
        return $this->arrFormValues[$strName];
      } else {
        return null;
      }//if
    }//function

    public function getErrorRegistry () {
      return $this->objErrorRegistry;
    }//function

    public function isError () {
      return $this->objErrorRegistry->isError();
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

      $strError = str_replace('{field}', $this->arrLabels[$strField], $strError);

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
      return ($strValue == $this->arrFormValues[$strMatchField]);
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

    private function integer_nonnegative ($strValue) {
      return !$this->integer_negative($strValue);
    }//function

    private function integer_nonzero ($strValue) {
      return ($this->integer($strValue) && ($this->integer_max($strValue, -1) || $this->integer_min($strValue, 1)));
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