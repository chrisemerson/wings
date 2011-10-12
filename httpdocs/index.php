<?php
  require_once dirname(__FILE__) . "/../app/inc.wings.php";

  $objDispatcher = new Dispatcher(isset($_GET['url']) ? $_GET['url'] : '');
  $objDispatcher->execute();