<?php
 require_once dirname(__FILE__) . "/../app/inc.PROJECTNAME.php";
 require_once dirname(__FILE__) . "/../app/models/blogpost.php";

 $objBlogPost = new BlogPost();
 echo "<pre>";
 print_r($objBlogPost);
 echo "</pre>";
?>
