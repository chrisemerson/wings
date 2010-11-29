<?php
  class Application {
    public static function getCurrentPageURI () {
      $strCurrentPageURI = 'http';

      if (self::isSecure()) {
        $strCurrentPageURI .= 's';
      }//if

      $strCurrentPageURI .= '://';

      if ($_SERVER['SERVER_PORT'] != '80') {
        $strCurrentPageURI .= $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];
      } else {
        $strCurrentPageURI .= $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
      }//if

      return $strCurrentPageURI;
    }//function

    public static function getBaseURI ($blnSecure = false) {
      $objAppConfig = Config::get('app');
      $objEnvConfig = Config::get('environment');

      $strCurrentEnvironment = self::getEnvironment();

      $strAppBaseURI = 'http';

      if ($blnSecure) {
        $strAppBaseURI .= 's';
      }//if

      $strAppBaseURI .= '://';

      $strPath = $objEnvConfig->uri->path;

      if (!empty($strPath)) {
        $strPath = trim($strPath, '/') . '/';
      }//if

      if ($_SERVER['SERVER_PORT'] != '80') {
        $strAppBaseURI .= $objEnvConfig->uri->host . ':' . $_SERVER['SERVER_PORT'] . '/' . $strPath;
      } else {
        $strAppBaseURI .= $objEnvConfig->uri->host . '/' . $strPath;
      }//if

      return $strAppBaseURI;
    }//function

    public static function getBasePath () {
      return realpath(dirname(__FILE__) . '/../../') . '/';
    }//function

    public static function isSecure () {
     return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on');
    }//function

    public static function redirect ($strURL) {
      header('Location: ' . $strURL);
      self::exitApp();
    }//function

    public static function getEnvironment () {
      $objAppConfig = Config::get('app');

      if (isset($_SERVER[$objAppConfig->environmentvar])) {
        $strEnvironment = $_SERVER[$objAppConfig->environmentvar];

        if (!empty($strEnvironment)) {
          return $strEnvironment;
        }//if
      }//if

      return 'default';
    }//function

    public static function exitApp ($strMessage = "") {
      session_write_close();
      exit($strMessage);
    }//function

    public static function showError ($strErrorType) {
      $objErrorController = new ErrorController();
      $strAction = "Error" . ucwords($strErrorType);

      call_user_func_array(array($objErrorController, $strAction));
    }//function
  }//class