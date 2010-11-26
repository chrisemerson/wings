<?php
  require_once dirname(__FILE__) . "/../app/inc.framework.php";

  $filter = new ResultFilter();

  $filter->model('post')
         ->start(10)
         ->limit(10)
         ->orderby('field', ORDER_BY_ASC);

  $objCollection = new Collection($filter);




/*
  echo "<h2>Filter Object</h2>\n";

  print_r($filter);*/