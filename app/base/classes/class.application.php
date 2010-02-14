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
     return ($_SERVER['HTTPS'] == 'on');
    }//function

    public static function redirect ($strURL) {
      header('Location: ' . $strURL);
      session_write_close();
      exit();
    }//function

    public static function getEnvironment () {
      $objAppConfig = Config::get('app');

      return $_SERVER[$objAppConfig->environmentvar];
    }//function
  }//class
?>