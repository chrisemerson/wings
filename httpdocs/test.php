<?php
  require_once dirname(__FILE__) . "/../app/inc.framework.php";

  $filter = new ResultFilter();

  $filter->start(10)
         ->limit(10)
         ->orderby('field', ORDER_BY_ASC)
         ->conditions('test1 > 50 AND test5 < 60 OR ((test3 = 5 && test4 = 6 OR test34 = 43) AND (testq >= qwerty)) AND test23 BETWEEN 1 AND 5');
/*
  echo "<h2>Filter Object</h2>\n";

  print_r($filter);*/