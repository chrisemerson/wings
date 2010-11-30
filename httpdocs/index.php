<?php
  require_once dirname(__FILE__) . "/../app/inc.framework.php";

  $objDispatcher = new Dispatcher(isset($_GET['url']) ? $_GET['url'] : '');
  $objDispatcher->execute();