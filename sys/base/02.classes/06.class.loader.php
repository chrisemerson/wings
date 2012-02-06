<?php
  class Loader {
    private $strClassName;
    private $objModelRegistry;

    public function __construct ($strClassName) {
      $this->strClassName = $strClassName;
      $this->objModelRegistry = new ModelRegistry();
    }//function

    public function load () {
      //Load Order/Priority: DB Drivers, Controllers, Views, Collections, Models, Lib, Third Party

      if (strtolower(substr($this->strClassName, -6)) == 'driver') {
        $strModuleName = strtolower(substr($this->strClassName, 0, -6));

        if (self::isDBDriver($strModuleName)) {
          self::loadDBDriver($strModuleName);
          return true;
        }//if
      } else if (strtolower(substr($this->strClassName, -10)) == 'controller') {
        $strModuleName = strtolower(substr($this->strClassName, 0, -10));

        if (self::isController($strModuleName)) {
          self::loadController($strModuleName);
          return true;
        } else {
          Application::showError('controller');
        }//if
      } else if (strtolower(substr($this->strClassName, -4)) == 'view') {
        $strModuleName = strtolower(substr($this->strClassName, 0, -4));

        if (self::isView($strModuleName)) {
          self::loadView($strModuleName);
          return true;
        } else {
          Application::showError('view');
        }//if
      } else if (strtolower(substr($this->strClassName, -10)) == 'collection') {
        $strModuleName = substr($this->strClassName, 0, -10);

        if (self::isModel(strtolower($strModuleName)) || $this->objModelRegistry->isModel($strModuleName)) {
          eval('class ' . $this->strClassName . ' extends Collection { public function __construct ($strFilterOptions = \'\') { parent::__construct(\'' . $strModuleName . '\', $strFilterOptions); }}');
        } else {
          return false;
        }//if
      } else {
        $strModuleName = strtolower($this->strClassName);

        if (self::isModel($strModuleName)) {
          self::loadModel($strModuleName);
        } else if ($this->objModelRegistry->isModel($this->strClassName)) {
          eval('class ' . $this->strClassName . ' extends BaseModel {}');

          return true;
        } else if (self::isLibrary($strModuleName)) {
          self::loadLibrary($strModuleName);
        } else if (self::isThirdParty($strModuleName)) {
          self::loadThirdParty($strModuleName);
        } else {
          Application::showError('general');
          return false;
        }//if
      }//if
    }//function

    /* Module Exists Checkers */

    private static function isDBDriver ($strName) {
      return file_exists(self::getDBDriverFilename($strName));
    }//function

    private static function isController ($strName) {
      return file_exists(self::getControllerFilename($strName));
    }//function

    private static function isView ($strName) {
      return file_exists(self::getViewFilename($strName));
    }//function

    private static function isModel ($strName) {
      return file_exists(self::getModelFilename($strName));
    }//function

    private static function isLibrary ($strName) {
      return file_exists(self::getLibraryFilename($strName));
    }//function

    private static function isThirdParty ($strName) {
      try {
        return file_exists(self::getThirdPartyFilename($strName));
      } catch (ConfigSettingNotFoundException $exNotFound) {
        return false;
      }//try
    }//function

    /* Module Loaders */

    private static function loadDBDriver ($strName) {
      require_once self::getDBDriverFilename($strName);
    }//function

    private static function loadController ($strName) {
      require_once self::getControllerFilename($strName);
    }//function

    private static function loadView ($strName) {
      require_once self::getViewFilename($strName);
    }//function

    private static function loadModel ($strName) {
      require_once self::getModelFilename($strName);
    }//function

    private static function loadLibrary ($strName) {
      require_once self::getLibraryFilename($strName);
    }//function

    private static function loadThirdParty ($strName) {
      require_once self::getThirdPartyFilename($strName);
    }//function

    /* Module Filenames */

    private static function getDBDriverFilename ($strName) {
      return Application::getAppFilename("dbdrivers/db." . $strName . ".php");
    }//function

    private static function getControllerFilename ($strName) {
      return Application::getAppFilename("controllers/controller." . $strName . ".php");
    }//function

    private static function getViewFilename ($strName) {
      return Application::getAppFilename("views/view." . $strName . ".php");
    }//function

    private static function getModelFilename ($strName) {
      return Application::getAppFilename("models/model." . $strName . ".php");
    }//function

    private static function getLibraryFilename ($strName) {
      return Application::getAppFilename("lib/class." . $strName . ".php");
    }//function

    private static function getThirdPartyFilename ($strName) {
      $objThirdPartyConfig = new Config('thirdparty');

      return Application::getAppFilename("lib/thirdparty/" . $objThirdPartyConfig->$strName);
    }//function
  }//class