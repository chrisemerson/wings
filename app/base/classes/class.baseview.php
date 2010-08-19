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