<?php
  class MysqliDriver implements iDBDriver {
    private static $arrConnections;
    private        $strConnectionHash;

    private static $intQueryCount;

    public         $affected_rows = 0;
    public         $insert_id = 0;
    public         $connect_errno = 0;
    public         $connect_error = '';
    public         $errno = 0;
    public         $error = '';

    /****************************/
    /* DB Abstraction Functions */
    /****************************/

    public function __construct ($strHost = "localhost", $strUser = "", $strPass = "", $strDBName = "", $intPort = 3306) {
      $this->strConnectionHash = md5($strHost . $strUser . $strPass . $strDBName . $intPort);

      if (!isset(self::$arrConnections[$this->strConnectionHash])) {
        self::$arrConnections[$this->strConnectionHash] = new mysqli($strHost, $strUser, $strPass, $strDBName, $intPort);

        $this->connect_errno = self::$arrConnections[$this->strConnectionHash]->connect_errno;
        $this->connect_error = self::$arrConnections[$this->strConnectionHash]->connect_error;
      }//if
    }//function

    public function query ($strQuery) {
      $dbResults = self::$arrConnections[$this->strConnectionHash]->query($strQuery);

      $this->affected_rows = self::$arrConnections[$this->strConnectionHash]->affected_rows;
      $this->errno = self::$arrConnections[$this->strConnectionHash]->errno;
      $this->error = self::$arrConnections[$this->strConnectionHash]->error;
      $this->insert_id = self::$arrConnections[$this->strConnectionHash]->insert_id;

      self::$intQueryCount++;

      if ($dbResults === true || $dbResults === false) {
        return $dbResults;
      } else {
        return new mySQLiResult($dbResults);
      }//if
    }//function

    public function multi_query ($strQuery) {
      return self::$arrConnections[$this->strConnectionHash]->multi_query($strQuery);
    }//function

    public function escape_string ($strStringToEscape) {
      return self::$arrConnections[$this->strConnectionHash]->escape_string($strStringToEscape);
    }//function

    public function close () {
      return self::$arrConnections[$this->strConnectionHash]->close();
    }//function

    /*******************/
    /* Other Functions */
    /*******************/

    public function getQueryCount () {
      return self::$intQueryCount;
    }//function
  }//class

  class mySQLiResult implements iDBResult {
    private $dbResults;

    public  $num_rows;

    /****************************/
    /* DB Abstraction Functions */
    /****************************/

    public function __construct ($dbResults) {
      $this->dbResults = $dbResults;

      $this->num_rows = $this->dbResults->num_rows;
    }//function

    public function fetch_assoc () {
      return $this->dbResults->fetch_assoc();
    }//function

    public function free () {
      $this->dbResults->free();
    }//function
  }//class