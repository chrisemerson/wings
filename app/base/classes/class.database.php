<?php
  class Database {
    protected $dbConn;
    protected $strTablePrefix;

    protected function openDBConn () {
      if (empty($this->dbConn)) {
        $objEnvConfig = Config::get('environment');

        try {
          $objDBConfig = Config::get($objEnvConfig->db);
        } catch (ConfigSettingNotFoundException $exException) {
          //Database Connection Error
          Application::showError('database');
        }//try

        $strDBURI = $objDBConfig->uri;
        $this->strTablePrefix = $objDBConfig->prefix;

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