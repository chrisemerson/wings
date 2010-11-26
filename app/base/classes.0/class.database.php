<?php
  class Database {
    protected $dbConn;

    function openDBConn () {
      if (empty($this->dbConn)) {
        $objEnvConfig = Config::get('environment');

        try {
          $objDBConfig = Config::get($objEnvConfig->dbconfig);
        } catch (ConfigSettingNotFoundException $exException) {
          $objDBConfig = Config::get('db');
        }//try

        $strClassName = $objDBConfig->driver . "Driver";
        $this->dbConn = new $strClassName($objDBConfig->host, $objDBConfig->user, $objDBConfig->pass, $objDBConfig->name);
      }//if

      return $this->dbConn;
    }//function
  }//class