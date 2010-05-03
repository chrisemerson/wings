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

    public function addData ($mixNameOrArray, $strValue = "") {
      if (is_array($mixNameOrArray)) {
        array_map(array($this, 'addData'), array_keys($mixNameOrArray), array_values($mixNameOrArray));
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
      if (empty($strDBDate)) {
        return '';
      }//if

      $objDate = new IDate();
      $objDate->loadFromDBFormat($strDBDate);

      return $objDate->format($strFormat);
    }//function
  }//class
?>