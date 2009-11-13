<?php
 class ErrorController extends BaseController {
  public function Error404 () {
   echo "404 - Not Found";
  }//function

  public function ErrorControllerNotFound () {
   echo "Controller Not Found";
  }//function

  public function ErrorActionNotFound () {
   echo "Action Not Found";

  }//function

  public function ErrorViewNotFound () {
   echo "View Not Found";
  }//function

  public function ErrorTemplateNotFound () {
   echo "Template Not Found";
  }//function

  public function ErrorDatabase () {
   echo "Database Error";
  }//function

  public function ErrorGeneral () {
   echo "General Error";
  }//function

  public function ErrorSiteOffline () {
   echo "Site Offline";
  }//function
 }//class
?>
