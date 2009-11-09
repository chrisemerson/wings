<?php
 /***********************************/
 /* Config Class - by Chris Emerson */
 /* http://www.cemerson.co.uk/      */
 /*                                 */
 /* Version 0.1                     */
 /* 23rd May 2009                   */
 /***********************************/

 class Config {
  private static $arrConfigObjects = array();
  private        $arrConfigData = array();

  private function __construct ($mixConfigData) {
   if (is_array($mixConfigData)) {
    $this->arrConfigData = $mixConfigData;
   } else if (is_file($mixConfigData)) {
    $this->arrConfigData = $this->getConfigDataFromXML(simplexml_load_file($mixConfigData));
   } else {
    $strConfigFilename = dirname(__FILE__) . "/../../config/" . $mixConfigData . ".xml";
    $this->arrConfigData = $this->getConfigDataFromXML(simplexml_load_file($strConfigFilename));
   }//if
  }//function

  public static function get ($mixConfigData) {
   if (is_file($mixConfigData)) {
    $mixConfigData = $this->resolveFilename($mixConfigData);
   }//if

   if (!isset(self::$arrConfigObjects[$mixConfigData])) {
    self::$arrConfigObjects[$mixConfigData] = new Config($mixConfigData);
   }//if

   return self::$arrConfigObjects[$mixConfigData];
  }//function

  private function getConfigDataFromXML ($objXMLData) {
   $arrData = array();

   foreach ($objXMLData as $strElementName => $objElement) {
    $strElementValue = trim((string) $objElement);

    if (isset($arrData[$strElementName])) {
     throw new ConfigSettingAlreadyExistsException;
    }//if

    if (count($objElement) > 0) {
     $arrData[$strElementName] = $this->getConfigDataFromXML($objElement);
    } else {
     $arrData[$strElementName] = trim((string) $objElement);
    }//if
   }//foreach

   return $arrData;
  }//function

  private function resolveFilename ($strFilename) {
   return realpath(dirname($strFilename)) . '/' . basename($strFilename);
  }//function

  public function __get ($strConfigSetting) {
   if (!isset($this->arrConfigData[$strConfigSetting])) {
    throw new ConfigSettingNotFoundException;
   } else if (is_array($this->arrConfigData[$strConfigSetting])) {
    return new Config($this->arrConfigData[$strConfigSetting]);
   } else {
    return $this->arrConfigData[$strConfigSetting];
   }//if
  }//function

  public function __toString () {
   return '';
  }//function
 }//class

 //Exceptions

 class ConfigSettingNotFoundException extends Exception {}
 class ConfigSettingAlreadyExistsException extends Exception {}
?>