<?php
 /**************************************/
 /* BaseModel Class - by Chris Emerson */
 /* http://www.cemerson.co.uk/         */
 /*                                    */
 /* Version 0.1                        */
 /* 23rd May 2009                      */
 /**************************************/

 class BaseModel {
  private   $dbConn;

  private   $strTableName;
  private   $arrFields = array();
  private   $arrCurrentData = array();
  private   $arrNewData = array();

  protected $strSchema;

  public function __construct () {
   $objDBConfig = Config::get('db');
   $strClassName = $objDBConfig->driver . "Driver";
   $this->dbConn = new $strClassName($objDBConfig->host, $objDBConfig->user, $objDBConfig->pass, $objDBConfig->name);

   $this->loadSchema();
  }//function

  private function loadSchema () {
   $strSchemaFile = APP_BASE_PATH . "schemas/" . strtolower($this->strSchema) . ".xml";
   $arrSchemaFile = simplexml_load_file($strSchemaFile);

   //Table Name
   $this->strTableName = (string) $arrSchemaFile['name'];

   //Fields
   foreach ($arrSchemaFile->fields->field as $objField) {
    $arrField = array();
    $arrField['title'] = (string) $objField->title;
    $arrField['type'] = (string) $objField->type;

    if (isset($objField->length)) {
     $arrField['length'] = (int) $objField->length;
    }//if

    if (isset($objField->default)) {
     $arrField['default'] = (string) $objField->default;
    }//if

    if (isset($objField->null)) {
     $arrField['null'] = (strtolower((string) $objField->null) == "yes");
    }//if

    if (isset($objField->primary_key)) {
     $arrField['PK'] = (strtolower((string) $objField->primary_key) == "yes");
    }//if

    if (isset($objField->autonumber)) {
     $arrField['Autonumber'] = (strtolower((string) $objField->autonumber) == "yes");
    }//if

    $this->arrFields[(string) $objField['name']] = $arrField;
    $this->arrCurrentData[(string) $objField['name']] = null;
   }//foreach

   $this->arrNewData = $this->arrCurrentData;
  }//function

  public function __get ($strFieldName) {
   if (isset($this->arrNewData[$strFieldName])) {
    return $this->arrNewData[$strFieldName];
   } else {
    throw new FieldNotFoundException;
   }//if
  }//function

  public function __set ($strFieldName, $strValue) {
   if (isset($this->arrNewData[$strFieldName])) {
    $this->arrNewData[$strFieldName] = $strValue;
   } else {
    throw new FieldNotFoundException;
   }//if
  }//function

  public function save () {

  }//function

  public function loadFromPOST () {
   foreach ($this->arrFields as $strFieldName => $strFieldInfo) {
    if (isset($_POST[$strFieldName])) {
     $this->$strFieldName = $_POST[$strFieldName];
    }//if
   }//foreach
  }//function


 }//class

 //Exceptions

 class FieldNotFoundException extends Exception {}
?>