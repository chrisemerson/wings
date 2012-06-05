<?php
  namespace Wings\Lib\Util;

  class LogFile {
    private $strLogName;
    private $strLogFileName;
    private $fleLogFile;

    public function __construct ($strLogName) {
      $this->strLogName = $strLogName;
      $this->strLogFileName = Application::getBasePath() . "app/logs/" . strtolower($strLogName) . ".log";

      $this->openLogFile();
    }//function

    public function __destruct () {
      $this->closeLogFile();
    }//function

    public function logMessage ($strMessage) {
      //Also log time/date & IP address
      $strDate = date('Y-m-d H:i:s');
      $strIPAddress = $_SERVER['REMOTE_ADDR'];

      //Convert all new lines, multiple spaces, tabs etc to a single space & trim
      $strMessage = trim(preg_replace('/\s+/', ' ', $strMessage));

      $strLineToLog = "[" . $strDate . "]/[" . $strIPAddress . "]: " . $strMessage;

      fwrite($this->fleLogFile, "\n" . $strLineToLog);
    }//function

    private function openLogFile () {
      if (file_exists($this->strLogFileName)) {
        $this->fleLogFile = fopen($this->strLogFileName, 'a');
      } else {
        $this->fleLogFile = fopen($this->strLogFileName, 'a');
        fwrite($this->fleLogFile, "File " . $this->strLogName . ".log created on " . date('D jS M Y \a\t H:i:s') . "\n");
      }//if
    }//function

    private function closeLogFile () {
      fclose($this->fleLogFile);
    }//function
  }//class