<?php
 /**************************************************/
 /* MySQL Improved Driver Class - by Chris Emerson */
 /* http://www.cemerson.co.uk/                     */
 /*                                                */
 /* Version 0.1                                    */
 /* 28th May 2009                                  */
 /**************************************************/

 class MySQLiDriver implements iDBDriver {
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
   $this->resConnection = new mysqli($strHost, $strUser, $strPass, $strDBName, $intPort);

   $this->connect_errno = $this->resConnection->connect_errno;
   $this->connect_error = $this->resConnection->connect_error;
  }//function

  public function __destruct () {
   $this->close();
  }//function

  public function query ($strQuery) {
   $dbResults = $this->resConnection->query($strQuery);

   $this->affected_rows = $this->resConnection->affected_rows;
   $this->errno = $this->resConnection->errno;
   $this->error = $this->resConnection->error;
   $this->insert_id = $this->resConnection->insert_id;

   $this->intQueryCount++;

   return new mySQLiResult($dbResults);
  }//function

  public function escape_string ($strStringToEscape) {
   return $this->resConnection->escape_string($strStringToEscape);
  }//function

  public function close () {
   return $this->resConnection->close();
  }//function

  /*******************/
  /* Other Functions */
  /*******************/

  public function getQueryCount () {
   return $this->intQueryCount;
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
?>