<?php
  abstract class BaseController {
    protected $view = null;

    private $arrInbuiltClasses = array('errors' => 'ErrorRegistry',
                                       'input' => 'FormValidator',
                                       'session' => 'Session',
                                       'auth' => 'Authentication',
                                       'files' => 'FileUpload');

    public function index () {
      $this->view = new DefaultView();

      $this->view->controllername = get_class($this);

      $this->renderView();
    }//function

    public function __get ($strName) {
      if (isset($this->arrInbuiltClasses[$strName])) {
        $this->$strName = new $this->arrInbuiltClasses[$strName];
      }//if

      return $this->$strName;
    }//function

    protected function renderView () {
      foreach ($this->arrInbuiltClasses as $strName => $strClass) {
        $this->view->$strName = $this->$strName;
      }//foreach

      $this->view->render();
    }//function
  }//class