<?php
  namespace Wings\Controllers;

  class HomeController extends \Wings\Lib\System\Controller {
    public function index () {
      $this->view = new \Wings\Views\HomeView();

      $this->renderView();
    }//function
  }//class