<?php
  /*************************************/
  /* Template Class - by Chris Emerson */
  /* http://www.cemerson.co.uk/        */
  /*                                   */
  /* Version 0.3                       */
  /* 1st December 2009                 */
  /*************************************/

  class Template {
    private $strTemplateName;
    private $arrOriginalTemplateLines = array();

    private $arrVariables = array();

    private $arrBlockInformation = array();

    /* RegEx Constants */
    const RE_BLOCK_START = "/^\\s*<!--\\s*\\[\\s*([a-z0-9_-]+)\\s*(\\|\\s*([\\w,]+)\\s*)?\\]\\s*-->\\s*\$/i";
    const RE_BLOCK_END = "/^\\s*<!--\\s*\\[\\s*\\/([a-z0-9_-]+)\\s*\\]\\s*-->\\s*\$/i";
    const RE_INCLUDE = "/^(\\s*)<!--\\s*{\\s*([a-z0-9._-]+)\\s*}\\s*-->\\s*\$/i";
    const RE_INCLUDE_VARIABLE = "/^(\\s*)<!--\\s*{\\s*{([a-z0-9._-]+)}\\s*}\\s*-->\\s*\$/i";
    const RE_SLAVE_INCLUDE = "/^(\\s*)<!--\\s*<\\s*([a-z0-9._-]+)\\s*>\\s*-->\\s*\$/i";

    /********************************/
    /* Constructor & Initialisation */
    /********************************/

    public function __construct($strTemplateName, $blnIgnoreMasterTemplateSetting = false) {
      $this->strTemplateName = $strTemplateName;
      $strTemplateFile = Application::getBasePath() . "templates/" . str_replace(".", "/", $strTemplateName) . ".tpl";

      if (!file_exists($strTemplateFile)) {
        throw new TemplateNotFoundException();
      }//if

      $this->arrOriginalTemplateLines = file($strTemplateFile);

      if (!$blnIgnoreMasterTemplateSetting) {
        $this->applyMasterTemplate();
      }//if

      $this->initialiseBlock();
    }//function


    private function applyMasterTemplate () {
      try {
        $objAppConfig = Config::get('app');
        $strThisClassName = __CLASS__;
        $objMasterTemplate = new $strThisClassName($objAppConfig->template->mastertemplate, true);
        $arrMasterTemplateLines = $objMasterTemplate->returnOriginalContentLines();

        $arrNewTemplateLines = array();

        foreach ($arrMasterTemplateLines as $strMasterTemplateLine) {
          if (preg_match(self::RE_SLAVE_INCLUDE, $strMasterTemplateLine, $arrMatches)) {
            $strOriginalTemplateIndent = $arrMatches[1];

            foreach ($this->arrOriginalTemplateLines as $strOriginalTemplateLine) {
              $arrNewTemplateLines[] = rtrim($strOriginalTemplateIndent . $strOriginalTemplateLine, "\r\n") . PHP_EOL;
            }//foreach
          } else {
            $arrNewTemplateLines[] = $strMasterTemplateLine;
          }//if
        }//foreach

        $this->arrOriginalTemplateLines = $arrNewTemplateLines;
      } catch (ConfigSettingNotFoundException $exException) {
        //Master Template variable not set - do nothing
      }//try
    }//function

    private function initialiseBlock ($strBlockName = "") {
      if ($strBlockName == "") {
        if (!isset($this->arrBlockInformation[$strBlockName]['originallines'])) {
          $this->arrBlockInformation[$strBlockName]['originallines'] = $this->arrOriginalTemplateLines;
        }//if
      } else if (!isset($this->arrBlockInformation[$strBlockName])) {
        throw new BlockNotFoundException();
      }//if

      $arrLines = $this->arrBlockInformation[$strBlockName]['originallines'];

      //Check to make sure these aren't set to avoid overwriting already parsed blocks

      if (!isset($this->arrBlockInformation[$strBlockName]['startlines'])) {
        $this->arrBlockInformation[$strBlockName]['startlines'] = array();
      }//if

      if (!isset($this->arrBlockInformation[$strBlockName]['options'])) {
        $this->arrBlockInformation[$strBlockName]['options'] = array();
      }//if

      if (!isset($this->arrBlockInformation[$strBlockName]['outputlines'])) {
        $this->arrBlockInformation[$strBlockName]['outputlines'] = array();
      }//if

      $strCurrentSubBlock = "";
      $blnCurrentlyInsideSubBlock = false;
      $arrSubBlocks = array();
      $intBlockStack = 0;

      foreach ($arrLines as $intLineNo => $strLine) {
        //Look for end block first - to switch off lines going to block info array
        if (preg_match(self::RE_BLOCK_END, $strLine, $arrMatches)) {
          if (strcmp(ltrim($strBlockName . "." . $arrMatches[1], "."), $strCurrentSubBlock) == 0) {
            if ($intBlockStack == 1) {
              $blnCurrentlyInsideSubBlock = false;
            }//if
          } else if ($intBlockStack == 1) {
            //If the stack got to 1, the end tag didn't match the start tag, so block formatting error
            throw new BlockFormattingException();
          }//if

          $intBlockStack--;
        }//if

        //If inside a sub block, add the line to the sub block's originallines
        if ($blnCurrentlyInsideSubBlock) {
          $this->arrBlockInformation[$strCurrentSubBlock]['originallines'][] = $strLine;
        }//if

        //Check to make sure the template isn't trying to include itself outside of a block - infinite recursion
        if (!$blnCurrentlyInsideSubBlock && preg_match(self::RE_INCLUDE, $strLine, $arrMatches)) {
          if (($strBlockName == "") && (strcasecmp($arrMatches[1], $this->strTemplateName) == 0)) {
            throw new InvalidIncludeRecursionException();
          }//if
        }//if

        //Look for start block last - so lines go into block info array on the next line
        if (preg_match(self::RE_BLOCK_START, $strLine, $arrMatches)) {
          if ($intBlockStack == 0) {
            $strCurrentSubBlock = ltrim($strBlockName . "." . $arrMatches[1], ".");

            //Check we don't already have this block name at this level
            if (in_array($strCurrentSubBlock, $arrSubBlocks)) {
              throw new BlockFormattingException();
            }//if

            $arrSubBlocks[] = $strCurrentSubBlock;
            $blnCurrentlyInsideSubBlock = true;

            if (isset($arrMatches[3])) {
              $arrBlockOptions = explode(",", $arrMatches[3]);
            } else {
              $arrBlockOptions = array();
            }//if

            $this->arrBlockInformation[$strCurrentSubBlock]['options'] = $arrBlockOptions;
            $this->arrBlockInformation[$strBlockName]['startlines'][$intLineNo] = $strCurrentSubBlock;
            $this->arrBlockInformation[$strCurrentSubBlock]['originallines'] = array();
          }//if

          $intBlockStack++;
        }//if
      }//foreach

      //This means end tags didn't match start tags - error
      if ($intBlockStack != 0) {
        throw new BlockFormattingException();
      }//if

      //Initialise all the block children we found
      foreach ($arrSubBlocks as $strSubBlockName) {
        $this->initialiseBlock($strSubBlockName);
      }//foreach
    }//function

    /***********************/
    /* Variable Assignment */
    /***********************/

    public function __set ($strName, $strValue) {
      $this->setVar($strName, $strValue);
    }//function

    public function setVar ($strName, $strValue) {
      $this->arrVariables[strtolower($strName)] = $strValue;
    }//function

    public function assign ($mixNameOrArray, $strValue = "") {
      if (is_array($mixNameOrArray)) {
        array_map(array($this, 'setVar'), array_keys($mixNameOrArray), array_values($mixNameOrArray));
      } else {
        $this->setVar($mixNameOrArray, $strValue);
      }//if
    }//function

    public function __get ($strName) {
      return $this->getVar($strName);
    }//function

    public function getVar ($strName) {
      if (!$this->isVarSet($strName)) {
        return false;
      }//if

      return $this->arrVariables[$strName];
    }//function

    public function __isset ($strName) {
      return $this->isVarSet($strName);
    }//function

    public function isVarSet ($strName) {
      return isset($this->arrVariables[$strName]);
    }//function

    public function __unset ($strName) {
      $this->clearVar($strName);
    }//function

    public function clearVar ($strName) {
      unset($this->arrVariables[$strName]);
    }//function

    public function clearAllVars () {
      $this->arrVariables = array();
    }//function

    /*****************/
    /* Block Parsing */
    /*****************/

    public function parse ($strBlockName = "") {
      while (!isset($this->arrBlockInformation[$strBlockName])) {
        $strBlockNameForLoop = $strBlockName;

        while (!isset($this->arrBlockInformation[$strBlockNameForLoop])) {
          $strOldBlockNameForLoop = $strBlockNameForLoop;
          $strBlockNameForLoop = preg_replace('/(?:^|\.)[a-z0-9_-]+$/', '', $strBlockNameForLoop);
        }//while

        $blnIncludesFound = $this->parseIncludes($strBlockNameForLoop);

        if (!$blnIncludesFound || !isset($this->arrBlockInformation[$strOldBlockNameForLoop])) {
          throw new BlockNotFoundException();
        }//if
      }//while

      $this->parseIncludes($strBlockName);

      $arrBlockOriginalLines = $this->arrBlockInformation[$strBlockName]['originallines'];
      $arrStartLines = $this->arrBlockInformation[$strBlockName]['startlines'];

      //Loop through the lines in the block
      for ($intOriginalLineNo = 0; $intOriginalLineNo < count($arrBlockOriginalLines); $intOriginalLineNo++) {
        //If the current line is the start of a child block
        if (in_array($intOriginalLineNo, array_keys($arrStartLines))) {
          $strChildBlockName = $arrStartLines[$intOriginalLineNo];

          $this->applyOptions($strChildBlockName);

          $intNoOfLines = count($this->arrBlockInformation[$strChildBlockName]['originallines']);

          foreach ($this->arrBlockInformation[$strChildBlockName]['outputlines'] as $strChildBlockOutputLine) {
            $this->arrBlockInformation[$strBlockName]['outputlines'][] = $strChildBlockOutputLine;
          }//foreach

          $this->arrBlockInformation[$strChildBlockName]['outputlines'] = array();

          $intOriginalLineNo += $intNoOfLines + 1;
        } else {
          $this->arrBlockInformation[$strBlockName]['outputlines'][] = $this->replaceVariables($arrBlockOriginalLines[$intOriginalLineNo]);
        }//if
      }//for

      $this->clearAllVars();
    }//function

    /********************/
    /* Includes Parsing */
    /********************/

    private function parseIncludes ($strBlockName = "") {
      $arrFinalLines = array();
      $intLineCounter = 0;

      if (!isset($this->arrBlockInformation[$strBlockName])) {
        throw new BlockNotFoundException;
      }//if

      $arrLines = $this->arrBlockInformation[$strBlockName]['originallines'];

      $blnIncludeFound = false;

      foreach ($arrLines as $intLineNo => $strLine) {
        $blnIncludeFoundThisLoop = false;
        $blnProcessLine = true;

        if (preg_match(self::RE_INCLUDE, $strLine, $arrMatches)) {
          $blnIncludeFound = true;
          $blnIncludeFoundThisLoop = true;

          $strIncludedTemplateIndent = $arrMatches[1];
          $strIncludedTemplateName = $arrMatches[2];
        }//if

        if (preg_match(self::RE_INCLUDE_VARIABLE, $strLine, $arrMatches)) {
          if (isset($this->arrVariables[strtolower($arrMatches[2])])) {
            $strIncludedTemplateName = $this->arrVariables[strtolower($arrMatches[2])];
            $strIncludedTemplateIndent = $arrMatches[1];

            $blnIncludeFound = true;
            $blnIncludeFoundThisLoop = true;
          } else {
            $blnProcessLine = false;
          }//if
        }//if

        if ($blnIncludeFoundThisLoop) {
          $strThisClassName = __CLASS__;
          $objIncludedTemplate = new $strThisClassName($strIncludedTemplateName, true);
          $arrIncludedTemplateLines = $objIncludedTemplate->returnOriginalContentLines();

          $intIncludedLines = -1;

          foreach ($arrIncludedTemplateLines as $strIncludedTemplateLine) {
            $arrFinalLines[$intLineCounter] = rtrim($strIncludedTemplateIndent . $strIncludedTemplateLine, "\r\n") . PHP_EOL;
            $intIncludedLines++;
            $intLineCounter++;
          }//foreach

          //Because we have changed the line numbers / number of lines, we have to recalculate the start lines of child blocks now

          $arrBlockStartLines = $this->arrBlockInformation[$strBlockName]['startlines'];
          $arrNewBlockStartLines = array();

          foreach ($arrBlockStartLines as $intChildBlockStartLine => $strChildBlockName) {
            $intNewChildBlockStartLine = $intChildBlockStartLine;

            if ($intChildBlockStartLine >= $intLineNo) {
              $intNewChildBlockStartLine += $intIncludedLines;
            }//if

            $arrNewBlockStartLines[$intNewChildBlockStartLine] = $strChildBlockName;
          }//foreach

          $this->arrBlockInformation[$strBlockName]['startlines'] = $arrNewBlockStartLines;
        } else {
          if ($blnProcessLine) {
            $arrFinalLines[$intLineCounter] = $strLine;
            $intLineCounter++;
          }//if
        }//if
      }//foreach

      if ($blnIncludeFound) {
        $this->arrBlockInformation[$strBlockName]['originallines'] = $arrFinalLines;
        $this->initialiseBlock($strBlockName);
      }//if

      return $blnIncludeFound;
    }//function

    /*******************/
    /* Return & Output */
    /*******************/

    public function returnOriginalContentLines () {
      return $this->arrOriginalTemplateLines;
    }//function

    public function returnOutput () {
      return implode("", $this->arrBlockInformation['']['outputlines']);
    }//function

    public function out () {
      echo $this->returnOutput();
    }//function

    /*********************/
    /* Private Functions */
    /*********************/

    private function replaceVariables ($strTemplateContent) {
      $arrVariables = $this->arrVariables;

      foreach ($arrVariables as $strTag => $strContent) {
        $strTemplateContent = str_ireplace("{" . $strTag . "}", $strContent, $strTemplateContent);
      }//foreach

      return $strTemplateContent;
    }//function

    private function applyOptions ($strBlockName = "") {
      $arrOptions = $this->arrBlockInformation[$strBlockName]['options'];
      $arrBlockLines = $this->arrBlockInformation[$strBlockName]['outputlines'];

      foreach ($arrOptions as $strOption) {
        switch ($strOption) {
          case 'tt':
            $strTopLine = trim($arrBlockLines[0]);

            if (empty($strTopLine)) {
              unset($arrBlockLines[0]);
              $arrBlockLines = array_values($arrBlockLines);
            }//if
            break;

          case 'tb':
            $intLastArrayElement = count($arrBlockLines) - 1;
            $strBottomLine = trim($arrBlockLines[$intLastArrayElement]);

            if (empty($strBottomLine)) {
              unset($arrBlockLines[$intLastArrayElement]);
            }//if
            break;
        }//switch
      }//foreach

      $this->arrBlockInformation[$strBlockName]['outputlines'] = $arrBlockLines;
    }//function
  }//class

  //Exceptions

  class BlockFormattingException extends Exception {}
  class BlockNotFoundException extends Exception {}
  class InvalidIncludeRecursionException extends Exception {}
  class TemplateNotFoundException extends Exception {}
?>