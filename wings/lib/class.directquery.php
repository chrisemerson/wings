<?php
  class DirectQuery extends Database {
    public function __construct () {
      parent::__construct();
    }//function

    public function __get ($strName) {
      $arrAllowedProperties = array('affected_rows',
                                    'insert_id',
                                    'connect_errno',
                                    'connect_error',
                                    'errno',
                                    'error');

      if (in_array($strName, $arrAllowedProperties)) {
        return $this->dbConn->$strName;
      } else {
        return null;
      }//if
    }//function

    public function query ($strSQL) {
      return $this->dbConn->query($strSQL);
    }//function

    public function multi_query ($strSQL) {
      return $this->dbConn->multi_query($strSQL);
    }//function

    public function escape_string ($strString) {
      return $this->dbConn->escape_string($strString);
    }//function

    public function close () {
      return $this->dbConn->close();
    }//function
  }//class