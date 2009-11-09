<?php
 class TestController extends BaseController {
  public function TestMethod () {
   echo "Got To TestMethod";
  }//function

  public function TestMethod2 ($strText) {
   echo $strText;
  }//function
 }//class
?>