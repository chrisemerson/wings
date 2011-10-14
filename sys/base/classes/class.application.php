<?php
  class Application {
    public static function getCurrentPageURI ($arrQSA = array()) {
      $strCurrentPageURI = 'http';

      if (self::isSecure()) {
        $strCurrentPageURI .= 's';
      }//if

      $strCurrentPageURI .= '://';

      $arrURL = parse_url($_SERVER['REQUEST_URI']);

      $arrFinalQueryString = array();

      if (isset($arrURL['query'])) {
        $arrQueryString = explode('&', $arrURL['query']);

        foreach ($arrQueryString as $strQueryString) {
          list($strKey, $strValue) = explode('=', $strQueryString);

          $arrFinalQueryString[$strKey] = $strValue;
        }//foreach
      }//if

      if (is_array($arrQSA)) {
        foreach ($arrQSA as $strKey => $strValue) {
          $arrFinalQueryString[$strKey] = $strValue;
        }//foreach
      }//if

      $arrQueryStringPieces = array();

      foreach ($arrFinalQueryString as $strKey => $strValue) {
        $arrQueryStringPieces[] = $strKey . "=" . $strValue;
      }//foreach

      if (($_SERVER['SERVER_PORT'] != '80') && ($_SERVER['SERVER_PORT'] != '443')) {
        $strCurrentPageURI .= $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $arrURL['path'];
      } else {
        $strCurrentPageURI .= $_SERVER['SERVER_NAME'] . $arrURL['path'];
      }//if

      if (count($arrQueryStringPieces) && $arrQSA !== false) {
        $strCurrentPageURI .= '?' . implode('&', $arrQueryStringPieces);
      }//if

      return $strCurrentPageURI;
    }//function

    public static function getBaseURI ($blnSecure = false) {
      $objAppConfig = new Config('app');

      $strAppBaseURI = 'http';

      if ($blnSecure) {
        $strAppBaseURI .= 's';
      }//if

      $strAppBaseURI .= '://';

      $strPath = $objAppConfig->uri->path;

      if (!empty($strPath)) {
        $strPath = trim($strPath, '/') . '/';
      }//if

      if ($_SERVER['SERVER_PORT'] != '80') {
        $strAppBaseURI .= $objAppConfig->uri->host . ':' . $_SERVER['SERVER_PORT'] . '/' . $strPath;
      } else {
        $strAppBaseURI .= $objAppConfig->uri->host . '/' . $strPath;
      }//if

      return $strAppBaseURI;
    }//function

    public static function getCurrentPageURIRelativeToBase ($arrQSA = array()) {
      $strCurrentPageURI = self::getCurrentPageURI($arrQSA);
      $strBaseURI = self::getBaseURI(self::isSecure());

      if ($strBaseURI == substr($strCurrentPageURI, 0, strlen($strBaseURI))) {
        return substr($strCurrentPageURI, strlen($strBaseURI));
      } else {
        return $strCurrentPageURI;
      }//if

      return str_replace($strBaseURI, '', $strCurrentPageURI);
    }//function

    public static function getFullURI ($strPath = "/", $blnSecure = false) {
      return self::getBaseURI($blnSecure) . trim($strPath, '/') . '/';
    }//function

    public static function getBasePath () {
      return realpath(dirname(__FILE__) . '/../../../') . '/';
    }//function

    public static function isSecure () {
      return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on');
    }//function

    public static function doNotTrack () {
      return (isset($_SERVER['HTTP_DNT']) && ($_SERVER['HTTP_DNT'] == 1));
    }//function

    public static function redirect ($strURL) {
      header('Location: ' . $strURL);
      self::exitApp();
    }//function

    public static function getEnvironment () {
      if (isset($_SERVER['APP_ENVIRONMENT'])) {
        $strEnvironment = $_SERVER['APP_ENVIRONMENT'];

        if (!empty($strEnvironment)) {
          return $strEnvironment;
        }//if
      }//if

      return false;
    }//function

    public static function exitApp ($strMessage = "") {
      session_write_close();
      exit($strMessage);
    }//function

    public static function showError ($strErrorType, $mixErrorContent = '', $strLoggedError = '') {
      $objErrorController = new ErrorController();
      $strAction = "show" . ucwords(strtolower($strErrorType)) . "Error";

      call_user_func(array($objErrorController, $strAction), $mixErrorContent);

      //We don't want to continue beyond the error showing
      self::exitApp();
    }//function

    public static function handleUncaughtException ($exException) {
      self::showError('Exception', $exException);
    }//function
  }//class
