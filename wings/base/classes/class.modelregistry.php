<?php
  class ModelRegistry {
    public static $arrModelInfo;

    public function __construct () {
      if (empty($arrModelInfo)) {
        $this->loadModelInfo();
      }//if
    }//function

    private function loadModelInfo () {
      $objModelConfig = simplexml_load_file(Application::getBasePath() . "config/models.xml");

      foreach ($objModelConfig->model as $objModel) {
        $arrModel = array();

        if (!empty($objModel['plural'])) {
          $arrModel['plural'] = (string) $objModel['plural'];
        } else {
          $arrModel['plural'] = $this->pluraliseModelName($objModel['name']);
        }//if

        if (!empty($objModel['table'])) {
          $arrModel['table'] = (string) $objModel['table'];
        } else {
          $arrModel['table'] = $this->convertPluralNameToTableName($arrModel['plural']);
        }//if

        self::$arrModelInfo[(string) $objModel['name']] = $arrModel;
      }//foreach
    }//function

    public function isModel ($strModelName) {
      return isset(self::$arrModelInfo[$strModelName]);
    }//function

    public function getPluralisedName ($strModelName) {
      if ($this->isModel($strModelName)) {
        return self::$arrModelInfo[$strModelName]['plural'];
      }//if

      return false;
    }//function

    public function getTableName ($strModelName) {
      if ($this->isModel($strModelName)) {
        return self::$arrModelInfo[$strModelName]['table'];
      }//if

      return false;
    }//function

    public function getModelNameFromPlural ($strPlural) {
      foreach (self::$arrModelInfo as $strTableName => $arrTableInfo) {
        if ($arrTableInfo['plural'] == $strPlural) {
          return $strTableName;
        }//if
      }//foreach

      return false;
    }//function

    private function pluraliseModelName ($strModelName) {
      return $strModelName . 's';
    }//function

    private function convertPluralNameToTableName ($strPluralName) {
      $strTableName = "";

      for ($i = 0; $i < strlen($strPluralName); $i++) {
        $strCharacter = $strPluralName{$i};

        if ($i > 0 && ord($strCharacter) >= 65 && ord($strCharacter) <= 90) {
          $strTableName .= "_" . strtolower($strCharacter);
        } else {
          $strTableName .= strtolower($strCharacter);
        }//if
      }//for

      return $strTableName;
    }//function
  }//class