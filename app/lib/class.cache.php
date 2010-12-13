<?php
  class Cache {
    const CACHE_DIR = 'cache';

    public function save ($mixItem, $strName, $intExpiry = 0) {
      $strCacheFilename = $this->getCacheFilename($strName);

      $arrCacheContents = array('item' => $mixItem, 'expires' => $intExpiry);

      file_put_contents($strCacheFilename, serialize($arrCacheContents));

    }//function

    public function get ($strName) {
      $strCacheFilename = $this->getCacheFilename($strName);

      if (file_exists($strCacheFilename)) {
        $arrCacheContents = unserialize(file_get_contents($strCacheFilename));

        //Check expiry
        if ($arrCacheContents['expires'] == 0 || ($arrCacheContents['expires'] > date('U'))) {
          return $arrCacheContents['item'];
        }//if
      }//if

      return false;
    }//function

    private function getCacheFilename ($strCacheName) {
      return Application::getBasePath() . self::CACHE_DIR . "/" . $strCacheName . ".cache";
    }//function
  }//class