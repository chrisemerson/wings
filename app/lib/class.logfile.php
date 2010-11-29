<?php
 class LogFile {
   private $strLogFileName;
   private $fleLogFile;

   public function __construct ($strLogFileName) {
     $this->strLogFileName = Application::getBasePath() . "logs/" . $strLogfileName . ".log";

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

     $strLineToLog = "[" . $strDate . " " . $strIPAddress . "]: " . $strMessage;

     fwrite($this->fleLogFile, $strLineToLog . "\n");
   }//function

   private function openLogFile () {
     $this->fleLogFile = fopen($this->strLogFileName, 'a');
   }//function

   private function closeLogFile () {
     fclose($this->fleLogFile);
   }//function
 }//class