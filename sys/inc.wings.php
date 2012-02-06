<?php
  //Load all files in the base/ folder
  loadFilesInDirectory(realpath(dirname(__FILE__) . "/base"));
  loadFilesInDirectory(realpath(dirname(__FILE__) . "/../app/base"));

  function loadFilesInDirectory ($strDirectory) {
    $arrFilesInBaseDir = scandir($strDirectory);

    foreach ($arrFilesInBaseDir as $strFilename) {
      if ($strFilename{0} != '.') {
        $strFullFilename = $strDirectory . "/" . $strFilename;

        if (is_file($strFullFilename)) {
          require_once $strFullFilename;
        } else if (is_dir($strFullFilename)) {
          loadFilesInDirectory($strFullFilename);
        }//if
      }//if
    }//foreach
  }//function

  unset($strBasePath);