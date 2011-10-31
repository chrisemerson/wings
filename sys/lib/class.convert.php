<?php
  class Convert {
    public static function __callStatic ($strName, $arrArgs) {
      $xmlConvertConfig = simplexml_load_file(Application::getAppFilename('config/convert.xml'));
      $arrConversionRates = array();

      foreach ($xmlConvertConfig->group as $xmlGroup) {
        $arrGroup = array();

        foreach ($xmlGroup->unit as $xmlUnit) {
          $arrGroup[(string) $xmlUnit['code']] = (float) $xmlUnit;
        }//foreach

        $arrConversionRates[] = $arrGroup;
      }//foreach

      $decValue = $arrArgs[0];

      if (preg_match('/^([A-Z]+)to([A-Z]+)$/', $strName, $arrMatches)) {
        $strFrom = strtoupper($arrMatches[1]);
        $strTo = strtoupper($arrMatches[2]);

        foreach ($arrConversionRates as $arrConversionGroup) {
          if (in_array($strFrom, array_keys($arrConversionGroup)) && in_array($strTo, array_keys($arrConversionGroup))) {
            $decFrom = $arrConversionGroup[$strFrom];
            $decTo = $arrConversionGroup[$strTo];

            return $decValue * ($decTo / $decFrom);
          }//if
        }//foreach
      }//if

      throw new ConversionNotFoundException();
    }//function

    //Temperature conversion is more complex, since each system uses a different zero point

    public static function CtoF ($decValue) {
      return $decValue * 1.8 + 32;

    }//function

    public static function CtoK ($decValue) {
      return $decValue + 273.15;
    }//function

    public static function FtoC ($decValue) {
      return ($decValue - 32) / 1.8;
    }//function

    public static function FtoK ($decValue) {
      return ($decValue + 459.67) / 1.8;
    }//function

    public static function KtoC ($decValue) {
      return $decValue - 273.15;
    }//function

    public static function KtoF ($decValue) {
      return ($decValue * 1.8) - 459.67;
    }//function
  }//class

  class ConversionNotFoundException extends Exception {}