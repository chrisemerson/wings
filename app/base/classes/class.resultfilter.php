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
      echo "<h2>Original String</h2>\n";

      echo $strConditionString . "<br><br>\n\n";

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
          if (preg_match('/^(.*\s+)(\S+\s+BETWEEN\s+\d+\s+AND\s+\d+)(.*)$/', $strToken, $arrMatches)) {
            $arrNewTokens[] = $arrMatches[2];

            if (!isset($arrMatches[3])) {
              $arrMatches[3] = "";
            }//if

            $arrNewTokens[$intIndex] = $arrMatches[1] . "[[" . (count($arrNewTokens) - 1) . "]]" . $arrMatches[3];
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

      echo "<h2>Tokens - After Tokenisation</h2>\n";
      dump($arrTokens);

      echo "<h2>Tokens - Conditions</h2>\n";

      //Now, go through the tokens and work out which ones are conditions, ready to be correctly formatted. Replace all the field names with {fieldname} ready for replacement later on.

      $arrSimpleOperators = array('=', '<', '>', '<=', '>=', 'LIKE', 'IS', 'IS NOT', '<>', '!=');

      foreach ($arrTokens as $intIndex => $strToken) {
        //'Standard' 2 sided conditions
        if (preg_match('/^\s*(\(?)\s*((\w+)\((.*?)\)|(.*?))\s+(' . implode("|", $arrSimpleOperators) . ')\s+(.*?)\s*(\)?)\s*$/', $strToken, $arrMatches)) {
          dump($arrMatches);
        }//if
      }//foreach
    }//function
  }//class

  class InvalidConditionsException extends Exception {}

  function dump ($var) {
    if (is_array($var)) {
      echo "<pre>\n";
      print_r($var);
      echo "</pre>";
    } else {
      var_dump($var);
    }//if
  }//function