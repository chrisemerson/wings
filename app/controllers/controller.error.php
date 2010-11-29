<?php
  /* Modify the text in this class, but do not remove or rename any of the functions! */

  class ErrorController extends BaseController {
    public function Error404 () {
      $this->view = new ErrorView();

      $this->view->errortitle = "404 - Page Not Found";
      $this->view->errortext = "Page not found";

      $this->view->render();
    }//function

    public function ErrorController () {
      $this->view = new ErrorView();

      $this->view->errortitle = "Controller Not Found";
      $this->view->errortext = "Controller not found";

      $this->view->render();
    }//function

    public function ErrorAction () {
      $this->view = new ErrorView();

      $this->view->errortitle = "Action Not Found";
      $this->view->errortext = "Action not found";

      $this->view->render();
    }//function

    public function ErrorView () {
      $this->view = new ErrorView();

      $this->view->errortitle = "View Not Found";
      $this->view->errortext = "View not found";

      $this->view->render();
    }//function

    public function ErrorTemplate () {
      $this->view = new ErrorView();

      $this->view->errortitle = "Template Not Found";
      $this->view->errortext = "Template not found";

      $this->view->render();
    }//function

    public function ErrorDatabase () {
      $this->view = new ErrorView();

      $this->view->errortitle = "Database Error";
      $this->view->errortext = "Database error";

      $this->view->render();
    }//function

    public function ErrorGeneral () {
      $this->view = new ErrorView();

      $this->view->errortitle = "Error";
      $this->view->errortext = "General error";

      $this->view->render();
    }//function

    public function ErrorOffline () {
      $this->view = new ErrorView();

      $this->view->errortitle = "Site Offline";
      $this->view->errortext = "Please try again later.";

      $this->view->render();
    }//function
  }//class