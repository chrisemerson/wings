<?php
  namespace Wings\Views;

  class HomeView extends System\MasterView {
    public function __construct () {
      $this->loadTemplate('home');

      parent::__construct();
    }//function

    public function render () {
      parent::render();
    }//function
  }//class