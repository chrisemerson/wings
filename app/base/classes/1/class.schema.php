<?php
  class Schema extends Database {
    private $strSchema;

    private $strTableName;
    private $arrColumns;
    private $arrEmptyDataArray;
    private $arrRelationships;

    public function __construct ($strSchema) {
      $this->strSchema = $strSchema;
      $this->load();
    }//function

    private function load () {
      $strQuery = "SELECT * FROM information_schema;";

      $this->dbConn;

      $strSchemaFilename = Application::getBasePath() . "schemas/" . strtolower($this->strSchema) . ".xml";
      $objSchemaFile = simplexml_load_file($strSchemaFilename);

      //Table Name
      $this->strTableName = (string) $objSchemaFile['name'];

      //Fields
      foreach ($objSchemaFile->columns->column as $objColumn) {
        $arrColumn = array();
        $arrColumn['type'] = (string) $objColumn['type'];

        if (isset($objColumn['size'])) {
          $arrColumn['size'] = (int) $objColumn['size'];
        }//if

        if (isset($objColumn['default'])) {
          $arrColumn['default'] = (string) $objColumn['default'];
        }//if

        if (isset($objColumn['function'])) {
          $arrColumn['function'] = (string) $objColumn['function'];
        }//if

        if (isset($objColumn['nullable'])) {
          $arrColumn['nullable'] = (strtolower((string) $objColumn['nullable']) == "yes");
        }//if

        if (isset($objColumn['primary_key'])) {
          $arrColumn['PK'] = (strtolower((string) $objColumn['primary_key']) == "yes");

          if (isset($objColumn['default'])) {
            throw new InvalidSchemaException;
          }//if
        }//if

        if (isset($objColumn['auto_number'])) {
          $arrColumn['autonumber'] = (strtolower((string) $objColumn['auto_number']) == "yes");

          if (!isset($objColumn['primary_key'])) {
            throw new InvalidSchemaException;
          }//if
        }//if

        $this->arrColumns[(string) $objColumn['name']] = $arrColumn;
        $this->arrEmptyDataArray[(string) $objColumn['name']] = null;
      }//foreach

      if (isset($objSchemaFile->relationships->relationship)) {
        foreach ($objSchemaFile->relationships->relationship as $objRelationship) {
          $arrRelationship = array();

          $arrRelationship['type'] = (string) $objRelationship['type'];

          $arrColumns = array();

          foreach ($objRelationship->column as $objColumn) {
            $arrColumns[(string) $objColumn['local']] = (string) $objColumn['foreign'];
          }//foreach

          $arrRelationship['columns'] = $arrColumns;

          $this->arrRelationships[(string) $objRelationship['model']] = $arrRelationship;
        }//foreach
      }//if
    }//function

    public function getTableName () {
      return $this->strTableName;
    }//function

    public function getEmptyDataArray () {
      return $this->arrEmptyDataArray;
    }//function

    public function getDataType ($strFieldName) {
      return $this->arrColumns[$strFieldName]['type'];
    }//function

    public function getPrimaryKeys () {
      $arrColumns = $this->arrColumns;
      $arrPKs = array();

      foreach ($arrColumns as $strFieldName => $arrColumnInfo) {
        if (isset($arrColumnInfo['PK']) && $arrColumnInfo['PK']) {
          $arrPKs[] = $strFieldName;
        }//if
      }//foreach

      return $arrPKs;
    }//function

    public function isColumn ($strColumn) {
      return isset($this->arrColumns[$strColumn]);
    }//function

    public function getColumnInfo ($strFieldName) {
      return $this->arrColumns[$strFieldName];
    }//function

    public function getColumnList () {
      $arrColumnList = array();
      $arrColumns = $this->arrColumns;

      foreach ($arrColumns as $strFieldName => $arrColumnInfo) {
        $arrColumnList[] = $strFieldName;
      }//foreach

      return $arrColumnList;
    }//function

    public function getRelationshipInfo ($strModelName) {
      if (isset($this->arrRelationships[$strModelName])) {
        return $this->arrRelationships[$strModelName];
      } else {
        return false;
      }//if
    }//function
  }//class