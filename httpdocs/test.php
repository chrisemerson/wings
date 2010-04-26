<?php
  require_once dirname(__FILE__) . "/../app/inc.framework.php";

  try {
    $objPosts = new Collection('Post');
    $objPosts->get(array('available' => 1));

    foreach ($objPosts->getMembers() as $objPost) {
      echo $objPost->post_title . "\n";
    }//foreach
  } catch (Exception $exException) {
    showException($exException);
  }//try
?>