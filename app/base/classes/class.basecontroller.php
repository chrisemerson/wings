<?php
  abstract class BaseController {
    protected $view = null;

    public final function __construct () {
      $this->errors = new ErrorRegistry();
      $this->input = new FormValidator();
      $this->post = new InputFilter(INPUT_TYPE_POST, get_class($this));
      $this->get = new InputFilter(INPUT_TYPE_GET, get_class($this));
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
      $this->view->errors = $this->errors;
      $this->view->input = $this->input;
      $this->view->post = $this->post;
      $this->view->get = $this->get;
      $this->view->session = $this->session;
      $this->view->auth = $this->auth;
      $this->view->files = $this->files;

      $this->view->render();
    }//function
  }//class