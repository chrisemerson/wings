<?php
  class HomeView extends MasterView {
    public function __construct () {
      $this->loadTemplate('home');

      parent::__construct();
    }//function

    public function render () {
      parent::render();
    }//function
  }//class