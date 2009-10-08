<?php
 require_once dirname(__FILE__) . "/../app/inc.PROJECTNAME.php";

 try {
  $objFrontController = new FrontController($_GET['url']);
  $objFrontController->execute();
 } catch (Exception $exException) {
  print_r($exException->getMessage());
 }//try
?>