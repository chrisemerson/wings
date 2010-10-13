<?php
 /*******************************************/
 /* BaseController Class - by Chris Emerson */
 /* http://www.cemerson.co.uk/              */
 /*                                         */
 /* Version 0.1                             */
 /* 23rd May 2009                           */
 /*******************************************/

 class BaseController {
   public $view = null;
   protected $session;

   public function __construct () {
     $this->session = new Session();
   }//function

   public function index () {
     echo "Default Text Here";
   }//function
 }//class