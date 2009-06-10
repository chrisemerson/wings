<?php
 /********************************************/
 /* FrontController Class - by Chris Emerson */
 /* http://www.cemerson.co.uk/               */
 /*                                          */
 /* Version 0.1                              */
 /* 23rd May 2009                            */
 /********************************************/

 class FrontController {
  private $strURL;

  public function __construct ($strURL = '') {
   $this->strURL = $strURL;
  }//function

  public function execute () {
   $arrURLBits = explode('/', trim($this->strURL, '/'));
  }//function
 }//class
?>