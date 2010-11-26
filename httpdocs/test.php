<?php
  require_once dirname(__FILE__) . "/../app/inc.framework.php";

  $filter = new ResultFilter();

  $objCollection = new Collection($filter->model('post')
                                         ->start(10)
                                         ->limit(10)
                                         ->orderby('field', ORDER_BY_ASC));