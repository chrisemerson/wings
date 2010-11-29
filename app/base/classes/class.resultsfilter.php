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
    }//function

    public function model ($strModel = '') {
      $this->strModel = $strModel;

      return $this;
    }//function

    public function getConditionString () {
      $arrTokens = $this->tokeniseString($this->strConditions);

      //Replace all the field names with {fieldname} ready for replacement later on.
      $arrSimpleOperators = array('=', '<', '>', '<=', '>=', 'LIKE', 'NOT LIKE', 'IS', 'IS NOT', '<>', '!=', '<=>');

      do {
        $arrNewTokens = $arrTokens;
        $blnStillMatches = false;

        foreach ($arrTokens as $intIndex => $strToken) {
          if (preg_match('/^(.*?)\b(?<!\'|`|\\[)(?!AND\b|OR\b|BETWEEN\b|NULL\b)([A-Z_][A-Z0-9_-]+)\b(?!\\(|\'|`|\\[\\[)(.*)$/i', $strToken, $arrMatches)) {
            $blnStillMatches = true;
            $arrNewTokens[$intIndex] = $arrMatches[1] . $this->quoteFieldName($arrMatches[2]) . $arrMatches[3];
          }//if
        }//foreach

        $arrTokens = $arrNewTokens;
      } while ($blnStillMatches);//do

      $strFinalConditionsString = $this->reconstructString($arrTokens);

      if (empty($strFinalConditionsString)) {
        return "";
      } else {
        return "WHERE " . $this->reconstructString($arrTokens);
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

    private function tokeniseString ($strConditionString = '') {
      $arrBracketStarts = array();
      $strTokenisedString = $strConditionString;
      $arrTokens = array();
      $arrTokens[0] = "DO NOT USE 0";

      $intPosition = 0;

      //Split up by brackets
      while ($intPosition < strlen($strTokenisedString)) {
        if ($strTokenisedString{$intPosition} == "(") {
          $arrBracketStarts[] = $intPosition;
        }//if

        if ($strTokenisedString{$intPosition} == ")") {
          $intEnd = $intPosition;
          $intStart = array_pop($arrBracketStarts);

          $arrTokens[] = substr($strTokenisedString, $intStart, ($intEnd - $intStart + 1));
          $strTokenisedString = substr_replace($strTokenisedString, "[[" . (count($arrTokens) - 1) . "]]", $intStart, ($intEnd - $intStart + 1));

          $arrBracketStarts = array();
          $intPosition = 0;
        } else {
          $intPosition++;
        }//if
      }//while

      $arrTokens[0] = $strTokenisedString;

      //First, replace && and || with appropriate operators
      foreach ($arrTokens as $intIndex => $strToken) {
        $strToken = preg_replace('/\s+(&&|and)\s+/i', " AND ", $strToken);
        $strToken = preg_replace('/\s+(\|\||or)\s+/i', " OR ", $strToken);

        $arrTokens[$intIndex] = $strToken;
      }//foreach

      //Now, if AND or OR is used, split token out further into individual conditions

      //Start with OR as it has lower precedence
      do {
        $blnStillMatches = false;
        $arrNewTokens = $arrTokens;

        foreach ($arrTokens as $intIndex => $strToken) {
          //If this string isn't already tokenised
          if (!preg_match('/^(\()?\[\[\d+\]\]\s+OR\s+\[\[\d+\]\](\))?$/', $strToken)) {
            if (preg_match('/^(\()?(.*)\s+OR\s+(.*?)(\))?$/i', $strToken, $arrMatches)) {
              //No point tokenising anything that is already just a token
              if (preg_match('/^\[\[(\d+)\]\]$/', $arrMatches[2], $arrTokenMatches)) {
                $intSide1TokenNo = $arrTokenMatches[1];
              } else {
                $arrNewTokens[] = $arrMatches[2];
                $intSide1TokenNo = (count($arrNewTokens) - 1);
              }//if

              if (preg_match('/^\[\[(\d+)\]\]$/', $arrMatches[3], $arrTokenMatches)) {
                $intSide2TokenNo = $arrTokenMatches[1];
              } else {
                $arrNewTokens[] = $arrMatches[3];
                $intSide2TokenNo = (count($arrNewTokens) - 1);
              }//if

              if (!isset($arrMatches[4])) {
                $arrMatches[4] = "";
              }//if

              $arrNewTokens[$intIndex] = $arrMatches[1] . "[[" . $intSide1TokenNo . "]] OR [[" . $intSide2TokenNo . "]]" . $arrMatches[4];

              $blnStillMatches = true;
            }//if
          }//if
        }//foreach

        $arrTokens = $arrNewTokens;
      } while ($blnStillMatches);//do

      //Before splitting on AND, need to find all BETWEEN operators, which also use AND, and tokenise those
      do {
        $blnStillMatches = false;
        $arrNewTokens = $arrTokens;

        foreach ($arrTokens as $intIndex => $strToken) {
          if (preg_match('/^(\s*.*\s+)(\S+(?:\s+NOT)?\s+BETWEEN\s+(?:\'?[^\']+\'|\S+)\s+AND\s+(?:\'?[^\']+\'|\S+))(.*)$/', $strToken, $arrMatches)) {
            $arrNewTokens[] = $arrMatches[2];

            if (!isset($arrMatches[6])) {
              $arrMatches[6] = "";
            }//if

            $arrNewTokens[$intIndex] = $arrMatches[1] . "[[" . (count($arrNewTokens) - 1) . "]]" . $arrMatches[6];
          }//if
        }//foreach

        $arrTokens = $arrNewTokens;
      } while ($blnStillMatches);//do

      //Next, split out on AND
      do {
        $blnStillMatches = false;
        $arrNewTokens = $arrTokens;

        foreach ($arrTokens as $intIndex => $strToken) {
          //If this string isn't already tokenised
          if (!preg_match('/^(\()?\[\[\d+\]\]\s+AND\s+\[\[\d+\]\](\))?$/', $strToken)) {
            if (preg_match('/^(\()?(.*)\s+AND\s+(.*?)(\))?$/i', $strToken, $arrMatches)) {
              if (!preg_match('/BETWEEN\s+\d+$/', $arrMatches[2]) || !preg_match('/^\d+\s*$/', $arrMatches[3])) {
                //No point tokenising anything that is already just a token
                if (preg_match('/^\[\[(\d+)\]\]$/', $arrMatches[2], $arrTokenMatches)) {
                  $intSide1TokenNo = $arrTokenMatches[1];
                } else {
                  $arrNewTokens[] = $arrMatches[2];
                  $intSide1TokenNo = (count($arrNewTokens) - 1);
                }//if

                if (preg_match('/^\[\[(\d+)\]\]$/', $arrMatches[3], $arrTokenMatches)) {
                  $intSide2TokenNo = $arrTokenMatches[1];
                } else {
                  $arrNewTokens[] = $arrMatches[3];
                  $intSide2TokenNo = (count($arrNewTokens) - 1);
                }//if

                if (!isset($arrMatches[4])) {
                  $arrMatches[4] = "";
                }//if

                $arrNewTokens[$intIndex] = $arrMatches[1] . "[[" . $intSide1TokenNo . "]] AND [[" . $intSide2TokenNo . "]]" . $arrMatches[4];

                $blnStillMatches = true;
              }//if
            }//if
          }//if
        }//foreach

        $arrTokens = $arrNewTokens;
      } while ($blnStillMatches);//do

      return $arrTokens;
    }//function

    private function reconstructString ($arrTokens) {
      do {
        $blnTokensStillExist = false;

        if (preg_match('/\[\[(\d+)\]\]/', $arrTokens[0], $arrMatches)) {
          $blnTokensStillExist = true;
          $arrTokens[0] = str_replace('[[' . $arrMatches[1] . ']]', $arrTokens[$arrMatches[1]], $arrTokens[0]);
          unset($arrTokens[$arrMatches[1]]);
        }//if
      } while ($blnTokensStillExist);

      return $arrTokens[0];
    }//function

    private function quoteFieldName ($strFieldName) {
      $arrFields = array('test1',
                         'test5',
                         'test3',
                         'test4',
                         'test34',
                         'testq',
                         'test23');

      if (in_array($strFieldName, $arrFields)) {
        return "`" . $strFieldName . "`";
      } else {
        if (!empty($this->dbConn)) {
          return "'" . $this->dbConn->escape_string($strFieldName) . "'";
        } else {
          return "'" . $strFieldName . "'";
        }//if
      }//if
    }//function

    public function setDBConn ($dbConn) {
      $this->dbConn = $dbConn;
    }//function
  }//class

  class InvalidConditionsException extends Exception {}