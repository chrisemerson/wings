<?php
  abstract class MasterView extends BaseView {
    public function __construct () {
      $this->template->base_uri = Application::getBaseURI(Application::isSecure());
      $this->template->current_uri = Application::getCurrentPageURI();

      parent::__construct();
    }//function

    public function render () {
      parent::render();
    }//function
  }//class