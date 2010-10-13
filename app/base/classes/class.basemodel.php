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

    private   $arrCurrentData = array();
    private   $arrNewData = array();

    protected $blnSaved = false;

    protected $strSchema;
    protected $objSchema;

    public function __construct ($mixPK = null) {
      $objEnvConfig = Config::get('environment');

      try {
        $objDBConfig = Config::get($objEnvConfig->dbconfig);
      } catch (ConfigSettingNotFoundException $exException) {
        $objDBConfig = Config::get('db');
      }//try

      $strClassName = $objDBConfig->driver . "Driver";
      $this->dbConn = new $strClassName($objDBConfig->host, $objDBConfig->user, $objDBConfig->pass, $objDBConfig->name);

      $this->strTablePrefix = $objDBConfig->prefix;

      if (empty($this->strSchema)) {
        $this->strSchema = strtolower(get_class($this));
      }//if

      $this->objSchema = new Schema($this->strSchema);
      $this->arrCurrentData = $this->objSchema->getEmptyDataArray();
      $this->arrNewData = $this->objSchema->getEmptyDataArray();

      if (!is_null($mixPK)) {
        $this->loadFromPK($mixPK);
      }//if
    }//function

    public function __get ($strFieldName) {
      if ($this->objSchema->isColumn($strFieldName)) {
        return $this->arrNewData[$strFieldName];
      } else {
        throw new FieldNotFoundException;
      }//if
    }//function

    public function __set ($strFieldName, $strValue) {
      if ($this->objSchema->isColumn($strFieldName)) {
        $this->arrNewData[$strFieldName] = $strValue;
      } else {
        throw new FieldNotFoundException;
      }//if
    }//function

    public function __call ($strMethodName, $arrArguments) {
      if (preg_match("/^get([A-Za-z_-]+)s\$/", $strMethodName, $arrMatches)) {
        $strChildModel = $arrMatches[1];
        $arrRelationshipInfo = $this->objSchema->getRelationshipInfo($strChildModel);

        if ($arrRelationshipInfo && ($arrRelationshipInfo['type'] == 'onetomany' || $arrRelationshipInfo['type'] == 'manytomany')) {
          $objCollection = new Collection($strChildModel);

          foreach ($arrRelationshipInfo['columns'] as $strLocalColumn => $strForeignColumn) {
            $objCollection->addCondition($strForeignColumn, $this->$strLocalColumn);
          }//foreach

          if (isset($arrArguments[0]['orderby'])) {
            $arrOrderBy = $arrArguments[0]['orderby'];

            foreach ($arrOrderBy as $strFieldName => $conOrderDirection) {
              $objCollection->addOrderBy($strFieldName, $conOrderDirection);
            }//foreach
          }//if

          if (isset($arrArguments[0]['limit'])) {
            $objCollection->setLimit($arrArguments[0]['limit']);
          }//if

          $objCollection->fetch();
          return $objCollection;
        }//if
      } else if (preg_match("/^get([A-Za-z_-]+)\$/", $strMethodName, $arrMatches)) {
        $strParentModel = $arrMatches[1];
        $arrRelationshipInfo = $this->objSchema->getRelationshipInfo($strParentModel);

        if ($arrRelationshipInfo && $arrRelationshipInfo['type'] == 'manytoone') {
          $arrWhere = array();

          foreach ($arrRelationshipInfo['columns'] as $strLocalColumn => $strForeignColumn) {
            $arrWhere[$strForeignColumn] = $this->$strLocalColumn;
          }//foreach

          $objParentModel = new $strParentModel($arrWhere);

          return $objParentModel;
        }//if
      }//if

      return null;
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
        if ($this->objSchema->isColumn($strColumn)) {
          $this->arrNewData[$strColumn] = $mixData;
        } else {
          throw new FieldNotFoundException;
        }//if
      }//foreach
    }//function

    public function loadFromDBArray ($arrData) {
      $this->loadFromArray($arrData);
      $this->arrCurrentData = $this->arrNewData;
      $this->blnSaved = true;
    }//function

    private function loadFromPK ($mixPK) {
      $strSQL = "SELECT * FROM `" . $this->strTablePrefix . $this->objSchema->getTableName() . "` WHERE ";

      $arrPKs = $this->objSchema->getPrimaryKeys();

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
        $this->arrCurrentData = $this->arrNewData;
      } else {
        throw new NoDataFoundException;
      }//if

      $this->blnSaved = true;
    }//function

    private function insertIntoDB () {
      $strSQL = "INSERT INTO `" . $this->strTablePrefix . $this->objSchema->getTableName() . "` ";

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
        $arrColumnInfo = $this->objSchema->getColumnInfo($strFieldName);

        if (!is_null($strData)) {
          $strDataToInsert = $this->prepareData($strData, $strFieldName);
        } else if (isset($arrColumnInfo['default'])) {
          $strDataToInsert = $this->prepareData($arrColumnInfo['default'], $strFieldName);
        } else if (isset($arrColumnInfo['function'])) {
          $strDataToInsert = $arrColumnInfo['function'];
        } else if ($arrColumnInfo['nullable'] == true) {
          $strDataToInsert = 'NULL';
        } else if ($arrColumnInfo['autonumber'] != true) {
          throw new DataMissingException;
        }//if

        if (!isset($arrColumnInfo['autonumber']) || !$arrColumnInfo['autonumber']) {
          $arrColumns[] = $strFieldName;
          $arrData[] = $strDataToInsert;
        }//if
      }//foreach

      $strSQL .= "(`" . implode("`, `", $arrColumns) . "`) VALUES (" . implode(", ", $arrData) . ");";

      $this->dbConn->query($strSQL);

      $arrColumns = $this->objSchema->getColumnList();
      $arrPKData = array();

      foreach ($arrColumns as $strFieldName) {
        $arrColumnInfo = $this->objSchema->getColumnInfo($strFieldName);

        if ((isset($arrColumnInfo['PK']) && $arrColumnInfo['PK']) && (isset($arrColumnInfo['autonumber']) && $arrColumnInfo['autonumber'])) {
          $arrPKData[$strFieldName] = $this->dbConn->insert_id;
        } else if (isset($arrColumnInfo['PK']) && $arrColumnInfo['PK']) {
          $arrPKData[$strFieldName] = $arrNewData[$strFieldName];
        }//if
      }//foreach

      $this->loadFromPK($arrPKData);
      $this->blnSaved = true;
    }//function

    private function updateDB () {
      $strSQL = "UPDATE `" . $this->strTablePrefix . $this->objSchema->getTableName() . "` SET";

      $arrNewData = $this->arrNewData;
      $arrCurrentData = $this->arrCurrentData;

      foreach ($arrNewData as $strFieldName => $strData) {
        if ($strData !== $arrCurrentData[$strFieldName]) {
          $strSQL .= " `" . $strFieldName . "` = " . $this->prepareData($strData, $strFieldName) . ",";
        }//if
      }//foreach

      $arrPKs = $this->objSchema->getPrimaryKeys();

      $arrWhereStrings = array();

      foreach ($arrPKs as $strFieldName) {
        $arrWhereStrings[] = "`" . $strFieldName . "` = ". $this->prepareData($arrCurrentData[$strFieldName], $strFieldName);
      }//foreach

      $strSQL = rtrim($strSQL, ",");

      $strSQL .= " WHERE " . implode(" AND ", $arrWhereStrings) . ";";

      $this->dbConn->query($strSQL);

      $arrColumns = $this->objSchema->getColumnList();
      $arrPKData = array();

      foreach ($arrColumns as $strFieldName) {
        $arrColumnInfo = $this->objSchema->getColumnInfo($strFieldName);

        if ($arrColumnInfo['PK']) {
          $arrPKData[$strFieldName] = $arrNewData[$strFieldName];
        }//if
      }//foreach

      $this->loadFromPK($arrPKData);
    }//function

    public function delete () {
      if ($this->blnSaved) {
        $arrCurrentData = $this->arrCurrentData;
        $arrPKData = $this->objSchema->getPrimaryKeys();

        $strSQL = "DELETE FROM `" . $this->strTablePrefix . $this->objSchema->getTableName() . "` WHERE ";

        foreach ($arrPKData as $strFieldName) {
          $arrWhereStrings[] = "`" . $strFieldName . "` = ". $this->prepareData($arrCurrentData[$strFieldName], $strFieldName);
        }//foreach

        $strSQL .= implode(" AND ", $arrWhereStrings) . ";";

        $this->dbConn->query($strSQL);
      } else {
        throw new NoDataFoundException;
      }//if
    }//function

    public function isSavedData () {
      return $this->blnSaved;
    }//function

    private function prepareData ($strData, $strFieldName) {
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

  //Exceptions

  class DataMissingException extends Exception {}
  class FieldNotFoundException extends Exception {}
  class InvalidDataException extends Exception {}
  class InvalidSchemaException extends Exception {}
  class NoDataFoundException extends Exception {}