<?php
  require_once dirname(__FILE__) . "/../app/inc.framework.php";

  $objBlogPost = new Post();
  $objBlogPost->getComments();
?>