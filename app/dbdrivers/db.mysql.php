<?php
 /*****************************************/
 /* MySQL Driver Class - by Chris Emerson */
 /* http://www.cemerson.co.uk/            */
 /*                                       */
 /* Version 0.1                           */
 /* 28th May 2009                         */
 /*****************************************/

 class MySQLDriver implements iDBDriver {
  private        $resConnection;
  private static $intQueryCount;

  public         $affected_rows;
  public         $insert_id;
  public         $connect_errno;
  public         $connect_error;
  public         $errno;
  public         $error;

  /****************************/
  /* DB Abstraction Functions */
  /****************************/

  public function __construct ($strHost = "localhost", $strUser = "", $strPass = "", $strDBName = "", $intPort = 3306) {
   $this->resConnection = mysql_connect($strHost . ':' . $intPort, $strUser, $strPass);

   $this->connect_errno = mysql_errno($this->resConnection);
   $this->connect_error = mysql_error($this->resConnection);

   if ($this->connect_errno == 0) {
    mysql_select_db($strDBName, $this->resConnection);

    $this->connect_errno = mysql_errno($this->resConnection);
    $this->connect_error = mysql_error($this->resConnection);
   }//if
  }//function

  public function __destruct () {
   $this->close();
  }//function

  public function query ($strQuery) {
   $dbResults = mysql_query($strQuery, $this->resConnection);

   $this->affected_rows = mysql_affected_rows($this->resConnection);
   $this->errno = mysql_errno($this->resConnection);
   $this->error = mysql_error($this->resConnection);
   $this->insert_id = mysql_insert_id($this->resConnection);

   $this->intQueryCount++;

   if ($dbResults === true || $dbResults === false) {
     return $dbResults;
   } else {
     return new mySQLResult($dbResults);
   }//if
  }//function

  public function escape_string ($strStringToEscape) {
   return mysql_real_escape_string($strStringToEscape, $this->resConnection);
  }//function

  public function close () {
   return mysql_close($this->resConnection);
  }//function

  /*******************/
  /* Other Functions */
  /*******************/

  public function getQueryCount () {
   return $this->intQueryCount;
  }//function
 }//class



 class mySQLResult implements iDBResult {
  private $dbResults;

  public $num_rows;

  /****************************/
  /* DB Abstraction Functions */
  /****************************/

  public function __construct ($dbResults) {
   $this->dbResults = $dbResults;

   $this->num_rows = mysql_num_rows($this->dbResults);
  }//function

  public function fetch_assoc () {
   return mysql_fetch_assoc($this->dbResults);
  }//function

  public function free () {
   mysql_free_result($this->dbResults);
  }//function
 }//class