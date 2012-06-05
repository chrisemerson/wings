<?php
  namespace Wings;

  //Declare autoload function
  function autoload ($strFQClassName) {
    $arrNSPieces = explode("\\", $strFQClassName);
    unset($arrNSPieces[0]);

    $strClassName = array_pop($arrNSPieces);

    $arrNSPieces = array_map('strtolower', $arrNSPieces);

    if (isset($arrNSPieces[1]) && ($arrNSPieces[1] == "controllers")) {
      $strBaseFilename = "controller." . substr($strClassName, 0, -10) . ".php";
    } else if (isset($arrNSPieces[1]) && ($arrNSPieces[1] == "models")) {
      $objModelRegistry = new \Wings\Lib\System\ModelRegistry();

      $strBaseFilename = "model." . $strClassName . ".php";
      $strFilename = implode("\\", $arrNSPieces) . "\\" . $strBaseFilename;

      if ((!file_exists(realpath(dirname(__FILE__) . "/../app/" . $strFilename))) && (!file_exists(realpath(dirname(__FILE__) . "/" . $strFilename))) && $objModelRegistry->isModel($strClassName)) {
        eval('namespace Wings\\Models; class ' . $strClassName . ' extends \Wings\Lib\System\Model {}');

        return true;
      }//if
    } else if (isset($arrNSPieces[1]) && ($arrNSPieces[1] == "collections") && substr($strClassName, -10, 10) == "Collection") {
      $objModelRegistry = new \Wings\Lib\System\ModelRegistry();

      $strModelName = substr($strClassName, 0, -10);

      if ($objModelRegistry->isModel($strModelName)) {
        eval('namespace Wings\\Collections; class ' . $strClassName . ' extends \Wings\Lib\System\Collection { public function __construct ($strSQLString = \'\') { parent::__construct(\'' . $strModelName . '\', $strSQLString); }}');

        return true;
      } else {
        return false;
      }//if
    } else if (isset($arrNSPieces[1]) && ($arrNSPieces[1] == "views")) {
      $strBaseFilename = "view." . substr($strClassName, 0, -4) . ".php";
    } else if (isset($arrNSPieces[1]) && isset($arrNSPieces[2]) && ($arrNSPieces[1] == "lib") && ($arrNSPieces[2] == "interfaces")) {
      $strBaseFilename = "interface." . $strClassName . ".php";
    } else if (isset($arrNSPieces[1]) && isset($arrNSPieces[2]) && ($arrNSPieces[1] == "lib") && ($arrNSPieces[2] == "dbdrivers")) {
      $strBaseFilename = "db." . substr($strClassName, 0, -6) . ".php";
    } else {
      $strBaseFilename = "class." . $strClassName . ".php";
    }//if

    $strFilename = implode("\\", $arrNSPieces) . "\\" . $strBaseFilename;

    //If a version in the app folder exists, use that, otherwise look for one in sys
    if (file_exists(realpath(dirname(__FILE__) . "/../app/" . $strFilename))) {
      require_once realpath(dirname(__FILE__) . "/../app/" . $strFilename);

      return true;
    } else if (file_exists(realpath(dirname(__FILE__) . "/" . $strFilename))) {
      require_once realpath(dirname(__FILE__) . "/" . $strFilename);

      return true;
    }//if

    return false;
  }//function

  spl_autoload_register('Wings\autoload');

  //Handle uncaught exceptions
  set_exception_handler(array('\Wings\Lib\System\Application', 'handleUncaughtException'));

  //If magic quotes is turned on, undo its effects
  if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
    $_GET    = array_map('stripslashes', $_GET);
    $_POST   = array_map('stripslashes', $_POST);
    $_COOKIE = array_map('stripslashes', $_COOKIE);
  }//if

  //Load all files in the app's bootstrap/ folder
  function loadFilesInDirectory ($strDirectory) {
    $arrFilesInBaseDir = scandir($strDirectory);

    foreach ($arrFilesInBaseDir as $strFilename) {
      if ($strFilename{0} != '.') {
        $strFullFilename = $strDirectory . "/" . $strFilename;

        if (is_file($strFullFilename)) {
          require_once $strFullFilename;
        } else if (is_dir($strFullFilename)) {
          loadFilesInDirectory($strFullFilename);
        }//if
      }//if
    }//foreach
  }//function

  loadFilesInDirectory(realpath(dirname(__FILE__) . "/../app/bootstrap"));

  //Check to see if DB Sessions is turned on, and use them if so
  $objDBSessionsConfig = new Lib\System\Config('dbsessions');

  if ($objDBSessionsConfig->enabled) {
    session_set_save_handler(array('Lib\Util\DatabaseSession', 'open'),
                             array('Lib\Util\DatabaseSession', 'close'),
                             array('Lib\Util\DatabaseSession', 'read'),
                             array('Lib\Util\DatabaseSession', 'write'),
                             array('Lib\Util\DatabaseSession', 'destroy'),
                             array('Lib\Util\DatabaseSession', 'gc'));

    register_shutdown_function('session_write_close');
  }//if

  unset($objDBSessionsConfig);

  //Start Session
  session_start();