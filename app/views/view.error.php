<?php
  class ErrorView extends BaseView {
    public function __construct () {
      $this->loadTemplate('system.error');
    }//function

    public function render () {
      $this->template->errortitle = $this->errortitle;
      $this->template->errortext = $this->errortext;
    }//function
  }//class