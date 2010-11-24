<?php
  define('ORDER_BY_ASC', 1);
  define('ORDER_BY_DESC', 2);

  class ResultFilter {
    private $intStart;
    private $intLimit;
    private $arrOrderBy = array();
    private $arrConditions = array();

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
      echo $strConditionString . "<br><br>\n\n";

      $arrBracketStarts = array();
      $strTokenisedString = $strConditionString;
      $arrTokens = array();

      $intPosition = 0;

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
          print_r($strTokenisedString . "<br>\n");
        } else {
          $intPosition++;
        }//if
      }//for

      foreach ($arrTokens as $intIndex => $strToken) {
        //First, replace && and || with appropriate operands
        $strToken = str_replace("&&", "AND", $strToken);
        $strToken = str_replace("||", "OR", $strToken);

        $arrTokens[$intIndex] = $strToken;

        //Now, if AND or OR is used, split token out further into individual conditions

        if (preg_match('/^\((.*)\s+(?:AND|OR)\s+(.*)\)$/', $strToken, $arrMatches)) {
          print_r($arrMatches);
        }//if
      }//foreach

      echo "<pre>";
      print_r($arrTokens);
      echo "</pre>";
    }//function
  }//class

  class InvalidConditionsException extends Exception {}