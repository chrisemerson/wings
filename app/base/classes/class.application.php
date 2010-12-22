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

      foreach ($arrQSA as $strKey => $strValue) {
        $arrFinalQueryString[$strKey] = $strValue;
      }//foreach

      $arrQueryStringPieces = array();

      foreach ($arrFinalQueryString as $strKey => $strValue) {
        $arrQueryStringPieces[] = $strKey . "=" . $strValue;
      }//foreach

      if (($_SERVER['SERVER_PORT'] != '80') && ($_SERVER['SERVER_PORT'] != '443')) {
        $strCurrentPageURI .= $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $arrURL['path'] . '?' . implode('&', $arrQueryStringPieces);
      } else {
        $strCurrentPageURI .= $_SERVER['SERVER_NAME'] . $arrURL['path'] . '?' . implode('&', $arrQueryStringPieces);
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

    public static function getFullURI ($strPath = "/", $blnSecure = false) {
      return self::getBaseURI($blnSecure) . trim($strPath, '/') . '/';
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

    public static function showError ($strErrorType, $strErrorText = '', $strLoggedError = '') {
      $objErrorController = new ErrorController();
      $strAction = "show" . ucwords(strtolower($strErrorType)) . "Error";

      call_user_func(array($objErrorController, $strAction), $strErrorText);

      //We don't want to continue beyond the error showing
      self::exitApp();
    }//function

    public static function handleUncaughtException ($exException) {
      echo "<h1>Uncaught " . get_class($exException) . "</h1>\n\n";
      echo "<p><b>" . $exException->getFile() . "(" . $exException->getLine() . ")</b></p>";

      $arrTrace = $exException->getTrace();

      echo "<ol>";

      foreach ($arrTrace as $arrTraceStep) {
        echo "<li>\n";
        echo "  <dl>\n";

        if (isset($arrTraceStep['file'])) {
          echo "    <dt><b>File (Line)</b></dt>\n";
          echo "    <dd>" . $arrTraceStep['file'];

          if (isset($arrTraceStep['line'])) {
            echo " (" . $arrTraceStep['line'] . ")";
          }//if

          echo "\n\n";
        }//if

        if (isset($arrTraceStep['class'])) {
          echo "    <dt><b>Call</b></dt>\n";
          echo "    <dd>" . $arrTraceStep['class'] . $arrTraceStep['type'] . $arrTraceStep['function'];

          if (!empty($arrTraceStep['args'])) {
            echo "(" . implode(", ", array_map('strval', $arrTraceStep['args'])) . ")</dd>";
          }//if
        }//if

        echo "  </dl>\n";
        echo "</li>\n";
      }//foreach

      echo "</ol>";
    }//function
  }//class