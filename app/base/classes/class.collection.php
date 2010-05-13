<?php
  define('ORDER_BY_ASC', 1);
  define('ORDER_BY_DESC', 2);

  class Collection {
    private $dbConn;
    private $strTablePrefix;

    private $strModelName;

    private $objSchema;

    private $arrWhere = array();
    private $arrOrderBy = array();
    private $intLimit = 0;

    private $arrMembers;

    public function __construct ($strModelName) {
      $this->strModelName = $strModelName;
      $this->objSchema = new Schema(strtolower($strModelName));

      $objDBConfig = Config::get('db');

      $strClassName = $objDBConfig->driver . "Driver";
      $this->dbConn = new $strClassName($objDBConfig->host, $objDBConfig->user, $objDBConfig->pass, $objDBConfig->name);

      $this->strTablePrefix = $objDBConfig->prefix;
    }//function

    public function fetch () {
      $arrWhere = $this->arrWhere;
      $arrOrderBy = $this->arrOrderBy;

      $strModelName = $this->strModelName;

      $strSQL = "SELECT * FROM `" . $this->strTablePrefix . $this->objSchema->getTableName() . "`";

      if (count($arrWhere) > 0) {
        $strSQL .= " WHERE ";

        $arrWhereStrings = array();

        foreach ($arrWhere as $strFieldName => $mixData) {
          $arrWhereStrings[] .= "`" . $strFieldName . "` = " . $mixData;
        }//foreach

        $strSQL .= implode(" AND ", $arrWhereStrings);
      }//if

      if (count($arrOrderBy) > 0) {
        $strSQL .= " ORDER BY ";

        $arrOrderByStrings = array();

        foreach ($arrOrderBy as $arrOrderByInfo) {
          $strOrderByString = $arrOrderByInfo['field'] . " ";

          if ($arrOrderByInfo['dir'] == ORDER_BY_ASC) {
            $strOrderByString .= "ASC";
          } else if ($arrOrderByInfo['dir'] == ORDER_BY_DESC) {
            $strOrderByString .= "DESC";
          }//if

          $arrOrderByStrings[] = $strOrderByString;
        }//foreach

        $strSQL .= implode(", ", $arrOrderByStrings);
      }//if

      if ($this->intLimit != 0) {
        $strSQL .= " LIMIT " . $this->intLimit;
      }//if

      $strSQL .= ";";

      $dbResults = $this->dbConn->query($strSQL);

      $arrMembers = array();

      while ($arrResult = $dbResults->fetch_assoc()) {
        $objModel = new $strModelName;
        $objModel->loadFromDBArray($arrResult);
        $arrMembers[] = $objModel;
      }//while

      $this->arrMembers = $arrMembers;
    }//function

    public function addCondition ($strField, $mixValue) {
      $this->arrWhere[$strField] = $mixValue;
    }//function

    public function addOrderBy ($strField, $conDirection = ORDER_BY_ASC) {
      $this->arrOrderBy[] = array('field' => $strField, 'dir' => $conDirection);
    }//function

    public function setLimit ($intLimit) {
      $this->intLimit = $intLimit;
    }//function

    public function getMembers () {
      return $this->arrMembers;
    }//function

    public function getMemberCount () {
      return count($this->arrMembers);
    }//function
  }//class
?>