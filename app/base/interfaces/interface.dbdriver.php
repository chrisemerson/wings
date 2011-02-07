<?php
  interface iDBDriver {
    public function __construct ($strHost = "localhost", $strUser = "", $strPass = "", $strDBName = "", $intPort = 3306);
    public function query ($strQuery);
    public function multi_query ($strQuery);
    public function escape_string ($strStringToEscape);
    public function close ();
  }//interface

  interface iDBResult {
    public function __construct ($dbResults);
    public function fetch_assoc ();
    public function free ();
  }//interface