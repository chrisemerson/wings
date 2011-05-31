<?php
  class ErrorView extends MasterView {
    public function __construct () {
      $this->loadTemplate('system.error');

      parent::__construct();
    }//function

    public function render () {
      if (isset($this->is404) && $this->is404) {
        header('HTTP/1.0 404 Not Found');
      }//if

      if (isset($this->exception)) {
        $this->template->exceptiontype =  get_class($this->exception);

        $arrTrace = $this->exception->getTrace();

        foreach ($arrTrace as $arrTraceStep) {
          if (isset($arrTraceStep['file'])) {
            $this->template->filename = $arrTraceStep['file'];

            if (isset($arrTraceStep['line'])) {
              $this->template->lineno = $arrTraceStep['line'];
            }//if

            $this->template->parse('uncaughtexception.tracestep.file');

            unset($this->template->lineno);
          }//if

          if (isset($arrTraceStep['class'])) {
            $this->template->class = $arrTraceStep['class'];
            $this->template->type = $arrTraceStep['type'];
            $this->template->function = $arrTraceStep['function'];

            if (!empty($arrTraceStep['args'])) {
              $this->template->args = implode(", ", array_map('strval', $arrTraceStep['args']));
            }//if

            $this->template->parse('uncaughtexception.tracestep.class');

            unset($this->template->args);
          }//if

          $this->template->parse('uncaughtexception.tracestep');
        }//foreach

        $this->template->filename = $this->exception->getFile();
        $this->template->lineno = $this->exception->getLine();

        $this->template->parse('uncaughtexception');
      } else {
        $this->template->errortitle = $this->errortitle;
        $this->template->errortext = $this->errortext;

        $this->template->parse('errortext');
      }//if

      parent::render();
    }//function
  }//class