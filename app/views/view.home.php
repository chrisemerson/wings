<?php
  class HomeView extends MasterView {
    public function __construct () {
      $this->loadTemplate('home');
    }//function

    public function render () {
      parent::render();
    }//function
  }//class