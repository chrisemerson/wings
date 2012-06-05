<?php
  namespace Wings\Views\System;

  use \Wings\Lib\System\Application;

  abstract class MasterView extends \Wings\Lib\System\View {
    public function __construct () {
      $this->template->base_uri = Application::getBaseURI(Application::isSecure());
      $this->template->current_uri = Application::getCurrentPageURI();

      parent::__construct();
    }//function

    public function render () {
      parent::render();
    }//function

    protected function addStylesheet ($strStylesheetName, $strStylesheetMedia = 'screen') {
      $this->template->stylesheetname = $strStylesheetName;
      $this->template->stylesheetmedia = $strStylesheetMedia;

      $this->template->parse('stylesheet');
    }//function

    protected function addScript ($strScriptName) {
      $this->template->scriptname = $strScriptName;

      $this->template->parse('script');
    }//function
  }//class