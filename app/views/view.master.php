<?php
  abstract class MasterView extends BaseView {
    public function render () {
      $this->template->base_uri = Application::getBaseURI(Application::isSecure());

      parent::render();
    }//function
  }//class