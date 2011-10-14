<?php
  abstract class Schema extends Database {
    protected $strModelName;
    protected $objModelRegistry;

    protected static $arrRelationships;
    protected static $arrColumns;

    protected $arrEmptyDataArray;

    protected function __construct () {
      $this->objModelRegistry = new ModelRegistry();

      if (!$this->objModelRegistry->isModel($this->strModelName)) {
        throw new ModelNotFoundException;
      }//if

      $this->loadRelationships();

      parent::__construct();

      //Must come after the parent constructor, as we need a DB Connection for this method to work
      $this->loadColumnInfo();
    }//function

    private function loadRelationships () {
      if (empty(self::$arrRelationships)) {
        $objRelationshipsConfig = simplexml_load_file(Application::getBasePath() . "app/config/relationships.xml");

        foreach ($objRelationshipsConfig->onetomany as $objOneToManyRelationship) {
          $arrRelationship = array();

          $arrRelationship['type'] = 'onetomany';

          $arrRelationship['local'] = array('model' => (string) $objOneToManyRelationship->localmodel['name'],
                                            'column' => (string) $objOneToManyRelationship->localmodel['column']);

          $arrRelationship['foreign'] = array('model' => (string) $objOneToManyRelationship->foreignmodel['name'],
                                              'column' => (string) $objOneToManyRelationship->foreignmodel['column']);

          self::$arrRelationships[] = $arrRelationship;
        }//foreach

        foreach ($objRelationshipsConfig->manytomany as $objManyToManyRelationship) {
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

        foreach ($objRelationshipsConfig->onetoone as $objOneToOneRelationship) {
          $arrRelationship = array();

          $arrRelationship['type'] = 'onetoone';

          $arrModels = array();

          $arrRelationship['local'] = array('name' => (string) $objOneToOneRelationship->localmodel['name'],
                                            'column' => (string) $objOneToOneRelationship->localmodel['column']);

          $arrRelationship['foreign'] = array('name' => (string) $objOneToOneRelationship->foreignmodel['name'],
                                              'column' => (string) $objOneToOneRelationship->foreignmodel['column']);

          self::$arrRelationships[] = $arrRelationship;
        }//foreach
      }//if
    }//function

    private function loadColumnInfo () {
      if (!isset(self::$arrColumns[$this->strModelName]) || empty(self::$arrColumns[$this->strModelName])) {
        $strQuery = "SHOW COLUMNS IN `" . $this->dbConn->escape_string($this->getTableName()) . "`;";
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

          self::$arrColumns[$this->strModelName][$strColumnName] = $arrColumn;
        }//while
      }//if

      foreach (self::$arrColumns[$this->strModelName] as $strColumnName => $arrColumn) {
        $this->arrEmptyDataArray[$strColumnName] = null;
      }//foreach
    }//function

    protected function getTableName () {
      return $this->addTablePrefix($this->objModelRegistry->getTableName($this->strModelName));
    }//function

    protected function addTablePrefix ($strTableName) {
      return $this->strTablePrefix . $strTableName;
    }//function

    protected function getDataType ($strFieldName) {
      return self::$arrColumns[$this->strModelName][$strFieldName]['type'];
    }//function

    protected function getPrimaryKeys () {
      $arrColumns = self::$arrColumns[$this->strModelName];
      $arrPKs = array();

      foreach ($arrColumns as $strFieldName => $arrColumnInfo) {
        if (isset($arrColumnInfo['PK']) && $arrColumnInfo['PK']) {
          $arrPKs[] = $strFieldName;
        }//if
      }//foreach

      return $arrPKs;
    }//function

    protected function isColumn ($strColumn) {
      return isset(self::$arrColumns[$this->strModelName][$strColumn]);
    }//function

    protected function getColumnInfo ($strFieldName) {
      return self::$arrColumns[$this->strModelName][$strFieldName];
    }//function

    protected function getColumnList () {
      $arrColumnList = array();
      $arrColumns = self::$arrColumns[$this->strModelName];

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

      if (is_null($strData)) {
        return 'NULL';
      }//if

      switch ($strDataType) {
        case 'int':
        case 'tinyint':
        case 'decimal':
          return empty($strData) ? 0 : $strData;
          break;

        case 'char':
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

  class ModelNotFoundException extends Exception {}