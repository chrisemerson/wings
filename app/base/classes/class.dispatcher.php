<?php
 /***************************************/
 /* Dispatcher Class - by Chris Emerson */
 /* http://www.cemerson.co.uk/          */
 /*                                     */
 /* Version 0.1                         */
 /* 23rd May 2009                       */
 /***************************************/

 class Dispatcher {
  private $strURL;
  private $arrRoutes;

  public function __construct ($strURL = '') {
   $this->strURL = $strURL;
   $this->loadRoutes();
  }//function

  public function loadRoutes () {

  }//function

  public function execute () {
   $arrURLBits = explode('/', trim($this->strURL, '/'));
   print_r($arrURLBits);
  }//function
 }//class
?>