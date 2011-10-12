<?php
  class Convert {
    private static $arrConversionRates = array(//Distance
                                               array('CM'     => 185200,        //Centimetre
                                                     'FT'     => 6076.11549,    //Foot
                                                     'IN'     => 72913.3858,    //Inch
                                                     'KM'     => 1.85200,       //Kilometre
                                                     'M'      => 1852,          //Metre
                                                     'MI'     => 1.15077945,    //Mile
                                                     'MM'     => 1852000,       //Millimetre
                                                     'NM'     => 1,             //Nautical Mile
                                                     'YD'     => 2025.37183),   //Yard

                                               //Weight
                                               array('G'      => 1016046.91,    //Gram
                                                     'KG'     => 1016.04691,    //Kilogram
                                                     'LB'     => 2240,          //Pound
                                                     'OZ'     => 35840,         //Ounce
                                                     'ST'     => 160,           //Stone
                                                     'TONS'   => 1,             //Ton
                                                     'TONNES' => 1.01604691),   //Tonne

                                               //Volume
                                               array('CC'     => 1000000,       //Cubic Centimetre
                                                     'CFT'    => 35.3146667,    //Cubic Foot
                                                     'CIN'    => 61023.7441,    //Cubic Inch
                                                     'CM'     => 1,             //Cubic Metre
                                                     'CMM'    => 1000000000,    //Cubic Millimetre
                                                     'GAL'    => 219.969157,    //Gallon
                                                     'L'      => 1000,          //Litre
                                                     'ML'     => 1000000,       //Millilitre
                                                     'OZ'     => 35195.0652,    //Fluid Ounce
                                                     'PT'     => 1759.75326,    //Pint
                                                     'QT'     => 879.87663,     //Quart
                                                     'USGAL'  => 264.172052,    //US Gallon
                                                     'USOZ'   => 33814.0227,    //US Fluid Ounce
                                                     'USPT'   => 2113.37642,    //US Pint
                                                     'USQT'   => 1056.68821),   //US Quart

                                               //Angles
                                               array('DEG'    => 57.2957795,    //Degree
                                                     'RAD'    => 1),            //Radian

                                               //Speed
                                               array('C'      => 1,             //Speed Of Light
                                                     'KT'     => 582749918,     //Knot
                                                     'KPH'    => 1079252850,    //Kilometres Per Hour
                                                     'MPH'    => 670616629,     //Miles Per Hour
                                                     'MPS'    => 299792458),    //Metres Per Second

                                               //Pressure
                                               array('ATM'    => 1,             //Atmosphere
                                                     'BAR'    => 1.01325,       //Bar
                                                     'INHG'   => 29.9246899,    //Inches Of Mercury
                                                     'MB'     => 1013.25,       //Millibar
                                                     'PSI'    => 14.6959488));  //Pounds Per Square Inch

    public static function __callStatic ($strName, $arrArgs) {
      $decValue = $arrArgs[0];

      if (preg_match('/^([A-Z]+)to([A-Z]+)$/', $strName, $arrMatches)) {
        $strFrom = strtoupper($arrMatches[1]);
        $strTo = strtoupper($arrMatches[2]);

        foreach (self::$arrConversionRates as $arrConversionGroup) {
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