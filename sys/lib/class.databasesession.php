<?php
  class DatabaseSession {
    public static function open ($strSavePath, $strSessionName) {
      //Nothing to do here
    }//function

    public static function close () {
      //Nothing to do here
    }//function

    public static function read ($strSessionID) {
      $arrConfigData = self::getConfigData();

      $strModelName = $arrConfigData['model'];
      $strDataField = $arrConfigData['datafield'];

      try {
        $objDBSession = new $strModelName($strSessionID);

        return $objDBSession->$strDataField;
      } catch (NoDataFoundException $e) {
        return "";
      }//try
    }//function

    public static function write ($strSessionID, $strData) {
      $arrConfigData = self::getConfigData();

      $strModelName = $arrConfigData['model'];
      $strIDField = $arrConfigData['idfield'];
      $strAccessTimeField = $arrConfigData['accesstimefield'];
      $strDataField = $arrConfigData['datafield'];

      try {
        $objDBSession = new $strModelName($strSessionID);
      } catch (NoDataFoundException $e) {
        $objDBSession = new $strModelName();
        $objDBSession->$strIDField = $strSessionID;
      }//try

      $objDBSession->$strAccessTimeField = date('Y-m-d H:i:s');
      $objDBSession->$strDataField = $strData;

      $objDBSession->save();
    }//function

    public static function destroy ($strSessionID) {
      $arrConfigData = self::getConfigData();

      $strModelName = $arrConfigData['model'];

      try {
        $objDBSession = new $strModelName($strSessionID);

        $objDBSession->delete();
      } catch (NoDataFoundException $e) {
        //Nothing to do here - Session doesn't exist!
      }//try
    }//function

    public static function gc ($intMaxLifetime) {
      $arrConfigData = self::getConfigData();

      $strModelName = $arrConfigData['model'];
      $strAccessTimeField = $arrConfigData['accesstimefield'];
      $strCollectionName = $strModelName . "Collection";

      $strDate = date('Y-m-d H:i:s', strtotime("NOW - " . $intMaxLifetime . " seconds"));

      $objDBSessions = new $strCollectionName("WHERE " . $strAccessTimeField . " <= '" . $strDate . "'");

      $objDBSessions->delete();
    }//function

    private static function getConfigData () {
      $objDBSessionsConfig = new Config('dbsessions');

      $arrConfigData = array();

      $arrConfigData['model'] = $objDBSessionsConfig->model;
      $arrConfigData['idfield'] = $objDBSessionsConfig->fields->id;
      $arrConfigData['accesstimefield'] = $objDBSessionsConfig->fields->accesstime;
      $arrConfigData['datafield'] = $objDBSessionsConfig->fields->data;

      return $arrConfigData;
    }//if
  }//class