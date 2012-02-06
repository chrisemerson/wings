<?php
  abstract class Database {
    protected $dbConn;
    protected $strTablePrefix;

    protected function __construct () {
      $this->openDBConn();
    }//function

    private function openDBConn () {
      if (empty($this->dbConn)) {
        $objAppConfig = new Config('app');

        try {
          $objDBConfig = $objAppConfig->db;
        } catch (ConfigSettingNotFoundException $exException) {
          //Database Connection Error
          Application::showError('database', '', 'No Database Config Details Found');
        }//try

        $arrDBInfo = parse_url($objDBConfig->uri);
        $this->strTablePrefix = $objDBConfig->prefix;

        $strClassName = ucwords($arrDBInfo['scheme']) . "Driver";
        $this->dbConn = new $strClassName($arrDBInfo['host'], $arrDBInfo['user'], $arrDBInfo['pass'], trim($arrDBInfo['path'], "/\\"));
      }//if

      return $this->dbConn;
    }//function

    public function startTransaction () {
      $this->dbConn->query("START TRANSACTION;");
    }//function

    public function commit () {
      $this->dbConn->query("COMMIT;");
    }//function

    public function rollback () {
      $this->dbConn->query("ROLLBACK;");
    }//function
  }//class