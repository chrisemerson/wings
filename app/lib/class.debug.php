<?php
  class Debug {
    private static function isDebugMode () {
      $objAppConfig = new Config('app');

      return ($objAppConfig->errors->debugmode == 1);
    }//function

    public static function e ($mixInput) {
      if (self::isDebugMode()) {
        echo $mixInput;
      }//if
    }//function

    public static function vd ($mixInput) {
      if (self::isDebugMode()) {
        var_dump($mixInput);
      }//if
    }//function

    public static function mt ($blnGetAsFloat) {
      if (self::isDebugMode()) {
        microtime($blnGetAsFloat);
      }//if
    }//function

    public static function db ($blnProvideObject) {
      if (self::isDebugMode()) {
        debug_backtrace($blnProvideObject);
      }//if
    }//function

    public static function pr ($mixInput, $blnReturn = false) {
      if (self::isDebugMode()) {
        print_r($mixInput, $blnReturn);
      }//if
    }//function
  }//class