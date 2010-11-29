<?php
  class HomeController extends BaseController {
    public function index () {
      $this->view = new HomeView();

      $this->view->render();
    }//function
  }//class