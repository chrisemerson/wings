<?php
  class ErrorView extends MasterView {
    public function __construct () {
      $this->loadTemplate('system.error');

      parent::__construct();
    }//function

    public function render () {
      if (isset($this->is404) && $this->is404) {
        header('HTTP/1.0 404 Not Found');
      }//if

      $this->template->errortitle = $this->errortitle;
      $this->template->errortext = $this->errortext;

      parent::render();
    }//function
  }//class