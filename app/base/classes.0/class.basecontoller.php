<?php
 class BaseController {
   protected $view = null;

   private $arrInbuiltClasses = array('errors' => 'ErrorHandler',
                                      'input' => 'InputFilter',
                                      'session' => 'Session');

   public function __get ($strName) {
     if (isset($this->arrInbuiltClasses[$strName])) {
       $this->$strName = new $this->arrInbuiltClasses[$strName];
     }//if

     return $this->$strName;
   }//function

   public function index () {
     echo "Default Text Here";
   }//function
 }//class