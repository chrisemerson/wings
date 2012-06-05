<?php
  namespace Wings\Lib\System;

  use \Wings\Lib\Util;

  abstract class Controller {
    protected $view = null;

    public final function __construct () {
      $this->errors = new Util\ErrorRegistry();
      $this->post = new Util\InputFilter(INPUT_TYPE_POST);
      $this->get = new Util\InputFilter(INPUT_TYPE_GET);
      $this->session = new Util\Session();
      $this->auth = new Util\Authentication();
      $this->files = new Util\FileUpload();
    }//function

    public function index () {
      $this->view = new \Wings\Views\DefaultView();

      $this->view->controllername = get_class($this);

      $this->renderView();
    }//function

    protected function renderView () {
      $this->view->render();
    }//function
  }//class