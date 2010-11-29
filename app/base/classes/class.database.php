<?php
  class Database {
    protected $dbConn;
    protected $strTablePrefix;

    public function __construct () {
      $this->openDBConn();
    }//function

    protected function openDBConn () {
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