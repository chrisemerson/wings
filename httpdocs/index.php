<?php
  require_once dirname(__FILE__) . "/../app/inc.framework.php";

  set_exception_handler(array('Application', 'handleUncaughtException'));

  $objDispatcher = new Dispatcher(isset($_GET['url']) ? $_GET['url'] : '');
  $objDispatcher->execute();