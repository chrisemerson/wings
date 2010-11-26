<?php
  class Database {
    protected $dbConn;

    protected function openDBConn () {
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

    protected function prepareData ($strData, $strFieldName) {
      $strDataType = $this->objSchema->getDataType($strFieldName);

      switch ($strDataType) {
        case 'int':
        case 'tinyint':
        case 'decimal':
          return $strData;
          break;

        case 'date':
        case 'datetime':
        case 'time':
        case 'text':
        case 'varchar':
          return "'" . $this->dbConn->escape_string($strData) . "'";
          break;
      }//switch
    }//function
  }//class