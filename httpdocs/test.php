<?php
 require_once dirname(__FILE__) . "/../app/inc.framework.php";
 require_once dirname(__FILE__) . "/../app/models/model.post.php";

 $objBlogPost = new Post();
 echo "<pre>";
 print_r($objBlogPost);
 echo "</pre>";
?>