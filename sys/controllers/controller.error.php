<?php
  namespace Wings\Controllers;

  use \Wings\Views\System\ErrorView;

  class ErrorController extends \Wings\Lib\System\Controller {
    public function show404Error ($strErrorText = '') {
      $this->view = new ErrorView();

      $this->view->errortitle = "404 - Page Not Found";
      $this->view->is404 = true;

      if (!empty($strErrorText)) {
        $this->view->errortext = $strErrorText;
      } else {
        $this->view->errortext = "Page not found";
      }//if

      $this->renderView();
    }//function

    public function showControllerError ($strErrorText = '') {
      $this->view = new ErrorView();

      $this->view->errortitle = "Controller Not Found";

      if (!empty($strErrorText)) {
        $this->view->errortext = $strErrorText;
      } else {
        $this->view->errortext = "Controller not found";
      }//if

      $this->renderView();
    }//function

    public function showActionError ($strErrorText = '') {
      $this->view = new ErrorView();

      $this->view->errortitle = "Action Not Found";

      if (!empty($strErrorText)) {
        $this->view->errortext = $strErrorText;
      } else {
        $this->view->errortext = "Action not found";
      }//if

      $this->renderView();
    }//function

    public function showViewError ($strErrorText = '') {
      $this->view = new ErrorView();

      $this->view->errortitle = "View Not Found";

      if (!empty($strErrorText)) {
        $this->view->errortext = $strErrorText;
      } else {
        $this->view->errortext = "View not found";
      }//if

      $this->renderView();
    }//function

    public function showTemplateError ($strErrorText = '') {
      $this->view = new ErrorView();

      $this->view->errortitle = "Template Not Found";

      if (!empty($strErrorText)) {
        $this->view->errortext = $strErrorText;
      } else {
        $this->view->errortext = "Template not found";
      }//if

      $this->renderView();
    }//function

    public function showDatabaseError ($strErrorText = '') {
      $this->view = new ErrorView();

      $this->view->errortitle = "Database Error";

      if (!empty($strErrorText)) {
        $this->view->errortext = $strErrorText;
      } else {
        $this->view->errortext = "Database error";
      }//if

      $this->renderView();
    }//function

    public function showGeneralError ($strErrorText = '') {
      $this->view = new ErrorView();

      $this->view->errortitle = "Error";

      if (!empty($strErrorText)) {
        $this->view->errortext = $strErrorText;
      } else {
        $this->view->errortext = "General error";
      }//if

      $this->renderView();
    }//function

    public function showOfflineError ($strErrorText = '') {
      $this->view = new ErrorView();

      $this->view->errortitle = "Site Offline";

      if (!empty($strErrorText)) {
        $this->view->errortext = $strErrorText;
      } else {
        $this->view->errortext = "Please try again later.";
      }//if

      $this->renderView();
    }//function

    public function showExceptionError ($exException) {
      $this->view = new ErrorView();

      $this->view->exception = $exException;

      $this->renderView();
    }//function
  }//class