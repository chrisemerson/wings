<?php
  class FileUpload {
    private $conSaveType;

    private $intMaxFilesize;
    private $arrAllowedFileTypes;

    private $strSavePath;

    public function __construct () {
      $this->conSaveType = FILE_SAVE_TYPE_SEQUENTIAL;

      $this->setSavePath('data/files');
    }//function

    public function addAllowedFileType ($strFileExtension, $strFileType = '', $blnIsImage = false) {
      $arrFileType = array();

      $arrFileType['extension'] = $strFileExtension;

      if (!empty($strFileType)) {
        $arrFileType['type'] = $strFileType;
      }//if

      $arrFileType['image'] = $blnIsImage;

      $this->arrAllowedFileTypes[] = $arrFileType;
    }//function

    public function setSavePath ($strSavePath = '') {
      $this->strSavePath = realpath(Application::getBasePath() . "/app/" . $strSavePath);
    }//function

    public function setSaveType ($conSaveType = FILE_SAVE_TYPE_SEQUENTIAL) {
      $this->conSaveType = $conSaveType;
    }//function

    public function setMaxFilesize ($intMaxFilesize = 0) {
      $this->intMaxFilesize = $intMaxFilesize;
    }//function

    public function upload ($strName) {

      return $strNewFilename;
    }//function
  }//class

  define('FILE_SAVE_TYPE_SEQUENTIAL', 0);
  define('FILE_SAVE_TYPE_HASH', 1);
  define('FILE_SAVE_TYPE_OVERWRITE', 2);