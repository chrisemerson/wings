<?php
 /***********************************/
 /* Loader Class - by Chris Emerson */
 /* http://www.cemerson.co.uk/      */
 /*                                 */
 /* Version 0.1                     */
 /* 23rd May 2009                   */
 /***********************************/

 class Loader {
  private $strClassName;

  public function __construct ($strClassName) {
   $this->strClassName = $strClassName;
   $this->loadAllModuleInfo();
  }//function

  public function load () {
   //Priority: DB Drivers, Views, Models, Controllers, Lib

   if (strtolower(substr($this->strClassName, -10)) == "controller") {
    $strFileToLoad = APP_BASE_PATH . "controllers/controller." . strtolower(substr($this->strClassName, 0, -10)) . ".php";
   } else if (strtolower(substr($this->strClassName, -6)) == 'driver') {
    $strFileToLoad = APP_BASE_PATH . "dbdrivers/db." . strtolower(substr($this->strClassName, 0, -6)) . ".php";
   }//if

   require_once $strFileToLoad;
  }//function

  private function loadAllModuleInfo () {
   //Load order: dbdrivers,controllers,views,models,lib,thirdparty
  }//function
 }//class
?>