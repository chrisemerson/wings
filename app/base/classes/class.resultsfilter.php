<?php
  define('ORDER_BY_ASC', 1);
  define('ORDER_BY_DESC', 2);

  class ResultsFilter {
    private $intStart;
    private $intLimit;
    private $arrOrderBy = array();
    private $strConditions = '';
    private $strModel;
    private $dbConn;

    public function start ($intStart = 0) {
      $this->intStart = $intStart;

      return $this;
    }//function

    public function limit ($intLimit = 0) {
      $this->intLimit = $intLimit;

      return $this;
    }//function

    public function orderby ($strField, $conDirection = ORDER_BY_ASC) {
      $this->arrOrderBy[] = array('field' => $strField,
                                  'order' => $conDirection);

      return $this;
    }//function

    public function conditions ($strConditionString = '') {
      $this->strConditions = $strConditionString;

      return $this;
    }//function

    public function model ($strModel = '') {
      $this->strModel = $strModel;

      return $this;
    }//function

    public function getConditionString () {
      if (empty($this->strConditions)) {
        return "";
      } else {
        return "WHERE " . $this->strConditions;
      }//if
    }//function

    public function getLimitString () {
      $strLimitString = "LIMIT ";

      if (!empty($this->intStart)) {
        $strLimitString .= intval($this->intStart) . ", ";
      }//if

      if (!empty($this->intLimit)) {
        $strLimitString .= intval($this->intLimit);
        return $strLimitString;
      }//if

      return "";
    }//function

    public function getOrderByString () {
      if (empty($this->arrOrderBy)) {
        return "";
      } else {
        $strOrderByString = "ORDER BY ";

        foreach ($this->arrOrderBy as $arrOrderByInfo) {
          $strOrderByString .= "`" . $arrOrderByInfo['field'] . "` ";

          switch ($arrOrderByInfo['order']) {
            case ORDER_BY_ASC:
              $strOrderByString .= "ASC";
              break;

            case ORDER_BY_DESC:
              $strOrderByString .= "DESC";
              break;
          }//switch

          $strOrderByString .= ", ";
        }//foreach

        return rtrim($strOrderByString, ", ");
      }//if
    }//function

    public function getModelName () {
      return $this->strModel;
    }//function

    public function setDBConn ($dbConn) {
      $this->dbConn = $dbConn;
    }//function
  }//class