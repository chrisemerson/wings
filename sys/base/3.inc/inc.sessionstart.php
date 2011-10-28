<?php
  $objDBSessionsConfig = new Config('dbsessions');

  if ($objDBSessionsConfig->enabled) {
    session_set_save_handler(array('DatabaseSession', 'open'), array('DatabaseSession', 'close'), array('DatabaseSession', 'read'), array('DatabaseSession', 'write'), array('DatabaseSession', 'destroy'), array('DatabaseSession', 'gc'));
    register_shutdown_function('session_write_close');
  }//if

  unset($objDBSessionsConfig);

  session_start();