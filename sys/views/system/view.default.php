<?php
  namespace Wings\Views\System;

  class DefaultView extends MasterView {
    public function __construct () {
      $this->loadTemplate('system.default');
    }//function

    public function render () {
      $this->passthroughAll();

      parent::render();
    }//function
  }//class