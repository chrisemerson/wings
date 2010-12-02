<?php
  abstract class Database {
    protected $dbConn;
    protected $strTablePrefix;

    protected function __construct () {
      $this->openDBConn();
    }//function

    private function openDBConn () {
      if (empty($this->dbConn)) {
        $objAppConfig = Config::get('app');

        try {
          $objDBConfig = $objAppConfig->db;
        } catch (ConfigSettingNotFoundException $exException) {
          //Database Connection Error
          Application::showError('database');
        }//try

        $arrDBInfo = parse_url($objDBConfig->uri);
        $this->strTablePrefix = $objDBConfig->prefix;

        $strClassName = strtoupper($arrDBInfo['scheme']) . "Driver";
        $this->dbConn = new $strClassName($arrDBInfo['host'], $arrDBInfo['user'], $arrDBInfo['pass'], trim($arrDBInfo['path'], "/\\"));
      }//if

      return $this->dbConn;
    }//function
  }//class