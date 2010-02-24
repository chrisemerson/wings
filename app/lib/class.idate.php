<?php
  /******************************************/
  /* Improved Date Class - by Chris Emerson */
  /* http://www.cemerson.co.uk/             */
  /*                                        */
  /* Version 0.1                            */
  /* 28th May 2009                          */
  /******************************************/

  class IDate {
    private $intYear;
    private $intMonth;
    private $intDay;
    private $intHour;
    private $intMinute;
    private $intSecond;

    public function loadCurrentDate () {
      $this->setYear(intval(date('Y')));
      $this->setMonth(intval(date('n')));
      $this->setDay(intval(date('j')));
      $this->setHour(intval(date('G')));
      $this->setMinute(intval(date('i')));
      $this->setSecond(intval(date('s')));
    }//function

    public function loadFromDBFormat ($strDBFormattedDate) {
      if (preg_match('/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/', $strDBFormattedDate, $arrMatches)) {
        $this->setYear(intval($arrMatches[1]));
        $this->setMonth(intval($arrMatches[2]));
        $this->setDay(intval($arrMatches[3]));
        $this->setHour(intval($arrMatches[4]));
        $this->setMinute(intval($arrMatches[5]));
        $this->setSecond(intval($arrMatches[6]));
      } else if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $strDBFormattedDate, $arrMatches)) {
        $this->setYear(intval($arrMatches[1]));
        $this->setMonth(intval($arrMatches[2]));
        $this->setDay(intval($arrMatches[3]));
      } else if (preg_match('/^(\d{2}):(\d{2}):(\d{2})$/', $strDBFormattedDate, $arrMatches)) {
        $this->setHour(intval($arrMatches[1]));
        $this->setMinute(intval($arrMatches[2]));
        $this->setSecond(intval($arrMatches[3]));
      }//if
    }//function

    public function loadDate ($intYear = null, $intMonth = null, $intDay = null, $intHour = null, $intMinute = null, $intSecond = null) {
      if (is_int($intYear)) {
        $this->setYear($intYear);
      }//if

      if (is_int($intMonth)) {
        $this->setMonth($intMonth);
      }//if

      if (is_int($intDay)) {
        $this->setDay($intDay);
      }//if

      if (is_int($intHour)) {
        $this->setHour($intHour);
      }//if

      if (is_int($intMinute)) {
        $this->setMinute($intMinute);
      }//if

      if (is_int($intSecond)) {
        $this->setSecond($intSecond);
      }//if
    }//function

    /*****************/
    /* Class Getters */
    /*****************/

    public function getYear () {
      return $this->intYear;
    }//function

    public function getMonth () {
      return $this->intMonth;
    }//function

    public function getDay () {
      return $this->intDay;
    }//function

    public function getHour () {
      return $this->intHour;
    }//function

    public function getMinute () {
      return $this->intMinute;
    }//function

    public function getSecond () {
      return $this->intSecond;
    }//function

    /*****************/
    /* Class Setters */
    /*****************/

    public function setYear ($intYear) {
      if (is_int($intYear)) {
        $this->intYear = $intYear;
      }//if
    }//function

    public function setMonth ($intMonth) {
      if (is_int($intMonth)) {
        $this->intMonth = $intMonth;
      }//if
    }//function

    public function setDay ($intDay) {
      if (is_int($intDay)) {
        $this->intDay = $intDay;
      }//if
    }//function

    public function setHour ($intHour) {
      if (is_int($intHour)) {
        $this->intHour = $intHour;
      }//if
    }//function

    public function setMinute ($intMinute) {
      if (is_int($intMinute)) {
        $this->intMinute = $intMinute;
      }//if
    }//function

    public function setSecond ($intSecond) {
      if (is_int($intSecond)) {
        $this->intSecond = $intSecond;
      }//if
    }//function

    /****************************/
    /* Other Functions - Public */
    /****************************/

    public function format ($strFormat) {
      $strReturnString = "";

      for ($i = 0; $i < strlen($strFormat); $i++) {
        $strCharacter = substr($strFormat, $i, 1);

        if ($strCharacter == "\\") {
          $i++;
          $strReturnString .= substr($strFormat, $i, 1);
          continue;
        }//if

        switch ($strCharacter) {
          case "d":
            $strReturnString .= $this->getDayWithLeadingZeros();
            break;

          case "D":
            $strReturnString .= $this->getShortDayOfWeekText();
            break;

          case "j":
            $strReturnString .= $this->getDayNoLeadingZeros();
            break;

          case "l":
            $strReturnString .= $this->getFullDayOfWeekText();
            break;

          case "N":
            $strReturnString .= $this->getISO8601NumericDayOfWeek();
            break;

          case "S":
            $strReturnString .= $this->getDayOrdinalSuffix();
            break;

          case "w":
            $strReturnString .= $this->getNumericDayOfWeek();
            break;

          case "z":
            $strReturnString .= $this->getDayOfYear();
            break;

          case "W":
            $strReturnString .= $this->getISO8601WeekOfYear();
            break;

          case "F":
            $strReturnString .= $this->getFullMonthText();
            break;

          case "m":
            $strReturnString .= $this->getMonthWithLeadingZeros();
            break;

          case "M":
            $strReturnString .= $this->getShortMonthText();
            break;

          case "n":
            $strReturnString .= $this->getMonthNoLeadingZeros();
            break;

          case "t":
            $strReturnString .= $this->getNumberOfDaysInMonth();
            break;

          case "L":
            $strReturnString .= $this->getLeapYearFlag();
            break;

          case "o":
            $strReturnString .= $this->getISO8601YearNumber();
            break;

          case "Y":
            $strReturnString .= $this->get4DigitYear();
            break;

          case "y":
            $strReturnString .= $this->get2DigitYear();
            break;

          case "a":
            $strReturnString .= $this->getLCAMPM();
            break;

          case "A":
            $strReturnString .= $this->getUCAMPM();
            break;

          case "B":
            $strReturnString .= $this->getSwatchInternetTime();
            break;

          case "g":
            $strReturnString .= $this->getHour12HourFormatNoLeadingZeros();
            break;

          case "G":
            $strReturnString .= $this->getHourNoLeadingZeros();
            break;

          case "h":
            $strReturnString .= $this->getHour12HourFormatWithLeadingZeros();
            break;

          case "H":
            $strReturnString .= $this->getHourWithLeadingZeros();
            break;

          case "i":
            $strReturnString .= $this->getMinuteWithLeadingZeros();
            break;

          case "s":
            $strReturnString .= $this->getSecondWithLeadingZeros();
            break;

          case "c":
            $strReturnString .= $this->getISO8601Date();
            break;

          case "r":
            $strReturnString .= $this->getRFC2822Date();
            break;

          case "U":
            $strReturnString .= $this->getUnixTimestamp();
            break;

          default:
            $strReturnString .= $strCharacter;
            break;
        }//switch
      }//for

      return $strReturnString;
    }//function

    public function isValid () {
      if ($this->getMonth() < 1 || $this->getMonth() > 12) {
        return false;
      }//if

      if ($this->getDay() < 1 || $this->getDay() > $this->getNumberOfDaysInMonth()) {
        return false;
      }//if

      if (($this->getHour() !== null) && ($this->getHour() < 0 || $this->getHour() > 23)) {
        return false;
      }//if

      if (($this->getMinute() !== null) && ($this->getMinute() < 0 || $this->getMinute() > 59)) {
        return false;
      }//if

      if (($this->getSecond() !== null) && ($this->getSecond() < 0 || $this->getSecond() > 59)) {
        return false;
      }//if

      return true;
    }//function

    public function isInPast () {
      $strThisClass = __CLASS__;
      $objToday = new $strThisClass();
      $objToday->loadCurrentDate();

      return ($this->format('YmdHis') < $objToday->format('YmdHis'));
    }//function

    public function addSeconds ($intSecondsToAdd) {
      $this->intSecond += $intSecondsToAdd;
      $this->resolveOverflow();
    }//function

    public function addMinutes ($intMinutesToAdd) {
      $this->intMinute += $intMinutesToAdd;
      $this->resolveOverflow();
    }//function

    public function addHours ($intHoursToAdd) {
      $this->intHour += $intHoursToAdd;
      $this->resolveOverflow();
    }//function

    public function addDays ($intDaysToAdd) {
      $this->intDay += $intDaysToAdd;
      $this->resolveOverflow();
    }//function

    public function addMonths ($intMonthsToAdd) {
      $this->intMonth += $intMonthsToAdd;
      $this->resolveOverflow();
    }//function

    public function addYears ($intYearsToAdd) {
      $this->intYear += $intYearsToAdd;
      $this->resolveOverflow();
    }//function

    public function addTimePeriod ($intYears, $intMonths, $intDays, $intHours, $intMinutes, $intSeconds) {
      $this->addSeconds($intSeconds);
      $this->addMinutes($intMinutes);
      $this->addHours($intHours);
      $this->addDays($intDays);
      $this->addMonths($intMonths);
      $this->addYears($intYears);
    }//function

    /***************************************************/
    /* Other Functions - Private - For Date Formatting */
    /***************************************************/

    private function getDayWithLeadingZeros () {
      return $this->addLeadingZeros($this->getDay(), 2);
    }//function

    private function getShortDayOfWeekText () {
      $intNumericDayOfWeek = $this->getNumericDayOfWeek();

      switch ($intNumericDayOfWeek) {
        case 0:
          return "Sun";
          break;

        case 1:
          return "Mon";
          break;

        case 2:
          return "Tue";
          break;

        case 3:
          return "Wed";
          break;

        case 4:
          return "Thu";
          break;

        case 5:
          return "Fri";
          break;

        case 6:
          return "Sat";
          break;
      }//switch
    }//function

    private function getDayNoLeadingZeros () {
      return $this->stripLeadingZeros($this->getDay());
    }//function

    private function getFullDayOfWeekText () {
      $intNumericDayOfWeek = $this->getNumericDayOfWeek();

      switch ($intNumericDayOfWeek) {
        case 0:
          return "Sunday";
          break;

        case 1:
          return "Monday";
          break;

        case 2:
          return "Tuesday";
          break;

        case 3:
          return "Wednesday";
          break;

        case 4:
          return "Thursday";
          break;

        case 5:
          return "Friday";
          break;

        case 6:
          return "Saturday";
          break;
      }//switch
    }//function

    private function getISO8601NumericDayOfWeek () {
      $intNumericDayOfWeek = $this->getNumericDayOfWeek();

      if ($intNumericDayOfWeek == 0) {
        return 7;
      } else {
        return $intNumericDayOfWeek;
      }//if
    }//function

    private function getDayOrdinalSuffix () {
      $intDay = $this->getDayNoLeadingZeros();

      switch ($intDay) {
        case 1:
        case 21:
        case 31:
          return "st";
          break;

        case 2:
        case 22:
          return "nd";
          break;

        case 3:
        case 23:
          return "rd";
          break;

        default:
          return "th";
          break;
      }//switch
    }//function

    private function getNumericDayOfWeek () {
      $intFullYear = $this->addLeadingZeros($this->getYear(), 4);

      $intYear = substr($intFullYear, 2, 2);
      $intCentury = substr($intFullYear, 0, 2);
      $intMonth = $this->getMonth() - 2;
      $intDay = $this->getDay();

      if ($intMonth <= 0) {
        $intMonth += 12;
        $intYear--;

        if ($intYear < 0) {
          $intYear += 100;
        }//if

        if ($intYear == 99) {
          $intCentury--;
        }//if
      }//if

      $intDayOfWeek = ($intDay + floor((($intMonth * 13) - 1) / 5) + $intYear + floor($intYear / 4) + floor($intCentury / 4) - ($intCentury * 2)) % 7;

      while ($intDayOfWeek < 0) {
        $intDayOfWeek += 7;
      }//while

      return $intDayOfWeek;
    }//function

    private function getDayOfYear () {
      $intMonth = $this->getMonthNoLeadingZeros();
      $intDay = $this->getDayNoLeadingZeros();

      $intDayOfYear = $intDay;

      if (!$this->isLeapYear() || $intMonth <= 2) {
        $intDayOfYear--;
      }//if

      switch ($intMonth) {
        case 12:
          $intDayOfYear += 30;

        case 11:
          $intDayOfYear += 31;

        case 10:
          $intDayOfYear += 30;

        case 9:
          $intDayOfYear += 31;

        case 8:
          $intDayOfYear += 31;

        case 7:
          $intDayOfYear += 30;

        case 6:
          $intDayOfYear += 31;

        case 5:
          $intDayOfYear += 30;

        case 4:
          $intDayOfYear += 31;

        case 3:
          $intDayOfYear += 28;

        case 2:
          $intDayOfYear += 31;
      }//switch

      return $intDayOfYear;
    }//function

    private function getISO8601WeekOfYear () {
      list ($intISOYear, $intISOWeekNumber) = $this->getISO8601Information();

      return $this->addLeadingZeros($intISOWeekNumber, 2);
    }//function

    private function getFullMonthText () {
      $intNumericMonth = $this->getMonthNoLeadingZeros();

      switch ($intNumericMonth) {
        case 1:
          return "January";
          break;

        case 2:
          return "February";
          break;

        case 3:
          return "March";
          break;

        case 4:
          return "April";
          break;

        case 5:
          return "May";
          break;

        case 6:
          return "June";
          break;

        case 7:
          return "July";
          break;

        case 8:
          return "August";
          break;

        case 9:
          return "September";
          break;

        case 10:
          return "October";
          break;

        case 11:
          return "November";
          break;

        case 12:
          return "December";
          break;
      }//switch
    }//function

    private function getMonthWithLeadingZeros () {
      return $this->addLeadingZeros($this->getMonth(), 2);
    }//function

    private function getShortMonthText () {
      $intNumericMonth = $this->getMonthNoLeadingZeros();

      switch ($intNumericMonth) {
        case 1:
          return "Jan";
          break;

        case 2:
          return "Feb";
          break;

        case 3:
          return "Mar";
          break;

        case 4:
          return "Apr";
          break;

        case 5:
          return "May";
          break;

        case 6:
          return "Jun";
          break;

        case 7:
          return "Jul";
          break;

        case 8:
          return "Aug";
          break;

        case 9:
          return "Sep";
          break;

        case 10:
          return "Oct";
          break;

        case 11:
          return "Nov";
          break;

        case 12:
          return "Dec";
          break;
      }//switch
    }//function

    private function getMonthNoLeadingZeros () {
      return $this->stripLeadingZeros($this->getMonth());
    }//function

    private function getNumberOfDaysInMonth () {
      $intMonth = $this->getMonthNoLeadingZeros();

      switch ($intMonth) {
        case 2:
          if ($this->isLeapYear()) {
            return 29;
          } else {
            return 28;
          }//if
          break;

        case 4:
        case 6:
        case 9:
        case 11:
          return 30;
          break;

        default:
          return 31;
          break;
      }//switch
    }//function

    private function getLeapYearFlag () {
      if ($this->isLeapYear()) {
        return 1;
      } else {
        return 0;
      }//if
    }//function

    private function getISO8601YearNumber () {
      list ($intISOYear, $intISOWeekNumber) = $this->getISO8601Information();

      return $intISOYear;
    }//function

    private function get4DigitYear () {
      return $this->getYear();
    }//function

    private function get2DigitYear () {
      $intYear = $this->getYear();
      return $this->addLeadingZeros(($intYear % 100), 2);
    }//function

    private function getLCAMPM () {
      if ($this->getHourNoLeadingZeros() < 12) {
        return "am";
      } else {
        return "pm";
      }//if
    }//function

    private function getUCAMPM () {
      return strtoupper($this->getLCAMPM());
    }//function

    private function getSwatchInternetTime () {
      $intHour = ($this->getHour() + 1) % 24;
      $intMinute = $this->getMinute();
      $intSecond = $this->getSecond();

      $intSecondsAfterMidnight = ($intHour * 3600) + ($intMinute * 60) + $intSecond;
      return $this->addLeadingZeros(floor($intSecondsAfterMidnight / 86.4), 3);
    }//function

    private function getHour12HourFormatNoLeadingZeros () {
      return $this->stripLeadingZeros($this->getHour12HourFormatWithLeadingZeros());
    }//function

    private function getHourNoLeadingZeros () {
      if ($this->getHour() == 0) {
        return "0";
      }//if

      return $this->stripLeadingZeros($this->getHour());
    }//function

    private function getHour12HourFormatWithLeadingZeros () {
      $intHour = intval($this->getHour());

      if ($intHour == 0) {
        $intHour = 12;
      }//if

      if ($intHour > 12) {
        $intHour -= 12;
      }//if

      return $this->addLeadingZeros($intHour, 2);
    }//function

    private function getHourWithLeadingZeros () {
      return $this->addLeadingZeros($this->getHour(), 2);
    }//function

    private function getMinuteWithLeadingZeros () {
      return $this->addLeadingZeros($this->getMinute(), 2);
    }//function

    private function getSecondWithLeadingZeros () {
      return $this->addLeadingZeros($this->getSecond(), 2);
    }//function

    private function getISO8601Date () {
      return $this->format("Y-m-d\\TH:i:s") . "+00:00";
    }//function

    private function getRFC2822Date () {
      return $this->format("D, d M Y H:i:s") . " +0000";
    }//function

    private function getUnixTimestamp () {
      return mktime($this->getHour(), $this->getMinute(), $this->getSecond(), $this->getMonth(), $this->getDay(), $this->getYear());
    }//function

    /***********************************/
    /* Other Functions - Other Private */
    /***********************************/

    private function isLeapYear() {
      $intYear = $this->getYear();

      if (($intYear % 400) == 0) {
        return true;
      } else if (($intYear % 100) == 0) {
        return false;
      } else if (($intYear % 4) == 0) {
        return true;
      } else {
        return false;
      }//if
    }//function

    private function getISO8601Information () {
      $strThisClass = __CLASS__;

      $intISOYear = $this->getYear();

      $dteFourthOfJanThisYear = new $strThisClass();
      $dteFourthOfJanThisYear->loadDate($intISOYear, 1, 4);
      $intFourthOfJanThisYearDayOfWeek = intval($dteFourthOfJanThisYear->format('N'));

      $intDaysAfterISOWeekOneMonday = $this->getDayOfYear() - 4 + $intFourthOfJanThisYearDayOfWeek;

      $intISOWeekNumber = floor($intDaysAfterISOWeekOneMonday / 7) + 1;

      //Edge case: Day is before week one Monday, and belongs to Week 52/53 of LAST year
      $intDayNoOfISOWeekOneMonday = 4 - $intFourthOfJanThisYearDayOfWeek;

      if ($this->getDayOfYear() < $intDayNoOfISOWeekOneMonday) {
        $intISOYear--;

        //Twenty-Eighth of December is never an edge case, so no danger of infinite loop here
        $objTwentyEighthOfDecemberLastYear = new $strThisClass();
        $objTwentyEighthOfDecemberLastYear->loadDate($intISOYear, 12, 28);
        $intISOWeekNumber = intval($objTwentyEighthOfDecemberLastYear->format('W'));
      }//if

      //Edge case: Day is after the last week of the year, and belongs to Week 1 of NEXT year
      $intDayNoOfISOWeekOneMondayNextYear = 368 - (($intFourthOfJanThisYearDayOfWeek + $this->getLeapYearFlag()) % 7) + $this->getLeapYearFlag();

      if ($this->getDayOfYear() >= $intDayNoOfISOWeekOneMondayNextYear) {
        $intISOWeekNumber = 1;
        $intISOYear++;
      }//if

      return array($intISOYear, $intISOWeekNumber);
    }//function

    private function addLeadingZeros ($strInput, $intLength) {
      return str_pad($strInput, $intLength, "0", STR_PAD_LEFT);
    }//function

    private function stripLeadingZeros ($strInput) {
      return ltrim($strInput, '0');
    }//function

    private function resolveOverflow () {
      //Resolve Seconds overflow first, and work up

      while ($this->intSecond > 59) {
        $this->intSecond -= 60;
        $this->intMinute += 1;
      }//while

      while ($this->intSecond < 0) {
        $this->intSecond += 60;
        $this->intMinute -= 1;
      }//while

      //Minutes

      while ($this->intMinute > 59) {
        $this->intMinute -= 60;
        $this->intHour += 1;
      }//while

      while ($this->intMinute < 0) {
        $this->intMinute += 60;
        $this->intHour -= 1;
      }//while

      //Hours

      while ($this->intHour > 24) {
        $this->intHour -= 24;
        $this->intDay += 1;
      }//while

      while ($this->intHour < 0) {
        $this->intHour += 24;
        $this->intDay -= 1;
      }//while

      //Days & Months - have to do both at once to make sure leap years are accounted for

      while ($this->intDay > $this->getNumberOfDaysInMonth()) {
        $this->intDay -= $this->getNumberOfDaysInMonth();
        $this->intMonth += 1;

        if ($this->intMonth > 12) {
          $this->intMonth -= 12;
          $this->intYear += 1;
        }//if
      }//while

      while ($this->intDay < 1) {
        $this->intMonth -= 1;
        $this->intDay += $this->getNumberOfDaysInMonth();

        if ($this->intMonth < 1) {
          $this->intMonth += 12;
          $this->intYear -= 1;
        }//if
      }//while

      //Handle Months separately as well - in case Days is already in range

      while ($this->intMonth > 12) {
        $this->intMonth -= 12;
        $this->intYear += 1;
      }//while

      while ($this->intMonth < 1) {
        $this->intMonth += 12;
        $this->intYear -= 1;
      }//while

      //We've changed years, so could have changed from a leap year to not a leap year

      if ($this->intMonth == 2 && $this->intDay == 29 && !$this->isLeapYear()) {
        $this->intMonth = 3;
        $this->intDay = 1;
      }//if
    }//function
  }//class
?>