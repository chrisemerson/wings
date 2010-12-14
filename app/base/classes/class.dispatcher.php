<?php
  class Dispatcher {
    private $strURI;

    private $arrRoutes;
    private $arrIndexAction;
    private $arrNotFoundAction;

    public function __construct ($strURI = '') {
      $this->strURI = trim($strURI, '/ ');
      $this->loadRoutes();
    }//function

    public function execute () {
      if (empty($this->strURI)) {
        //Home (Index) Page
        $this->dispatchRoute($this->arrIndexAction);
      } else {
        foreach ($this->arrRoutes as $arrRoute) {
          $strRouteMatch = '/^' . str_replace('/', '\/', $arrRoute['match']) . '$/';

          $strRouteMatch = str_replace('{string}', '([^\/]+)', $strRouteMatch);
          $strRouteMatch = str_replace('{int}', '([0-9]+)', $strRouteMatch);
          $strRouteMatch = str_replace('{integer}', '([0-9]+)', $strRouteMatch);

          if (preg_match($strRouteMatch, $this->strURI, $arrMatches)) {
            //Check if the secure setting is correct, redirect if not
            if (isset($arrRoute['execute']['secure'])) {
              if ($arrRoute['execute']['secure'] == 'yes') {
                $blnSecure = true;
              } else if ($arrRoute['execute']['secure'] == 'no') {
                $blnSecure = false;
              }//if

              if ($blnSecure != Application::isSecure()) {
                Application::redirect(Application::getBaseURI($blnSecure) . $this->strURI . '/');
              }//if
            }//if

            if (isset($arrRoute['execute']['params'])) {
              $arrParams = $arrRoute['execute']['params'];

              foreach ($arrParams as $intParamIndex => $strParam) {
                if (preg_match('/%([0-9]+)/', $strParam, $arrParamMatches)) {
                  $arrParams[$intParamIndex] = $arrMatches[$arrParamMatches[1]];
                }//if
              }//foreach

              $arrRoute['execute']['params'] = $arrParams;
            }//if

            $this->dispatchRoute($arrRoute);
            return true;
          }//if
        }//foreach

        //404 (Not Found)
        header ('HTTP/1.1 404 Not Found');
        $this->dispatchRoute($this->arrNotFoundAction);
        return false;
      }//if
    }//function

    private function loadRoutes () {
      $strRoutesFilename = Application::getBasePath() . "config/routes.xml";
      $objRoutesFile = simplexml_load_file($strRoutesFilename);

      if (!isset($objRoutesFile->index)) {
        throw new NoIndexRouteDefinedException;
      }//if

      $this->arrIndexAction = $this->parseRoute($objRoutesFile->index);

      if (!isset($objRoutesFile->notfound)) {
        throw new NoNotFoundActionDefinedException;
      }//if

      $this->arrNotFoundAction = $this->parseRoute($objRoutesFile->notfound);

      if (count($objRoutesFile->route) > 0) {
        foreach ($objRoutesFile->route as $objRoute) {
          $this->arrRoutes[] = $this->parseRoute($objRoute);
        }//foreach
      } else {
        $this->arrRoutes = array();
      }//if
    }//function

    private function parseRoute ($objRoute) {
      $arrReturnArray = array();

      if (isset($objRoute['match'])) {
        $arrReturnArray['match'] = trim((string) $objRoute['match'], '/ ');
      }//if

      if (isset($objRoute->redirect)) {
        $arrRedirectArray = array();

        if (isset($objRoute->redirect['uri'])) {
          $arrRedirectArray['uri'] = trim((string) $objRoute->redirect['uri']);
        }//if

        if (isset($objRoute->redirect['route'])) {
          $arrRedirectArray['route'] = trim((string) $objRoute->redirect['route'], '/ ');
        }//if

        if (isset($objRoute->redirect['secure'])) {
          $arrRedirectArray['secure'] = trim((string) $objRoute->redirect['secure']);
        }//if

        $arrReturnArray['redirect'] = $arrRedirectArray;
      } else if (isset($objRoute->execute)) {
        $arrExecuteArray = array();

        if (!isset($objRoute->execute['controller'])) {
          throw new NoControllerSetException;
        }//if

        $arrExecuteArray['controller'] = trim((string) $objRoute->execute['controller']);

        if (isset($objRoute->execute['action'])) {
          $arrExecuteArray['action'] = trim((string) $objRoute->execute['action']);
        }//if

        if (isset($objRoute->execute['secure'])) {
          $arrExecuteArray['secure'] = trim((string) $objRoute->execute['secure']);
        }//if

        $arrReturnArray['execute'] = $arrExecuteArray;

        if (count($objRoute->param) > 0) {
          $arrReturnArray['execute']['params'] = array();

          foreach ($objRoute->param as $objParam) {
            $arrReturnArray['execute']['params'][] = trim((string) $objParam);
          }//foreach
        }//if
      } else {
        throw new NoDispatchMethodDefinedException;
      }//if

      return $arrReturnArray;
    }//function

    private function dispatchRoute ($arrAction) {
      if (isset($arrAction['redirect'])) {
        //Redirect - either by URI (priority) or alternate route in this app

        if (isset($arrAction['redirect']['uri'])) {
          $strRedirectURI = $arrAction['redirect']['uri'];
        } else if (isset($arrAction['redirect']['route'])) {
          $blnSecure = Application::isSecure();

          if (isset($arrAction['redirect']['secure'])) {
            if ($arrAction['redirect']['secure'] == 'yes') {
              $blnSecure = true;
            } else if ($arrAction['redirect']['secure'] == 'no') {
              $blnSecure = false;
            }//if
          }//if

          $strRedirectURI = Application::getBaseURI($blnSecure) . $arrAction['redirect']['route'];

          //Could be redirecting to a file, so do basic checks to make sure before adding the slash - saves a lot of unnecessary redirects
          if (!file_exists(Application::getBasePath() . $arrAction['redirect']['route']) && !preg_match('|[^/]+\.[^/]+$|', $arrAction['redirect']['route'])) {
            $strRedirectURI .= '/';
          }//if
        } else {
          throw new NoRedirectLocationSpecifiedException;
        }//if

        Application::redirect($strRedirectURI);
      } else if (isset($arrAction['execute'])) {
        //Execute - combination of Controller, Action and Params
        $strController = $arrAction['execute']['controller'];

        if (isset($arrAction['execute']['action'])) {
          $strAction = $arrAction['execute']['action'];
        } else {
          $strAction = 'index';
        }//if

        if (isset($arrAction['execute']['params'])) {
          $arrParams = $arrAction['execute']['params'];
        } else {
          $arrParams = array();
        }//if

        $this->executeAction($strController, $strAction, $arrParams);
      }//if
    }//function

    private function executeAction ($strController, $strAction = 'index', $arrParams = array()) {
      $objController = new $strController;

      if (is_callable(array($objController, $strAction))) {
        call_user_func_array(array($objController, $strAction), $arrParams);
      } else {
        Application::showError('action');
      }//if
    }//function
  }//class

  class NoIndexRouteDefinedException extends Exception {}
  class NoNotFoundActionDefinedException extends Exception {}
  class NoDispatchMethodDefinedException extends Exception {}
  class NoControllerSetException extends Exception {}
  class NoRedirectLocationSpecifiedException extends Exception {}