<?php
  use Wings\Lib\System\Dispatcher;
  require_once dirname(__FILE__) . "/../sys/inc.wings.php";

  $objDispatcher = new Dispatcher(isset($_GET['url']) ? $_GET['url'] : '');
  $objDispatcher->execute();