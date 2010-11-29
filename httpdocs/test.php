<?php
  require_once dirname(__FILE__) . "/../app/inc.framework.php";

  $filter = new ResultFilter();

  $objCollection = new Collection($filter->model('Test')
                                         ->start(10)
                                         ->limit(10)
                                         ->orderby('test_name', ORDER_BY_ASC));