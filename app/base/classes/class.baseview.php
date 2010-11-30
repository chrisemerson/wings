<?php
  abstract class BaseView {
    protected $template;

    private   $arrData = array();
    private   $arrBaseTemplateVars = array();

    /* Template Handling */

    protected function loadTemplate ($strTemplateName, $blnIgnoreMasterTemplateSetting = false) {
      try {
        $this->template = new Template($strTemplateName, $blnIgnoreMasterTemplateSetting);
      } catch (TemplateNotFoundException $ex) {
        Application::showError('template');
      }//try
    }//function

    /* Output */

    public function render () {
      $this->template->parse();
      $this->template->out();
    }//function

    /* Variables */

    public function __get ($strName) {
      if (isset($this->arrData[$strName])) {
        return $this->arrData[$strName];
      } else {
        return false;
      }//if
    }//function

    public function __set ($strName, $mixValue) {
      $this->arrData[$strName] = $mixValue;
    }//function

    public function __isset ($strName) {
      return isset($this->arrData[$strName]);
    }//function

    public function loadData ($mixNameOrArray, $strValue = "") {
      if (is_array($mixNameOrArray)) {
        array_map(array($this, 'loadData'), array_keys($mixNameOrArray), array_values($mixNameOrArray));
      } else {
        $this->arrData[$mixNameOrArray] = $strValue;
      }//if
    }//function

    /* Helper Functions */

    protected function passthrough () {
      $arrVars = func_get_args();

      foreach ($arrVars as $strVar) {
        $this->template->$strVar = $this->$strVar;
      }//foreach
    }//function

    protected function passthroughAll () {
      $arrVars = $this->arrData;

      foreach ($arrVars as $strVar => $mixValue) {
        $this->template->$strVar = $mixValue;
      }//foreach
    }//function

    protected function coalesce () {
      $intMax = func_num_args();

      for ($i = 0; $i < $intMax - 1; $i++) {
        $mixValue = func_get_arg($i);

        if (!empty($mixValue)) {
          return $mixValue;
        }//if
      }//for

      return func_get_arg($intMax - 1);
    }//function
  }//class
