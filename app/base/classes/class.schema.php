<?php
  abstract class Schema extends Database {
    private $strModelName;

    private static $objSchemaConfig;
    private static $arrTableNames;
    private static $arrRelationships;
    private static $arrColumns;

    private $arrEmptyDataArray;

    protected function __construct ($strModelName) {
      $this->strModelName = $strModelName;
      $this->loadAllSchemaInfo();

      parent::__construct();

      $this->loadColumnInfo();
    }//function

    private function loadAllSchemaInfo () {
      if (empty(self::$objSchemaConfig)) {
        self::$objSchemaConfig = simplexml_load_file(Application::getBasePath() . "config/schema.xml");

        $this->loadTableNameInfo();
        $this->loadRelationships();
      }//if
    }//function

    private function loadTableNameInfo () {
      foreach (self::$objSchemaConfig->model as $objModel) {
        self::$arrTableNames[(string) $objModel['name']]['table'] = (string) $objModel['table'];
      }//foreach
    }//function

    private function loadRelationships () {
      foreach (self::$objSchemaConfig->relationships->onetomany as $objOneToManyRelationship) {
        $arrRelationship = array();

        $arrRelationship['type'] = 'onetomany';

        $arrRelationship['local'] = array('model' => (string) $objOneToManyRelationship->localmodel['name'],
                                          'column' => (string) $objOneToManyRelationship->localmodel['column']);

        $arrRelationship['foreign'] = array('model' => (string) $objOneToManyRelationship->foreignmodel['name'],
                                            'column' => (string) $objOneToManyRelationship->foreignmodel['column']);

        self::$arrRelationships[] = $arrRelationship;
      }//foreach

      foreach (self::$objSchemaConfig->relationships->manytomany as $objManyToManyRelationship) {
        $arrRelationship = array();

        $arrRelationship['type'] = 'manytomany';

        $arrRelationship['jointable'] = (string) $objManyToManyRelationship['jointable'];

        $arrModels = array();

        foreach ($objManyToManyRelationship->model as $objRelationshipModel) {
          $arrModel = array();

          $arrModel['name'] = (string) $objRelationshipModel['name'];
          $arrModel['column'] = (string) $objRelationshipModel['column'];

          $arrModels[] = $arrModel;
        }//foreach

        $arrRelationship['models'] = $arrModels;

        self::$arrRelationships[] = $arrRelationship;
      }//foreach
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
          $arrColumn['nullable'] = (strtoupper($arrResult['Null']) == 'YES');
          $arrColumn['PK'] = (strtoupper($arrResult['Key']) == 'PRI');
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

      return $strTableName . 's';
    }//function

    protected function getTableName () {
      if (isset(self::$arrTableNames[$this->strModelName])) {
        return $this->strTablePrefix . self::$arrTableNames[$this->strModelName]['table'];
      } else {
        return $this->strTablePrefix . $this->convertModelNameToTableName($this->strModelName);
      }//if
    }//function

    protected function getEmptyDataArray () {
      return $this->arrEmptyDataArray;
    }//function

    protected function getDataType ($strFieldName) {
      return self::$arrColumns[$strFieldName]['type'];
    }//function

    protected function getPrimaryKeys () {
      $arrColumns = self::$arrColumns;
      $arrPKs = array();

      foreach ($arrColumns as $strFieldName => $arrColumnInfo) {
        if (isset($arrColumnInfo['PK']) && $arrColumnInfo['PK']) {
          $arrPKs[] = $strFieldName;
        }//if
      }//foreach

      return $arrPKs;
    }//function

    protected function isColumn ($strColumn) {
      return isset(self::$arrColumns[$strColumn]);
    }//function

    protected function getColumnInfo ($strFieldName) {
      return self::$arrColumns[$strFieldName];
    }//function

    protected function getColumnList () {
      $arrColumnList = array();
      $arrColumns = self::$arrColumns;

      foreach ($arrColumns as $strFieldName => $arrColumnInfo) {
        $arrColumnList[] = $strFieldName;
      }//foreach

      return $arrColumnList;
    }//function

    protected function getRelationshipInfo () {
      return self::$arrRelationships;
    }//function

    protected function prepareData ($strData, $strFieldName) {
      $strDataType = $this->getDataType($strFieldName);

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