<?php
  namespace Wings\Lib\Util;

  class LatLongHelper {
    const MAJOR_SEMIAXIS = 6378137;
    const MINOR_SEMIAXIS = 6356752.3142;

    // This class makes use of Vincenty's formulas from http://www.movable-type.co.uk/scripts/latlong-vincenty.html

    public static function returnDistanceBetweenPoints ($decLat1, $decLong1, $decLat2, $decLong2) {
      $decFlattening = (self::MAJOR_SEMIAXIS - self::MINOR_SEMIAXIS) / self::MAJOR_SEMIAXIS;
      $radDifferenceInLongitude = deg2rad($decLong1 - $decLong2);

      $radU1 = atan((1 - $decFlattening) * tan(deg2rad($decLat1)));
      $radU2 = atan((1 - $decFlattening) * tan(deg2rad($decLat2)));

      $decSinU1 = sin($radU1);
      $decSinU2 = sin($radU2);
      $decCosU1 = cos($radU1);
      $decCosU2 = cos($radU2);

      $intIterationLimit = 100;

      $radLambda = $radDifferenceInLongitude;

      do {
        $decSinLambda = sin($radLambda);
        $decCosLambda = cos($radLambda);

        $decSinSigma = sqrt(pow($decCosU2 * $decSinLambda, 2) + pow(($decCosU1 * $decSinU2) - ($decSinU1 * $decCosU2 * $decCosLambda), 2));

        if ($decSinSigma == 0) {
          return 0;
        }//if

        $decCosSigma = ($decSinU1 * $decSinU2) + ($decCosU1 * $decCosU2 * $decCosLambda);

        $decSigma = atan2($decSinSigma, $decCosSigma);

        $decSinAlpha = ($decCosU1 * $decCosU2 * $decSinLambda) / $decSinSigma;
        $decCosSquaredAlpha = 1 - ($decSinAlpha * $decSinAlpha);

        if ($decCosSquaredAlpha == 0) {
          $decCos2SigmaM = 0;
        } else {
          $decCos2SigmaM = $decCosSigma - (2 * $decSinU1 * $decSinU2 / $decCosSquaredAlpha);
        }//if

        $decC = $decFlattening / 16 * $decCosSquaredAlpha * (4 + $decFlattening * (4 - 3 * $decCosSquaredAlpha));

        $radPreviousLambda = $radLambda;

        $radLambda = $radDifferenceInLongitude + (1 - $decC) * $decFlattening * $decSinAlpha * ($decSigma + $decC * $decSinAlpha * ($decCos2SigmaM + $decC * $decCosSigma * (-1 + 2 * $decCos2SigmaM * $decCos2SigmaM)));
      } while (abs($radLambda - $radPreviousLambda) > 1e-12 && --$intIterationLimit > 0);

      $decUSquared = $decCosSquaredAlpha * (self::MAJOR_SEMIAXIS * self::MAJOR_SEMIAXIS - self::MINOR_SEMIAXIS * self::MINOR_SEMIAXIS) / (self::MINOR_SEMIAXIS * self::MINOR_SEMIAXIS);

      $decA = 1 + $decUSquared / 16384 * (4096 + $decUSquared * (-768 + $decUSquared * (320 - 175 * $decUSquared)));
      $decB = $decUSquared / 1024 * (256 + $decUSquared * (-128 + $decUSquared * (74 - 47 * $decUSquared)));

      $decDeltaSigma = $decB * $decSinSigma * ($decCos2SigmaM + $decB / 4 * ($decCosSigma * (-1 + 2 * $decCos2SigmaM * $decCos2SigmaM) - $decB / 6 * $decCos2SigmaM * (-3 + 4 * $decSinAlpha * $decSinAlpha) * (-3 + 4 * $decCos2SigmaM * $decCos2SigmaM)));

      $decDistanceBetweenPoints = self::MINOR_SEMIAXIS * $decA * ($decSigma - $decDeltaSigma);

      return $decDistanceBetweenPoints;
    }//function

    public static function returnHeadingBetweenPoints ($decLat1, $decLong1, $decLat2, $decLong2) {
      $decFlattening = (self::MAJOR_SEMIAXIS - self::MINOR_SEMIAXIS) / self::MAJOR_SEMIAXIS;
      $radDifferenceInLongitude = deg2rad($decLong1 - $decLong2);

      $radU1 = atan((1 - $decFlattening) * tan(deg2rad($decLat1)));
      $radU2 = atan((1 - $decFlattening) * tan(deg2rad($decLat2)));

      $decSinU1 = sin($radU1);
      $decSinU2 = sin($radU2);
      $decCosU1 = cos($radU1);
      $decCosU2 = cos($radU2);

      $intIterationLimit = 100;

      $radLambda = $radDifferenceInLongitude;

      do {
        $decSinLambda = sin($radLambda);
        $decCosLambda = cos($radLambda);

        $decSinSigma = sqrt(pow($decCosU2 * $decSinLambda, 2) + pow(($decCosU1 * $decSinU2) - ($decSinU1 * $decCosU2 * $decCosLambda), 2));

        if ($decSinSigma == 0) {
          return 0;
        }//if

        $decCosSigma = ($decSinU1 * $decSinU2) + ($decCosU1 * $decCosU2 * $decCosLambda);

        $decSigma = atan2($decSinSigma, $decCosSigma);

        $decSinAlpha = ($decCosU1 * $decCosU2 * $decSinLambda) / $decSinSigma;
        $decCosSquaredAlpha = 1 - ($decSinAlpha * $decSinAlpha);

        if ($decCosSquaredAlpha == 0) {
          $decCos2SigmaM = 0;
        } else {
          $decCos2SigmaM = $decCosSigma - (2 * $decSinU1 * $decSinU2 / $decCosSquaredAlpha);
        }//if

        $decC = $decFlattening / 16 * $decCosSquaredAlpha * (4 + $decFlattening * (4 - 3 * $decCosSquaredAlpha));

        $radPreviousLambda = $radLambda;

        $radLambda = $radDifferenceInLongitude + (1 - $decC) * $decFlattening * $decSinAlpha * ($decSigma + $decC * $decSinAlpha * ($decCos2SigmaM + $decC * $decCosSigma * (-1 + 2 * $decCos2SigmaM * $decCos2SigmaM)));
      } while (abs($radLambda - $radPreviousLambda) > 1e-12 && --$intIterationLimit > 0);

      $decUSquared = $decCosSquaredAlpha * (self::MAJOR_SEMIAXIS * self::MAJOR_SEMIAXIS - self::MINOR_SEMIAXIS * self::MINOR_SEMIAXIS) / (self::MINOR_SEMIAXIS * self::MINOR_SEMIAXIS);

      $decA = 1 + $decUSquared / 16384 * (4096 + $decUSquared * (-768 + $decUSquared * (320 - 175 * $decUSquared)));
      $decB = $decUSquared / 1024 * (256 + $decUSquared * (-128 + $decUSquared * (74 - 47 * $decUSquared)));

      $decDeltaSigma = $decB * $decSinSigma * ($decCos2SigmaM + $decB / 4 * ($decCosSigma * (-1 + 2 * $decCos2SigmaM * $decCos2SigmaM) - $decB / 6 * $decCos2SigmaM * (-3 + 4 * $decSinAlpha * $decSinAlpha) * (-3 + 4 * $decCos2SigmaM * $decCos2SigmaM)));

      $decHeadingBetweenPoints = 0 - atan2($decCosU1 * $decSinLambda, -$decSinU1 * $decCosU2 + $decCosU1 * $decSinU2 * $decCosLambda);

      while ($decHeadingBetweenPoints < 0) {
        $decHeadingBetweenPoints += 2 * M_PI;
      }//while

      return rad2deg($decHeadingBetweenPoints);
    }//function

    public static function returnPointGivenHeadingAndDistance ($decLat, $decLong, $decHeading, $decDistance) {
      $decRadialDistance = LatLongHelper::returnRadialDistanceFromKM($decDistance);

      $decNewLat = rad2deg(asin(sin(deg2rad($decLat)) * cos($decRadialDistance) + cos(deg2rad($decLat)) * sin($decRadialDistance) * cos(deg2rad($decHeading))));
      $decNewLong = $decLong + rad2deg(atan2(sin(deg2rad($decHeading)) * sin($decRadialDistance) * cos(deg2rad($decLat)), cos($decRadialDistance) - sin(deg2rad($decLat)) * sin(deg2rad($decNewLat))));

      return array('lat' => $decNewLat, 'long' => $decNewLong);
    }//function

    public static function returnRadialDistanceFromKM ($decKM) {
      return Convert::DEG2RAD(Convert::KM2NM($decKM) / 60);
    }//function

    public static function returnPointOfCrossing ($decLine1Point1Lat, $decLine1Point1Long, $decLine1Point2Lat, $decLine1Point2Long, $decLine2Point1Lat, $decLine2Point1Long, $decLine2Point2Lat, $decLine2Point2Long) {
      if ($decLine1Point1Long == $decLine1Point2Long) {
        $decPointOfCrossingLong = $decLine1Point1Long;

        $decLine2Gradient = ($decLine2Point2Lat - $decLine2Point1Lat) / ($decLine2Point2Long - $decLine2Point1Long);
        $decLine2Intercept = $decLine2Point1Lat - ($decLine2Gradient * $decLine2Point1Long);

        $decPointOfCrossingLat = $decLine2Gradient * $decPointOfCrossingLong + $decLine2Intercept;

        return array($decPointOfCrossingLat, $decPointOfCrossingLong);
      }//if

      if ($decLine2Point1Long == $decLine2Point2Long) {
        $decPointOfCrossingLong = $decLine2Point1Long;

        $decLine1Gradient = ($decLine1Point2Lat - $decLine1Point1Lat) / ($decLine1Point2Long - $decLine1Point1Long);
        $decLine1Intercept = $decLine1Point1Lat - ($decLine1Gradient * $decLine1Point1Long);

        $decPointOfCrossingLat = $decLine1Gradient * $decPointOfCrossingLong + $decLine1Intercept;

        return array($decPointOfCrossingLat, $decPointOfCrossingLong);
      }//if

      $decLine1Gradient = ($decLine1Point2Lat - $decLine1Point1Lat) / ($decLine1Point2Long - $decLine1Point1Long);
      $decLine1Intercept = $decLine1Point1Lat - ($decLine1Gradient * $decLine1Point1Long);

      $decLine2Gradient = ($decLine2Point2Lat - $decLine2Point1Lat) / ($decLine2Point2Long - $decLine2Point1Long);
      $decLine2Intercept = $decLine2Point1Lat - ($decLine2Gradient * $decLine2Point1Long);

      $decPointOfCrossingLong = ($decLine2Intercept - $decLine1Intercept) / ($decLine1Gradient - $decLine2Gradient);
      $decPointOfCrossingLat = $decLine1Gradient * $decPointOfCrossingLong + $decLine1Intercept;

      return array($decPointOfCrossingLat, $decPointOfCrossingLong);
    }//function

    public static function returnRatioOfBeforeToTotal ($decBeforeLat, $decBeforeLong, $decAfterLat, $decAfterLong, $decMiddleLat, $decMiddleLong) {
      if ($decBeforeLat == $decMiddleLat && $decBeforeLong == $decMiddleLong) {
        return 0;
      }//if

      if ($decBeforeLat == $decAfterLat && $decBeforeLong == $decAfterLong) {
        return 1;
      }//if

      return self::returnDistanceBetweenPoints($decMiddleLat, $decMiddleLong, $decBeforeLat, $decBeforeLong) / self::returnDistanceBetweenPoints($decAfterLat, $decAfterLong, $decBeforeLat, $decBeforeLong);
    }//function
  }//class