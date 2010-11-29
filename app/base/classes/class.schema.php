<?php
  class Schema extends Database {
    private $strModelName;

    private static $arrSchemaInfo;
    private static $arrRelationships;
    private static $arrColumns;

    private $arrEmptyDataArray;

    public function __construct ($strModelName) {
      $this->strModelName = $strModelName;
      $this->loadAllSchemaInfo();

      parent::__construct();

      $this->loadColumnInfo();
    }//function

    private function loadAllSchemaInfo () {
      if (empty(self::$arrSchemaInfo)) {
        $objSchemaConfig = simplexml_load_file(Application::getBasePath() . "config/schema.xml");

        foreach ($objSchemaConfig->model as $objModel) {
          self::$arrSchemaInfo[(string) $objModel['name']]['table'] = (string) $objModel['table'];
        }//foreach

        foreach ($objSchemaConfig->relationships->relationship as $objRelationshipInfo) {
          $arrRelationship = array();

          $arrRelationship['model'] = (string) $objRelationshipInfo['foreign'];
          $arrRelationship['type'] = (string) $objRelationshipInfo['type'];

          $arrColumns = array();

          foreach ($objRelationshipInfo->column as $objColumn) {
            $arrColumns[(string) $objColumn['local']] = (string) $objColumn['foreign'];
          }//foreach

          $arrRelationship['columns'] = $arrColumns;

          self::$arrRelationships[(string) $objRelationshipInfo['local']] = $arrRelationship;

          //Inverse Relationship
          $arrInverseRelationship = array();

          $arrInverseRelationship['model'] = (string) $objRelationshipInfo['local'];

          if ((string) $objRelationshipInfo['type'] == 'onetomany') {
            $arrInverseRelationship['type'] = 'manytoone';
          } else {
            $arrInverseRelationship['type'] = 'manytomany';
          }//if

          $arrColumns = array();

          foreach ($objRelationshipInfo->column as $objColumn) {
            $arrColumns[(string) $objColumn['foreign']] = (string) $objColumn['local'];
          }//foreach

          $arrInverseRelationship['columns'] = $arrColumns;

          self::$arrRelationships[(string) $objRelationshipInfo['foreign']] = $arrRelationship;
        }//foreach
      }//if
    }//function

    private function loadColumnInfo () {
      $strTableName = $this->getTableName();

      if (empty(self::$arrColumns)) {
        $strQuery = "SHOW COLUMNS IN `" . $this->dbConn->escape_string($strTableName) . "`;";
        $dbResults = $this->dbConn->query($strQuery);

        while ($arrResult = $dbResults->fetch_assoc()) {
          $arrColumn = array();

          $strColumnName = $arrResult['Field'];

          if (preg_match('/^([^\s(]+)(?:\(([^)]+)\))?$/', $arrResult['Type'], $arrMatches)) {
            $arrColumn['type'] = $arrMatches[1];

            if (isset($arrMatches[2])) {
              $arrColumn['size'] = $arrMatches[2];
            }//if
          }//if

          $arrColumn['default'] = $arrResult['Default'];
          $arrColumn['nullable'] = (strtolower($arrResult['Null']) == 'YES');
          $arrColumn['PK'] = (strtolower($arrResult['Key']) == 'PRI');
          $arrColumn['autonumber'] = (strtolower($arrResult['Extra']) == 'auto_increment');

          self::$arrColumns[$strColumnName] = $arrColumn;
          $this->arrEmptyDataArray[$strColumnName] = null;
        }//while
      }//if
    }//function

    private function convertModelNameToTableName ($strModelName) {
      $strTableName = "";

      for ($i = 0; $i < strlen($strModelName); $i++) {
        $strCharacter = $strModelName{$i};

        if ($i > 0 && ord($strCharacter) >= 65 && ord($strCharacter) <= 90) {
          $strTableName .= "_" . strtolower($strCharacter);
        } else {
          $strTableName .= strtolower($strCharacter);
        }//if
      }//for

      return $strTableName;
    }//function

    public function getTableName () {
      if (isset(self::$arrSchemaInfo[$this->strModelName])) {
        return $this->strTablePrefix . self::$arrSchemaInfo[$this->strModelName]['table'];
      } else {
        return $this->strTablePrefix . $this->convertModelNameToTableName($this->strModelName);
      }//if
    }//function

    public function getEmptyDataArray () {
      return $this->arrEmptyDataArray;
    }//function

    public function getDataType ($strFieldName) {
      return self::$arrColumns[$strFieldName]['type'];
    }//function

    public function getPrimaryKeys () {
      $arrColumns = self::$arrColumns;
      $arrPKs = array();

      foreach ($arrColumns as $strFieldName => $arrColumnInfo) {
        if (isset($arrColumnInfo['PK']) && $arrColumnInfo['PK']) {
          $arrPKs[] = $strFieldName;
        }//if
      }//foreach

      return $arrPKs;
    }//function

    public function isColumn ($strColumn) {
      return isset(self::$arrColumns[$strColumn]);
    }//function

    public function getColumnInfo ($strFieldName) {
      return self::$arrColumns[$strFieldName];
    }//function

    public function getColumnList () {
      $arrColumnList = array();
      $arrColumns = self::$arrColumns;

      foreach ($arrColumns as $strFieldName => $arrColumnInfo) {
        $arrColumnList[] = $strFieldName;
      }//foreach

      return $arrColumnList;
    }//function

    public function getRelationshipInfo ($strModelName) {
      if (isset(self::$arrRelationships[$strModelName])) {
        return self::$arrRelationships[$strModelName];
      } else {
        return false;
      }//if
    }//function
  }//class