<?php
  require_once dirname(__FILE__) . "/../app/inc.framework.php";

  $filter = new ResultFilter();

  $filter->start(10)
         ->limit(10)
         ->orderby('field', ORDER_BY_ASC)
         ->conditions('(test1 > 50 AND test5 < 60) OR ((test3 = 5 && test4 = 6) AND (testq >= qwerty))');

  print_r($filter);