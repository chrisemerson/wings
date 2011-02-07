<?php
  require_once dirname(__FILE__) . "/../inc.framework.php";

  $objDirectQuery = new DirectQuery();

  $strDBPath = realpath(Application::getBasePath() . "../db");
  $dirHandle = opendir($strDBPath);

  $arrDBVersions = array();
  $intMaxDBVersion = 0;

  while (false !== ($strFilename = readdir($dirHandle))) {
    if (preg_match('/^(\d+).sql$/i', $strFilename, $arrMatches)) {
      $intDBVersion = $arrMatches[1];
      $arrDBVersions[$intDBVersion] = $strFilename;

      $intMaxDBVersion = max($intDBVersion, $intMaxDBVersion);
    }//if
  }//while

  ksort($arrDBVersions);

  $objConfigSetting = new ConfigSetting('database.version');
  $intCurrentDBVersion = $objConfigSetting->config_value;

  if ($intCurrentDBVersion < $intMaxDBVersion) {
    foreach (range($intCurrentDBVersion + 1, $intMaxDBVersion) as $intDBVersion) {
      if (file_exists($strDBPath . "/" . $arrDBVersions[$intDBVersion])) {
        $strFileContents = file_get_contents($strDBPath . "/" . $arrDBVersions[$intDBVersion]);

        if ($objDirectQuery->multi_query($strFileContents)) {
          $objConfigSetting->config_value = $intDBVersion;
          $objConfigSetting->save();
        } else {
          break;
        }//if
      }//if
    }//foreach
  }//if