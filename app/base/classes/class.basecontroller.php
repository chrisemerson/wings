<?php
  abstract class BaseController {
    protected $view = null;

    private $arrInbuiltClasses = array('errors' => 'ErrorRegistry',
                                       'input' => 'FormValidator',
                                       'session' => 'Session');

    public function index () {
      $this->view = new DefaultView();

      $this->view->controllername = get_class($this);

      $this->view->render();
    }//function

    public function __get ($strName) {
      if (isset($this->arrInbuiltClasses[$strName])) {
        $this->$strName = new $this->arrInbuiltClasses[$strName];
      }//if

      return $this->$strName;
    }//function
  }//class