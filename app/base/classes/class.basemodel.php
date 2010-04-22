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
    private   $strTablePrefix;

    private   $strTableName;
    private   $arrColumns;

    private   $arrCurrentData = array();
    private   $arrNewData = array();

    private   $arrRelationships = array();

    private   $blnSaved = false;

    protected $strSchema;

    public function __construct ($mixPK = null) {
      $objDBConfig = Config::get('db');
      $strClassName = $objDBConfig->driver . "Driver";
      $this->dbConn = new $strClassName($objDBConfig->host, $objDBConfig->user, $objDBConfig->pass, $objDBConfig->name);

      $this->strTablePrefix = $objDBConfig->prefix;

      if (empty($this->strSchema)) {
        $this->strSchema = strtolower(get_class($this));
      }//if

      $this->loadSchema();

      if (!is_null($mixPK)) {
        $this->loadFromPK($mixPK);
      }//if
    }//function

    private function loadSchema () {
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
        $this->arrCurrentData[(string) $objColumn['name']] = null;
      }//foreach

      foreach ($objSchemaFile->relationships->relationship as $objRelationship) {
        $arrRelationship = array();

        $arrRelationship['type'] = (string) $objRelationship['type'];

        $arrColumns = array();

        foreach ($objRelationship->column as $objColumn) {
          $arrColumns[(string) $objColumn['local']] = (string) $objColumn['foreign'];
        }//foreach

        $arrRelationship['columns'] = $arrColumns;

        $this->arrRelationships[(string) $objRelationship['with']] = $arrRelationship;
      }//foreach

      $this->arrNewData = $this->arrCurrentData;
    }//function

    public function __get ($strFieldName) {
      if (isset($this->arrColumns[$strFieldName])) {
        return $this->arrNewData[$strFieldName];
      } else {
        throw new FieldNotFoundException;
      }//if
    }//function

    public function __set ($strFieldName, $strValue) {
      if (isset($this->arrColumns[$strFieldName])) {
        $this->arrNewData[$strFieldName] = $strValue;
      } else {
        throw new FieldNotFoundException;
      }//if
    }//function

    public function __call ($strMethodName, $arrArguments) {
      if (preg_match("/^get([A-Za-z_-]+)s\$/", $strMethodName, $arrMatches)) {
        $strChildModel = $arrMatches[1];

        $objChildComments = new Collection($strChildModel);
      }//if

    }//function

    public function save () {
      if ($this->blnSaved) {
        $this->updateDB();
      } else {
        $this->insertIntoDB();
      }//if
    }//function

    public function loadFromArray ($arrData) {
      foreach ($arrData as $strColumn => $mixData) {
        if (isset($this->arrColumns[$strColumn])) {
          $this->arrCurrentData[$strColumn] = $mixData;
        } else {
          throw new FieldNotFoundException;
        }//if
      }//foreach

      $this->arrNewData = $this->arrCurrentData;
    }//function

    private function loadFromPK ($mixPK) {
      $strSQL = "SELECT * FROM `" . $this->strTablePrefix . $this->strTableName . "` WHERE ";

      $arrColumns = $this->arrColumns;
      $arrPKs = array();

      foreach ($arrColumns as $strFieldName => $arrColumnInfo) {
        if ($arrColumnInfo['PK']) {
          $arrPKs[] = $strFieldName;
        }//if
      }//foreach

      $arrWhereData = array();

      if (count($arrPKs) == 1 && !is_array($mixPK)) {
        $strPK = reset($arrPKs);
        $arrWhereData[$strPK] = $mixPK;
      } else {
        if (count($arrPKs) != count($mixPK)) {
          throw new InvalidDataException;
        }//if

        foreach ($arrPKs as $strFieldName) {
          if (isset($mixPK[$strFieldName])) {
            $arrWhereData[$strFieldName] = $mixPK[$strFieldName];
          } else {
            throw new InvalidDataException;
          }//if
        }//foreach
      }//if

      foreach ($arrWhereData as $strFieldName => $mixData) {
        $arrWhereStrings[] = "`" . $strFieldName . "` = ". $this->prepareData($mixData, $strFieldName);
      }//foreach

      $strSQL .= implode(" AND ", $arrWhereStrings) . ";";

      $dbResults = $this->dbConn->query($strSQL);

      if ($dbResults->num_rows != 0) {
        $arrResult = $dbResults->fetch_assoc();
        $this->loadFromArray($arrResult);
      } else {
        throw new NoDataFoundException;
      }//if

      $this->blnSaved = true;
    }//function

    private function insertIntoDB () {
      $strSQL = "INSERT INTO `" . $this->strTablePrefix . $this->strTableName . "` ";

      $arrNewData = $this->arrNewData;

      $arrColumns = array();
      $arrData = array();

      //Create list of data to insert. If values are empty, insert:
      // - The default value if present
      // - null if field is nullable
      // - throw DataMissing Exception
      //...in that order. Also add quotes and escape data as necessary.

      //Don't include any autonumbered fields!
      foreach ($arrNewData as $strFieldName => $strData) {
        if (!empty($strData)) {
          $strDataToInsert = $this->prepareData($strData, $strFieldName);
        } else if (isset($this->arrColumns[$strFieldName]['default'])) {
          $strDataToInsert = $this->prepareData($this->arrColumns[$strFieldName]['default'], $strFieldName);
        } else if (isset($this->arrColumns[$strFieldName]['function'])) {
          $strDataToInsert = $this->arrColumns[$strFieldName]['function'];
        } else if ($this->arrColumns[$strFieldName]['nullable'] == true) {
          $strDataToInsert = 'NULL';
        } else if ($this->arrColumns[$strFieldName]['autonumber'] != true) {
          throw new DataMissingException;
        }//if

        if ($this->arrColumns[$strFieldName]['autonumber'] != true) {
          $arrColumns[] = $strFieldName;
          $arrData[] = $strDataToInsert;
        }//if
      }//foreach

      $strSQL .= "(`" . implode("`, `", $arrColumns) . "`) VALUES (" . implode(", ", $arrData) . ");";

      $this->dbConn->query($strSQL);

      $arrColumns = $this->arrColumns;
      $arrPKData = array();

      foreach ($arrColumns as $strFieldName => $arrColumnInfo) {
        if ($arrColumnInfo['PK'] && $arrColumnInfo['autonumber']) {
          $arrPKData[$strFieldName] = $this->dbConn->insert_id;
        } else if ($arrColumnInfo['PK']) {
          $arrPKData[$strFieldName] = $arrNewData[$strFieldName];
        }//if
      }//foreach

      $this->loadFromPK($arrPKData);
      $this->blnSaved = true;
    }//function

    private function updateDB () {
      $strSQL = "UPDATE `" . $this->strTablePrefix . $this->strTableName . "` SET";

      $arrNewData = $this->arrNewData;
      $arrCurrentData = $this->arrCurrentData;

      foreach ($arrNewData as $strFieldName => $strData) {
        if ($strData !== $arrCurrentData[$strFieldName]) {
          $strSQL .= " `" . $strFieldName . "` = " . $this->prepareData($strData, $strFieldName) . ",";
        }//if
      }//foreach

      $arrColumns = $this->arrColumns;
      $arrPKs = array();

      foreach ($arrColumns as $strFieldName => $arrColumnInfo) {
        if ($arrColumnInfo['PK']) {
          $arrPKs[] = $strFieldName;
        }//if
      }//foreach

      $arrWhereStrings = array();

      foreach ($arrPKs as $strFieldName) {
        $arrWhereStrings[] = "`" . $strFieldName . "` = ". $this->prepareData($arrCurrentData[$strFieldName], $strFieldName);
      }//foreach

      $strSQL = rtrim($strSQL, ",");

      $strSQL .= " WHERE " . implode(" AND ", $arrWhereStrings) . ";";

      $this->dbConn->query($strSQL);

      $arrColumns = $this->arrColumns;
      $arrPKData = array();

      foreach ($arrColumns as $strFieldName => $arrColumnInfo) {
        if ($arrColumnInfo['PK']) {
          $arrPKData[$strFieldName] = $arrNewData[$strFieldName];
        }//if
      }//foreach

      $this->loadFromPK($arrPKData);
    }//function

    public function delete () {
      if ($this->blnSaved) {
        $arrColumns = $this->arrColumns;
        $arrCurrentData = $this->arrCurrentData;
        $arrPKData = array();

        foreach ($arrColumns as $strFieldName => $arrColumnInfo) {
          if ($arrColumnInfo['PK']) {
            $arrPKData[] = $strFieldName;
          }//if
        }//foreach

        $strSQL = "DELETE FROM `" . $this->strTablePrefix . $this->strTableName . "` WHERE ";

        foreach ($arrPKData as $strFieldName) {
          $arrWhereStrings[] = "`" . $strFieldName . "` = ". $this->prepareData($arrCurrentData[$strFieldName], $strFieldName);
        }//foreach

        $strSQL .= implode(" AND ", $arrWhereStrings) . ";";

        $this->dbConn->query($strSQL);
      } else {
        throw new NoDataFoundException;
      }//if

    }//function

    public function getTableName () {
      return $this->strTableName;
    }//function

    private function prepareData ($strData, $strFieldName) {
      $strDataType = $this->arrColumns[$strFieldName]['type'];

      switch ($strDataType) {
        case 'int':
        case 'tinyint':
          return $strData;
          break;

        case 'date':
        case 'datetime':
        case 'text':
        case 'varchar':
          return "'" . $this->dbConn->escape_string($strData) . "'";
          break;
      }//switch
    }//function
  }//class

  //Exceptions

  class DataMissingException extends Exception {}
  class FieldNotFoundException extends Exception {}
  class InvalidDataException extends Exception {}
  class InvalidSchemaException extends Exception {}
  class NoDataFoundException extends Exception {}
?>