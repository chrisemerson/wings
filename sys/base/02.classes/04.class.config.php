<?php
  class Config {
    private static $arrConfigs;
    private $strCurrentConfig;

    public function __construct ($strConfigData) {
      if (is_file(realpath($strConfigData))) {
        $strFilename = realpath($strConfigData);
      } else if (is_file(Application::getBasePath() . "app/config/" . $strConfigData . ".xml")) {
        $strFilename = Application::getBasePath() . "app/config/" . $strConfigData . ".xml";
      } else if (is_file(Application::getBasePath() . "sys/config/" . $strConfigData . ".xml")) {
        $strFilename = Application::getBasePath() . "sys/config/" . $strConfigData . ".xml";
      } else {
        throw new ConfigNotFoundException();
      }//if

      $this->strCurrentConfig = md5($strFilename);

      if (!isset(self::$arrConfigs[$this->strCurrentConfig])) {
        self::$arrConfigs[$this->strCurrentConfig] = $this->loadConfigFile($strFilename);
      }//if
    }//function

    private function loadConfigFile ($strFilename) {
      if ($strFilename == Application::getBasePath() . "app/config/app.xml") {
        $objAppConfig = simplexml_load_file($strFilename);
        $strEnvironment = Application::getEnvironment();

        $objDefaultConfigData = $this->convertToObject($objAppConfig->default);

        if ($strEnvironment && isset($objAppConfig->$strEnvironment)) {
          return $this->substituteValues($objDefaultConfigData, $this->convertToObject($objAppConfig->$strEnvironment));
        } else {
          return $objDefaultConfigData;
        }//if
      } else {
        return $this->convertToObject(simplexml_load_file($strFilename));
      }//if
    }//function

    private function convertToObject ($objConfigXMLData) {
      $objReturn = new stdClass();

      if (count($objConfigXMLData)) {
        foreach ($objConfigXMLData->children() as $strChildName => $objChild) {
          $objReturn->$strChildName = $this->convertToObject($objChild);
        }//foreach

        return $objReturn;
      } else {
        return (string) $objConfigXMLData;
      }//if
    }//function

    private function substituteValues ($objBaseObject, $objNewValuesObject) {
      foreach ($objBaseObject as $strName => $mixValue) {
        if (is_object($mixValue) && isset($objNewValuesObject->$strName)) {
          $objBaseObject->$strName = $this->substituteValues($objBaseObject->$strName, $objNewValuesObject->$strName);
        } else if (isset($objNewValuesObject->$strName)) {
          $objBaseObject->$strName = $objNewValuesObject->$strName;
        }//if
      }//foreach

      return $objBaseObject;
    }//function

    public function __get ($strName) {
      if (isset(self::$arrConfigs[$this->strCurrentConfig]->$strName)) {
        return self::$arrConfigs[$this->strCurrentConfig]->$strName;
      } else {
        throw new ConfigSettingNotFoundException;
      }//if
    }//function

    public function __isset ($strName) {
      return isset(self::$arrConfigs[$this->strCurrentConfig]->$strName);
    }//function
  }//class

  class ConfigNotFoundException extends Exception {}
  class ConfigSettingNotFoundException extends Exception {}