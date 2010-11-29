<?php
  require_once dirname(__FILE__) . "/../app/inc.framework.php";

  try {
    $objDispatcher = new Dispatcher(isset($_GET['url']) ? $_GET['url'] : '');
    $objDispatcher->execute();
  } catch (Exception $exException) {
    Application::handleUncaughtException($exException);
  }//try
?>