<?php
  abstract class BaseModel extends Schema {
    private   $arrCurrentData = array();
    private   $arrNewData = array();

    private $blnSaved = false;

    public function __construct ($mixPK = null) {
      $this->strModelName = get_class($this);
      parent::__construct();

      $this->arrCurrentData = $this->arrEmptyDataArray;
      $this->arrNewData = $this->arrEmptyDataArray;

      if (!is_null($mixPK)) {
        $this->loadFromPK($mixPK);
      }//if
    }//function

    public function __get ($strFieldName) {
      if ($this->isColumn($strFieldName)) {
        return $this->arrNewData[$strFieldName];
      } else {
        throw new FieldNotFoundException;
      }//if
    }//function

    public function __set ($strFieldName, $strValue) {
      if ($this->isColumn($strFieldName)) {
        $this->arrNewData[$strFieldName] = $strValue;
      } else {
        throw new FieldNotFoundException;
      }//if
    }//function

    public function __call ($strMethodName, $arrArguments) {
      if (preg_match("/^get([A-Za-z0-9-]+)\$/", $strMethodName, $arrMatches)) {
        $strGetCallName = $arrMatches[1];
        $blnRelationshipFound = false;

        if ($strRelatedModel = $this->objModelRegistry->getModelNameFromPlural($strGetCallName)) {
          $objCollection = new Collection($strRelatedModel);

          if (isset($arrArguments[0]) && ($arrArguments[0] instanceof ResultsFilter)) {
            $objResultsFilter = $arrArguments[0];
          }//if

          foreach (self::$arrRelationships as $arrRelationshipInfo) {
            switch ($arrRelationshipInfo['type']) {
              case 'manytomany':
                $strJoinTable = $arrRelationshipInfo['jointable'];

                $strLocalModel = "";
                $strForeignModel = "";

                foreach ($arrRelationshipInfo['models'] as $arrModelInfo) {
                  if ($arrModelInfo['name'] == $this->strModelName) {
                    $strLocalModel = $arrModelInfo['name'];
                    $strLocalColumn = $arrModelInfo['column'];
                  } else if ($arrModelInfo['name'] == $strRelatedModel) {
                    $strForeignModel = $arrModelInfo['name'];
                    $strForeignColumn = $arrModelInfo['column'];
                  }//if
                }//foreach

                if (($strLocalModel == $this->strModelName) && ($strForeignModel == $strRelatedModel)) {
                  $blnRelationshipFound = true;

                  $strSQL = "SELECT
                               f.*
                             FROM
                               `" . $this->addTablePrefix($this->objModelRegistry->getTableName($strForeignModel)) . "` f
                                 INNER JOIN `" . $this->addTablePrefix($strJoinTable) . "` j
                                   ON f.`" . $strForeignColumn . "` = j.`" . $strForeignColumn . "`
                                 INNER JOIN `" . $this->addTablePrefix($this->objModelRegistry->getTableName($strLocalModel)) . "` l
                                   ON l.`" . $strLocalColumn . "` = j.`" . $strLocalColumn . "`
                             WHERE
                               l.`" . $strLocalColumn . "` = " . $this->prepareData($this->$strLocalColumn, $strLocalColumn);

                  if (isset($objResultsFilter)) {
                    $strSQL .= $objResultsFilter->getOrderByString();
                    $strSQL .= $objResultsFilter->getLimitString();
                  }//if

                  $strSQL .= ";";

                  $dbResults = $this->dbConn->query($strSQL);

                  while ($arrResult = $dbResults->fetch_assoc()) {
                    $objModel = new $strRelatedModel;
                    $objModel->loadFromDBArray($arrResult);

                    $objCollection[] = $objModel;
                  }//while

                  return $objCollection;
                }//if
                break;

              case 'onetomany':
                if ($arrRelationshipInfo['local']['model'] == $this->strModelName && $arrRelationshipInfo['foreign']['model'] == $strRelatedModel) {
                  $blnRelationshipFound = true;

                  $strLocalColumn = $arrRelationshipInfo['local']['column'];

                  $strSQL = "SELECT
                               *
                             FROM
                               `" . $this->addTablePrefix($this->objModelRegistry->getTableName($arrRelationshipInfo['foreign']['model'])) . "`
                             WHERE
                               `" . $arrRelationshipInfo['foreign']['column'] . "` = " . $this->prepareData($this->$strLocalColumn, $strLocalColumn);

                  if (isset($objResultsFilter)) {
                    $strSQL .= $objResultsFilter->getOrderByString();
                    $strSQL .= $objResultsFilter->getLimitString();
                  }//if

                  $strSQL .= ";";

                  $dbResults = $this->dbConn->query($strSQL);

                  while ($arrResult = $dbResults->fetch_assoc()) {
                    $objModel = new $strRelatedModel;
                    $objModel->loadFromDBArray($arrResult);

                    $objCollection[] = $objModel;
                  }//while

                  return $objCollection;
                }//if
                break;
            }//switch
          }//foreach
        }//if

        if (!$blnRelationshipFound && $this->objModelRegistry->isModel($strGetCallName)) {
          $strRelatedModel = $strGetCallName;
          //Singular, so should only be concerned with onetomany relationships where this model is the child / foreign table, and onetoone relationships

          foreach (self::$arrRelationships as $arrRelationshipInfo) {
            switch ($arrRelationshipInfo['type']) {
              case 'onetomany':
                if ($arrRelationshipInfo['foreign']['model'] == $this->strModelName && $arrRelationshipInfo['local']['model'] == $strRelatedModel) {
                  $strForeignColumn = $arrRelationshipInfo['foreign']['column'];
                  $strLocalColumn = $arrRelationshipInfo['local']['column'];

                  return new $strRelatedModel(array($strLocalColumn => $this->$strForeignColumn));
                }//if
                break;

              case 'onetoone':
                $blnRelationshipFound = false;

                if ($arrRelationshipInfo['local']['name'] == $this->strModelName && $arrRelationshipInfo['foreign']['name'] == $strRelatedModel) {
                  $strColumn = $arrRelationshipInfo['local']['column'];
                  $strOtherColumn = $arrRelationshipInfo['foreign']['column'];

                  $objResultsFilter = new ResultsFilter();
                  $objResultsFilter->model($strRelatedModel)
                                   ->conditions($strOtherColumn . " = " . $this->prepareData($this->$strColumn, $strColumn));

                  $objCollection = new Collection($objResultsFilter);

                  if (count($objCollection) == 1) {
                    return $objCollection[0];
                  } else {
                    throw new NoDataFoundException();
                  }//if
                } else if ($arrRelationshipInfo['foreign']['name'] == $this->strModelName && $arrRelationshipInfo['local']['name'] == $strRelatedModel) {
                  $strColumn = $arrRelationshipInfo['foreign']['column'];
                  $strOtherColumn = $arrRelationshipInfo['local']['column'];

                  return new $strRelatedModel(array($strOtherColumn => $this->$strColumn));
                }//if
                break;
            }//switch
          }//foreach
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
        if ($this->isColumn($strColumn)) {
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
      $strSQL = "SELECT * FROM `" . $this->getTableName() . "` WHERE ";

      $arrPKs = $this->getPrimaryKeys();

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
      $strSQL = "INSERT INTO `" . $this->getTableName() . "` ";

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
        $arrColumnInfo = $this->getColumnInfo($strFieldName);

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

      $arrColumns = $this->getColumnList();
      $arrPKData = array();

      foreach ($arrColumns as $strFieldName) {
        $arrColumnInfo = $this->getColumnInfo($strFieldName);

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
      $strSQL = "UPDATE `" . $this->getTableName() . "` SET";

      $arrNewData = $this->arrNewData;
      $arrCurrentData = $this->arrCurrentData;

      foreach ($arrNewData as $strFieldName => $strData) {
        if ($strData !== $arrCurrentData[$strFieldName]) {
          $strSQL .= " `" . $strFieldName . "` = " . $this->prepareData($strData, $strFieldName) . ",";
        }//if
      }//foreach

      $arrPKs = $this->getPrimaryKeys();

      $arrWhereStrings = array();

      foreach ($arrPKs as $strFieldName) {
        $arrWhereStrings[] = "`" . $strFieldName . "` = ". $this->prepareData($arrCurrentData[$strFieldName], $strFieldName);
      }//foreach

      $strSQL = rtrim($strSQL, ",");

      $strSQL .= " WHERE " . implode(" AND ", $arrWhereStrings) . ";";

      $this->dbConn->query($strSQL);

      $arrColumns = $this->getColumnList();
      $arrPKData = array();

      foreach ($arrColumns as $strFieldName) {
        $arrColumnInfo = $this->getColumnInfo($strFieldName);

        if ($arrColumnInfo['PK']) {
          $arrPKData[$strFieldName] = $arrNewData[$strFieldName];
        }//if
      }//foreach

      $this->loadFromPK($arrPKData);
    }//function

    public function delete () {
      if ($this->blnSaved) {
        $arrCurrentData = $this->arrCurrentData;
        $arrPKData = $this->getPrimaryKeys();

        $strSQL = "DELETE FROM `" . $this->getTableName() . "` WHERE ";

        foreach ($arrPKData as $strFieldName) {
          $arrWhereStrings[] = "`" . $strFieldName . "` = ". $this->prepareData($arrCurrentData[$strFieldName], $strFieldName);
        }//foreach

        $strSQL .= implode(" AND ", $arrWhereStrings) . ";";

        $this->dbConn->query($strSQL);
      } else {
        throw new NoDataFoundException;
      }//if
    }//function

    protected function isSavedData () {
      return $this->blnSaved;
    }//function
  }//class

  //For use when creating classes on the fly

  class GenericModelClass extends BaseModel {}

  //Exceptions

  class DataMissingException extends Exception {}
  class FieldNotFoundException extends Exception {}
  class InvalidDataException extends Exception {}
  class InvalidSchemaException extends Exception {}
  class NoDataFoundException extends Exception {}
