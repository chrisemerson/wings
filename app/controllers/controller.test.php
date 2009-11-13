<?php
 class TestController extends BaseController {
  public function TestAction () {
   echo "Got To TestAction";
  }//function

  public function TestAction2 ($strText) {
   echo $strText;
  }//function
 }//class
?>