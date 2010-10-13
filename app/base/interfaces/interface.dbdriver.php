<?php
 /******************************************/
 /* DB Driver Interface - by Chris Emerson */
 /* http://www.cemerson.co.uk/             */
 /*                                        */
 /* Version 0.1                            */
 /* 28th May 2009                          */
 /******************************************/

 interface iDBDriver {
   public function __construct ($strHost = "localhost", $strUser = "", $strPass = "", $strDBName = "", $intPort = 3306);
   public function query ($strQuery);
   public function escape_string ($strStringToEscape);
   public function close ();
 }//interface

 interface iDBResult {
   public function __construct ($dbResults);
   public function fetch_assoc ();
   public function free ();
 }//interface