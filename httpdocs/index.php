<?php
 require_once dirname(__FILE__) . "/../app/inc.framework.php";

 try {
  $objDispatcher = new Dispatcher($_GET['url']);
  $objDispatcher->execute();
 } catch (Exception $exException) {
  print_r($exException->getMessage());
 }//try
?>