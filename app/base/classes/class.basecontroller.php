<?php
  abstract class BaseController {
    protected $view = null;

    public final function __construct () {
      $this->errors = new ErrorRegistry();
      $this->post = new InputFilter(INPUT_TYPE_POST);
      $this->get = new InputFilter(INPUT_TYPE_GET);
      $this->session = new Session();
      $this->auth = new Authentication();
      $this->files = new FileUpload();
    }//function

    public function index () {
      $this->view = new DefaultView();

      $this->view->controllername = get_class($this);

      $this->renderView();
    }//function

    protected function renderView () {
      $this->view->render();
    }//function
  }//class