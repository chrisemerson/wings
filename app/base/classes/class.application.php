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

   $strAppBaseURI = 'http';

   if ($blnSecure) {
    $strAppBaseURI .= 's';
   }//if

   $strAppBaseURI .= '://';

   if ($_SERVER['SERVER_PORT'] != '80') {
    $strAppBaseURI .= $objAppConfig->uri->host . ':' . $_SERVER['SERVER_PORT'] . '/' . trim($objAppConfig->uri->path, '/') . '/';
   } else {
    $strAppBaseURI .= $objAppConfig->uri->host . '/' . trim($objAppConfig->uri->path, '/') . '/';
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
 }//class
?>