<?php
  class ErrorView extends MasterView {
    public function __construct () {
      $this->loadTemplate('system.error');
    }//function

    public function render () {
      $this->template->errortitle = $this->errortitle;
      $this->template->errortext = $this->errortext;

      parent::render();
    }//function
  }//class