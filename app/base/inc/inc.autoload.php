<?php
 function __autoload ($strClassName) {
  $objLoader = new Loader($strClassName);
  $objLoader->load();
 }//function
?>