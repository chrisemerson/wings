<?php
  class Collection {
    private $dbConn;
    private $strTablePrefix;

    private $strModelName;

    private $objSchema;

    private $arrMembers;

    public function __construct ($strModelName) {
      $this->strModelName = $strModelName;
      $this->objSchema = new Schema(strtolower($strModelName));

      $objDBConfig = Config::get('db');

      $strClassName = $objDBConfig->driver . "Driver";
      $this->dbConn = new $strClassName($objDBConfig->host, $objDBConfig->user, $objDBConfig->pass, $objDBConfig->name);

      $this->strTablePrefix = $objDBConfig->prefix;
    }//function

    public function fetch ($arrWhere = array()) {
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

      $strSQL .= ";";

      $dbResults = $this->dbConn->query($strSQL);

      $arrMembers = array();

      while ($arrResult = $dbResults->fetch_assoc()) {
        $objModel = new $strModelName;
        $objModel->loadFromArray($arrResult);
        $arrMembers[] = $objModel;
      }//while

      $this->arrMembers = $arrMembers;
    }//function

    public function getMembers () {
      return $this->arrMembers;
    }//function

    public function getMemberCount () {
      return count($this->arrMembers);
    }//function
  }//class
?>