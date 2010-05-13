<?php
  /*************************************/
  /* BaseView Class - by Chris Emerson */
  /* http://www.cemerson.co.uk/        */
  /*                                   */
  /* Version 0.1                       */
  /* 23rd May 2009                     */
  /*************************************/

  class BaseView {
    protected $template;

    private   $arrData;
    private   $arrCollections;
    private   $arrModels;

    public function render () {
      $this->template->out();
    }//function

    public function loadTemplate ($strTemplateName, $blnIgnoreMasterTemplateSetting = false) {
      $this->template = new Template($strTemplateName, $blnIgnoreMasterTemplateSetting);
    }//function

    public function loadData ($mixNameOrArray, $strValue = "") {
      if (is_array($mixNameOrArray)) {
        array_map(array($this, 'loadData'), array_keys($mixNameOrArray), array_values($mixNameOrArray));
      } else {
        $this->arrData[$mixNameOrArray] = $strValue;
      }//if
    }//function

    protected function getData ($strName) {
      if (isset($this->arrData[$strName])) {
        return $this->arrData[$strName];
      } else {
        return false;
      }//if
    }//function

    public function loadCollection ($objCollection, $strName) {
      $this->arrCollections[$strName] = $objCollection;
    }//function

    protected function getCollection ($strName) {
      return $this->arrCollections[$strName];
    }//function

    public function loadModel ($objModel, $strName) {
      $this->arrModels[$strName] = $objModel;
    }//function

    protected function getModel ($strName) {
      return $this->arrModels[$strName];
    }//function

    protected function formatDBDate ($strDBDate, $strFormat) {
      if (empty($strDBDate) || $strDBDate == '0000-00-00') {
        return '';
      }//if

      $objDate = new IDate();
      $objDate->loadFromDBFormat($strDBDate);

      if ($objDate->isValid()) {
        return $objDate->format($strFormat);
      }//if

      return '';
    }//function

    protected function ifEmptyInsertContent ($mixVariable, $strContent = "&nbsp;") {
      if (empty($mixVariable)) {
        return $strContent;
      } else {
        return $mixVariable;
      }//if
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
?>