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
    private   $arrCurrentData = array();
    private   $arrNewData = array();

    private   $blnSaved = false;

    protected $strSchema;

    public function __construct () {
      $objDBConfig = Config::get('db');
      $strClassName = $objDBConfig->driver . "Driver";
      $this->dbConn = new $strClassName($objDBConfig->host, $objDBConfig->user, $objDBConfig->pass, $objDBConfig->name);

      if (empty($this->strSchema)) {
        $this->strSchema = strtolower(get_class($this));
      }//if

      $this->loadSchema();
    }//function

    private function loadSchema () {
      $strSchemaFilename = Application::getBasePath() . "schemas/" . strtolower($this->strSchema) . ".xml";
      $objSchemaFile = simplexml_load_file($strSchemaFilename);

      //Table Name
      $this->strTableName = (string) $arrSchemaFile['name'];

      //Fields
      foreach ($objSchemaFile->columns->column as $objColumn) {
        $arrColumn = array();
        $arrColumn['type'] = (string) $objColumn->type;

        if (isset($objColumn->size)) {
          $arrColumn['size'] = (int) $objColumn->length;
        }//if

        if (isset($objColumn->default)) {
          $arrColumn['default'] = (string) $objColumn->default;
        }//if

        if (isset($objColumn->null)) {
          $arrColumn['null'] = (strtolower((string) $objColumn->null) == "yes");
        }//if

        if (isset($objColumn->primary_key)) {
          $arrColumn['PK'] = (strtolower((string) $objColumn->primary_key) == "yes");
        }//if

        if (isset($objColumn->autonumber)) {
          $arrColumn['Autonumber'] = (strtolower((string) $objColumn->autonumber) == "yes");
        }//if

        $this->arrColumns[(string) $objColumn['title']] = $arrColumn;
        $this->arrCurrentData[(string) $objColumn['name']] = null;
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
      if ($this->blnSaved) {
        $this->updateDB();
      } else {
        $this->insertIntoDB();
      }//if
    }//function

    public function loadFromPOST () {
      foreach ($this->arrFields as $strFieldName => $strFieldInfo) {
        if (isset($_POST[$strFieldName])) {
          $this->$strFieldName = $_POST[$strFieldName];
        }//if
      }//foreach
    }//function

    private function insertIntoDB () {
      $strSQL = "INSERT INTO `" . $this->strTableName . "`";

      $arrColumns = array();
      $arrValues = array();

      $arrNewData = $this->arrNewData;

      foreach ($arrNewData as $arrData) {

      }//foreach

      return $strSQL;
    }//function

    private function updateDB () {

    }//function
  }//class

  //Exceptions

  class FieldNotFoundException extends Exception {}
?>