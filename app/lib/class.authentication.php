<?php
  class Authentication {
    private $strUserModel;
    private $strIDField;
    private $strUsernameField;
    private $strPasswordField;

    private $blnRememberedLoginsEnabled;

    private $strRememberedLoginsModel;
    private $strUserIDField;
    private $strTokenField;
    private $strSerialField;
    private $strExpiryField;

    private $strRequireLoginURI;
    private $strRequireLogoutURI;
    private $strRequireReAuthURI;
    private $strRedirectAfterLogin;

    private $strSalt;
    private $intDaysToRemember;

    private $strCookieDomain;
    private $strCookiePath;

    private $strSessionName;
    private $strCookieName;

    private $objSession;

    public function __construct ($strConfigName = 'auth') {
      $objAppConfig = new Config('app');

      $this->strCookieDomain = $objAppConfig->cookie->domain;
      $this->strCookiePath = $objAppConfig->cookie->path;

      $objAuthConfig = new Config($strConfigName);

      $this->strUserModel = $objAuthConfig->users->model;
      $this->strIDField = $objAuthConfig->users->fields->id;
      $this->strUsernameField = $objAuthConfig->users->fields->username;
      $this->strPasswordField = $objAuthConfig->users->fields->password;

      $this->strRequireLoginURI = Application::getFullURI($objAuthConfig->uris->requirelogin);
      $this->strRequireLogoutURI = Application::getFullURI($objAuthConfig->uris->requirelogout);
      $this->strRequireReAuthURI = Application::getFullURI($objAuthConfig->uris->requirereauth);
      $this->strRedirectAfterLogin = Application::getFullURI($objAuthConfig->uris->redirectafterlogin);

      $this->strSalt = $objAuthConfig->salt;

      $this->objSession = new Session($objAuthConfig->sessionname);

      $this->blnRememberedLoginsEnabled = ($objAuthConfig->rememberme->enabled == 1);

      if ($this->blnRememberedLoginsEnabled) {
        $this->strRememberedLoginsModel = $objAuthConfig->rememberme->rememberedlogins->model;
        $this->strUserIDField = $objAuthConfig->rememberme->rememberedlogins->fields->userid;
        $this->strTokenField = $objAuthConfig->rememberme->rememberedlogins->fields->token;
        $this->strSerialField = $objAuthConfig->rememberme->rememberedlogins->fields->serial;
        $this->strExpiryField = $objAuthConfig->rememberme->rememberedlogins->fields->expiry;

        $this->strCookieName = $objAuthConfig->rememberme->defaultcookiename;

        $this->intDaysToRemember = $objAuthConfig->rememberme->remembereddays;

        $this->checkForRememberedLogin();
      }//if
    }//function

    public function attemptLogin ($strUsername, $strPassword, $blnRememberMe = false) {
      $objResultsFilter = new ResultsFilter();
      $objResultsFilter->model($this->strUserModel)
                       ->conditions("`" . $this->strUsernameField . "` = '" . $strUsername . "'");

      $objUsers = new Collection($objResultsFilter);

      if (count($objUsers) == 1) {
        $objUser = $objUsers[0];
        $strPasswordField = $this->strPasswordField;

        if ($this->createHashFromPassword($strPassword) == $objUser->$strPasswordField) {
          $this->objSession->regenerateID();

          $strIDField = $this->strIDField;

          $this->objSession->currentuserid = intval($objUser->$strIDField);
          $this->objSession->loggedin = true;
          $this->objSession->auththissession = true;

          if ($this->blnRememberedLoginsEnabled) {
            $this->clearExpiredRememberedLogins($this->objSession->currentuserid);

            if ($blnRememberMe) {
              $this->rememberLoginAtThisLocation();
            } else {
              $this->forgetLoginAtThisLocation();
            }//if
          }//if

          return true;
        }//if
      }//if

      return false;
    }//function

    public function redirectAfterLogin ($strDefaultRedirection = '') {
      if (isset($this->objSession->redirectafterlogin)) {
        $strRedirect = $this->objSession->redirectafterlogin;

        unset($this->objSession->redirectafterlogin);

        Application::redirect($strRedirect);
      } else {
        if (!empty($strDefaultRedirection)) {
          Application::redirect($strDefaultRedirection);
        } else {
          Application::redirect($this->strRedirectAfterLogin);
        }//if
      }//if
    }//function

    public function logout () {
      if ($this->blnRememberedLoginsEnabled) {
        $this->forgetLoginAtThisLocation();
      }//if

      $this->objSession->destroy();
    }//function

    public function isLoggedIn () {
      return (isset($this->objSession->loggedin) && $this->objSession->loggedin && isset($this->objSession->currentuserid) && !empty($this->objSession->currentuserid));
    }//function

    public function isAuthenticatedThisSession () {
      return (isset($this->objSession->auththissession) && $this->objSession->auththissession);
    }//function

    public function requireLoggedIn ($strURIToRedirectTo = '') {
      if (!$this->isLoggedIn()) {
        $this->objSession->redirectafterlogin = Application::getCurrentPageURI();

        if (!empty($strURIToRedirectTo)) {
          Application::redirect($strURIToRedirectTo);
        } else {
          Application::redirect($this->strRequireLoginURI);
        }//if
      }//if
    }//function

    public function requireLoggedOut ($strURIToRedirectTo = '') {
      if ($this->isLoggedIn()) {
        if (!empty($strURIToRedirectTo)) {
          Application::redirect($strURIToRedirectTo);
        } else {
          Application::redirect($this->strRequireLogoutURI);
        }//if
      }//if
    }//function

    public function requireReAuthentication ($strURIToRedirectTo = '') {
      if (!$this->isLoggedIn() || !$this->isAuthenticatedThisSession()) {
        if (!empty($strURIToRedirectTo)) {
          Application::redirect($strURIToRedirectTo);
        } else {
          Application::redirect($this->strRequireReAuthURI);
        }//if
      }//if
    }//function

    public function getCurrentUser () {
      if ($this->isLoggedIn()) {
        $strUserModel = $this->strUserModel;

        return new $strUserModel($this->objSession->currentuserid);
      } else {
        return false;
      }//if
    }//function

    public function createHashFromPassword ($strPassword) {
      return sha1($this->strSalt . md5($strPassword) . $strPassword . strrev($this->strSalt) . strrev($strPassword) . md5($this->strSalt));
    }//function

    public function clearAllRememberedLogins ($intUserID = 0) {
      if ($this->blnRememberedLoginsEnabled) {
        if (!empty($intUserID) || (isset($this->objSession->currentuserid) && !empty($this->objSession->currentuserid))) {
          if (empty($intUserID)) {
            $intUserID = $this->objSession->currentuserid;
          }//if

          $objResultsFilter = new ResultsFilter();
          $objResultsFilter->model($this->strRememberedLoginsModel)
                           ->conditions("`" . $this->strUserIDField . "` = " . intval($intUserID));

          $objRememberedLogins = new Collection($objResultsFilter);

          $objRememberedLogins->delete();

          $this->forgetLoginAtThisLocation();
        }//if
      }//if
    }//function

    private function clearExpiredRememberedLogins () {
      if ($this->blnRememberedLoginsEnabled) {
        $objResultsFilter = new ResultsFilter();
        $objResultsFilter->model($this->strRememberedLoginsModel)
                         ->conditions("`" . $this->strExpiryField . "` <= '" . date('Y-m-d H:i:s') . "'");

        $objRememberedLogins = new Collection($objResultsFilter);

        $objRememberedLogins->delete();
      }//if
    }//function

    private function checkForRememberedLogin () {
      if ($this->blnRememberedLoginsEnabled) {
        //Don't check if already logged in for this session!
        if (!$this->isLoggedIn()) {
          if (isset($_COOKIE[$this->strCookieName])) {
            list($intUserID, $strToken, $strSerial) = explode("-", $_COOKIE[$this->strCookieName]);

            //First, check for full match, including expiry - if so, authenticate user

            $objResultsFilter = new ResultsFilter();
            $objResultsFilter->model($this->strRememberedLoginsModel)
                             ->conditions("`" . $this->strUserIDField . "` = " . intval($intUserID) . " AND `" . $this->strTokenField . "` = '" . $strToken . "' AND `" . $this->strSerialField . "` = '" . $strSerial . "' AND `" . $this->strExpiryField . "` >= '" . date('Y-m-d H:i:s') . "'");

            $objRememberedLogins = new Collection($objResultsFilter);

            if (count($objRememberedLogins) == 1) {
              $objRememberedLogin = $objRememberedLogins[0];

              $strUserIDField = $this->strUserIDField;
              $strTokenField = $this->strTokenField;
              $strExpiryField = $this->strExpiryField;

              $this->objSession->currentuserid = $objRememberedLogin->$strUserIDField;
              $this->objSession->loggedin = true;

              //Reissue new token
              $objRememberedLogin->$strTokenField = $this->generateRandomString();
              $objRememberedLogin->$strExpiryField = date('Y-m-d H:i:s', strtotime('+ ' . intval($this->intDaysToRemember) . ' days'));
              $objRememberedLogin->save();

              $this->setRememberedLoginCookie($objRememberedLogin);
            } else {
              //Check for series + username match, but not token. Expiry doesn't matter here, as we are simply detecting thefts and logging the user out anyway.

              $objResultsFilter = new ResultsFilter();
              $objResultsFilter->model($this->strRememberedLoginsModel)
                               ->conditions("`" . $this->strUserIDField . "` = " . intval($intUserID) . " AND `" . $this->strTokenField . "` != '" . $strToken . "' AND `" . $this->strSerialField . "` = '" . $strSerial . "'");

              $objRememberedLogins = new Collection($objResultsFilter);

              if (count($objRememberedLogins) == 1) {
                //Cookie theft has taken place! As a precaution, delete cookie, and log user out from all places

                $this->forgetLoginAtThisLocation($intUserID);
                $this->clearAllRememberedLogins($intUserID);
                $this->logout();
              }//if
            }//if
          }//if
        }//if
      }//if
    }//function

    private function rememberLoginAtThisLocation () {
      if ($this->blnRememberedLoginsEnabled && $this->isLoggedIn()) {
        //Generate new series and token identifiers, and add to database. Set as cookie.

        $objRememberedLogin = new RememberedLogin();

        $strUserIDField = $this->strUserIDField;
        $strTokenField = $this->strTokenField;
        $strSerialField = $this->strSerialField;
        $strExpiryField = $this->strExpiryField;

        $objRememberedLogin->$strUserIDField = $this->objSession->currentuserid;
        $objRememberedLogin->$strTokenField = $this->generateRandomString();
        $objRememberedLogin->$strSerialField = $this->generateRandomString();
        $objRememberedLogin->$strExpiryField = date('Y-m-d H:i:s', strtotime('+ ' . intval($this->intDaysToRemember) . ' days'));

        $objRememberedLogin->save();

        $this->setRememberedLoginCookie($objRememberedLogin);
      }//if
    }//function

    private function setRememberedLoginCookie ($objRememberedLogin) {
      if ($this->blnRememberedLoginsEnabled) {
        $strUserIDField = $this->strUserIDField;
        $strTokenField = $this->strTokenField;
        $strSerialField = $this->strSerialField;

        $strCookieValue = implode("-", array($objRememberedLogin->$strUserIDField, $objRememberedLogin->$strTokenField, $objRememberedLogin->$strSerialField));

        setcookie("cookieauth", $strCookieValue, time() + ($this->intDaysToRemember * 86400), $this->strCookiePath, $this->strCookieDomain, Application::isSecure(), true);
      }//if
    }//function

    private function forgetLoginAtThisLocation () {
      if ($this->blnRememberedLoginsEnabled) {
        if (isset($_COOKIE[$this->strCookieName])) {
          list($intUserID, $strToken, $strSerial) = explode("-", $_COOKIE[$this->strCookieName]);

          $objResultsFilter = new ResultsFilter();
          $objResultsFilter->model($this->strRememberedLoginsModel)
                           ->conditions("`" . $this->strUserIDField . "` = " . intval($intUserID) . " AND `" . $this->strTokenField . "` = '" . $strToken . "' AND `" . $this->strSerialField . "` = '" . $strSerial . "'");

          $objRememberedLogins = new Collection($objResultsFilter);
          $objRememberedLogins->delete();
        }//if

        setcookie("cookieauth", "", time() - 3600, $this->strCookiePath, $this->strCookieDomain, Application::isSecure(), true);
      }//if
    }//function

    private function generateRandomString () {
      return sha1(rand(0, 10000) . uniqid() . $_SERVER['REMOTE_ADDR']);
    }//function
  }//class