<?php
  //Load all files in the base/ folder

  $strBasePath = realpath(dirname(__FILE__) . "/base");

  loadFilesInDirectory($strBasePath);

  function loadFilesInDirectory ($strDirectory) {
    $arrFilesInBaseDir = scandir($strDirectory);

    if (file_exists($strDirectory . "/loadorder")) {
      $arrFilesToLoad = file($strDirectory . "/loadorder");

      foreach ($arrFilesToLoad as $strFilename) {
        if ($strFilename{0} != "#" && is_file($strDirectory . "/" . trim($strFilename))) {
          require_once $strDirectory . "/" . trim($strFilename);
        }//if
      }//foreach

      foreach ($arrFilesInBaseDir as $strFilename) {
        if ($strFilename{0} != '.') {
          $strFullFilename = $strDirectory . "/" . $strFilename;

          if (is_dir($strFullFilename)) {
            loadFilesInDirectory($strFullFilename);
          }//if
        }//if
      }//foreach
    } else {
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
    }//if
  }//function

  unset($strBasePath);