<?php
 require_once dirname(__FILE__) . "/../app/inc.PROJECTNAME.php";

 $objFrontController = new FrontController($_GET['url']);
 $objFrontController->execute();
?>