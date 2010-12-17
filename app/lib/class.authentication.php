<?php
  class Authentication {
    private $strUserModel;
    private $strIDField;
    private $strUsernameField;
    private $strPasswordField;

    private $strRememberedLoginsModel;
    private $strUserIDField;
    private $strTokenField;
    private $strSerialField;

    private $strSalt;
    private $intDaysToRemember;

    private $strCookieDomain;
    private $strCookiePath;

    private $objSession;

    public function __construct () {
      $this->objSession = new Session();

      $objAppConfig = new Config('app');

      $this->strCookieDomain = $objAppConfig->cookie->domain;
      $this->strCookiePath = $objAppConfig->cookie->path;

      $objAuthConfig = new Config('auth');

      $this->strUserModel = $objAuthConfig->users->model;
      $this->strIDField = $objAuthConfig->users->fields->id;
      $this->strUsernameField = $objAuthConfig->users->fields->username;
      $this->strPasswordField = $objAuthConfig->users->fields->password;

      $this->strRememberedLoginsModel = $objAuthConfig->rememberedlogins->model;
      $this->strUserIDField = $objAuthConfig->rememberedlogins->fields->userid;
      $this->strTokenField = $objAuthConfig->rememberedlogins->fields->token;
      $this->strSerialField = $objAuthConfig->rememberedlogins->fields->serial;

      $this->strSalt = $objAuthConfig->salt;
      $this->intDaysToRemember = $objAuthConfig->remembereddays;

      $this->checkForRememberedLogin();
    }//function

    public function attemptLogin ($strUsername, $strPassword, $blnRememberLogin = false) {
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

          if ($blnRememberLogin) {
            $this->rememberLoginAtThisLocation();
          } else {
            $this->forgetLoginAtThisLocation();
          }//if

          return true;
        }//if
      }//if

      return false;
    }//function

    public function logout () {
      $this->forgetLoginAtThisLocation();
      $this->objSession->destroy();
    }//function

    public function isLoggedIn () {
      return (isset($this->objSession->loggedin) && $this->objSession->loggedin && isset($this->objSession->currentuserid) && !empty($this->objSession->currentuserid));
    }//function

    public function isAuthenticatedThisSession () {
      return (isset($this->objSession->auththissession) && $this->objSession->auththissession);
    }//function

    public function requireLoggedIn ($strURLToRedirectTo) {
      if (!$this->isLoggedIn()) {
        $this->session->redirectafterlogin = Application::getCurrentPageURI();

        Application::redirect($strURLToRedirectTo);
      }//if
    }//function

    public function requireLoggedOut ($strURLToRedirectTo) {
      if ($this->isLoggedIn()) {
        Application::redirect($strURLToRedirectTo);
      }//if
    }//function

    public function requireReAuthentication ($strURLToRedirectTo) {
      if (!$this->isLoggedIn() || !$this->isAuthenticatedThisSession()) {
        Application::redirect($strURLToRedirectTo);
      }//if
    }//function

    public function getCurrentUser () {
      if ($this->isLoggedIn()) {
        $strUserModel = $this->strUserModel;

        return new $strUserModel($this->objSession->currentuserid);
      } else {
        return false;
      }//if
    }//if

    public function createHashFromPassword ($strPassword) {
      return sha1($this->strSalt . md5($strPassword) . $strPassword . strrev($this->strSalt) . strrev($strPassword) . md5($this->strSalt));
    }//function

    public function clearAllRememberedLogins ($intUserID = 0) {
      if (!empty($intUserID) || (isset($this->objSession->currentuserid) && !empty($this->objSession->currentuserid))) {
        if (empty($intUserID)) {
          $intUserID = $this->objSession->currentuserid;
        }//if

        $objResultsFilter = new ResultsFilter();
        $objResultsFilter->model('RememberedLogin')
                         ->conditions("`user_id` = " . intval($intUserID));

        $objRememberedLogins = new Collection($objResultsFilter);

        $objRememberedLogins->delete();
      }//if

      $this->logout();
    }//function

    private function checkForRememberedLogin () {
      //Don't check if already logged in for this session!
      if (!$this->isLoggedIn()) {
        if (isset($_COOKIE['cookieauth'])) {
          list($intUserID, $strToken, $strSerial) = explode("-", $_COOKIE['cookieauth']);

          //First, check for full match - if so, authenticate user

          $objResultsFilter = new ResultsFilter();
          $objResultsFilter->model('RememberedLogin')
                           ->conditions("`user_id` = " . intval($intUserID) . " AND `remembered_login_token` = '" . $strToken . "' AND `remembered_login_serial` = '" . $strSerial . "'");

          $objRememberedLogins = new Collection($objResultsFilter);

          if (count($objRememberedLogins) == 1) {
            $objRememberedLogin = $objRememberedLogins[0];

            $this->objSession->currentuserid = $objRememberedLogin->user_id;
            $this->objSession->loggedin = true;

            //Reissue new token
            $objRememberedLogin->remembered_login_token = $this->generateRandomNumber();
            $objRememberedLogin->remembered_login_expiry = date('Y-m-d H:i:s', strtotime('+30days'));
            $objRememberedLogin->save();

            $this->setRememberedLoginCookie($objRememberedLogin);
          } else {
            //Check for series + username match, but not token

            $objResultsFilter = new ResultsFilter();
            $objResultsFilter->model('RememberedLogin')
                             ->conditions("`user_id` = " . intval($intUserID) . " AND `remembered_login_token` != '" . $strToken . "' AND `remembered_login_serial` = '" . $strSerial . "'");

            $objRememberedLogins = new Collection($objResultsFilter);

            if (count($objRememberedLogins) == 1) {
              //Cookie theft has taken place! As a precaution, delete cookie, and log user out from all places

              $this->forgetLoginAtThisLocation($intUserID);
              $this->clearAllRememberedLogins($intUserID);
            }//if
          }//if
        }//if
      }//if
    }//function

    private function rememberLoginAtThisLocation () {
      if ($this->isLoggedIn()) {
        //Generate new series and token identifiers, and add to database. Set as cookie.

        $objRememberedLogin = new RememberedLogin();

        $objRememberedLogin->user_id = $this->objSession->currentuserid;
        $objRememberedLogin->remembered_login_token = $this->generateRandomNumber();
        $objRememberedLogin->remembered_login_serial = $this->generateRandomNumber();
        $objRememberedLogin->remembered_login_expiry = date('Y-m-d H:i:s', strtotime('+30days'));

        $objRememberedLogin->save();

        $this->setRememberedLoginCookie($objRememberedLogin);
      }//if
    }//function

    private function setRememberedLoginCookie ($objRememberedLogin) {
      $strCookieValue = implode("-", array($objRememberedLogin->user_id, $objRememberedLogin->remembered_login_token, $objRememberedLogin->remembered_login_serial));

      setcookie("cookieauth", $strCookieValue, time() + ($this->intDaysToRemember * 86400), $this->strCookiePath, $this->strCookieDomain, Application::isSecure());
    }//function

    private function forgetLoginAtThisLocation () {
      if (isset($_COOKIE['cookieauth'])) {
        list($intUserID, $strToken, $strSerial) = explode("-", $_COOKIE['cookieauth']);

        $objResultsFilter = new ResultsFilter();
        $objResultsFilter->model('RememberedLogin')
                         ->conditions("`user_id` = " . intval($intUserID) . " AND `remembered_login_token` = '" . $strToken . "' AND `remembered_login_serial` = '" . $strSerial . "'");

        $objRememberedLogins = new Collection($objResultsFilter);
        $objRememberedLogins->delete();
      }//if

      setcookie("cookieauth", "", time() - 3600, $this->strCookiePath, $this->strCookieDomain, Application::isSecure());
    }//function

    private function generateRandomNumber () {
      return sha1(rand(0, 10000) . uniqid() . $_SERVER['REMOTE_ADDR']);
    }//function
  }//class