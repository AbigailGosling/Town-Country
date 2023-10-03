<?php

require(__DIR__.'/../functions.php');

$id = request()->input('id');
define('DEL_SUNDAY',     1);
define('DEL_SATURDAY',   2);
define('DEL_FRIDAY',     4);
define('DEL_THURSDAY',   8);
define('DEL_WEDNESDAY', 16);
define('DEL_TUESDAY',   32);
define('DEL_MONDAY',    64);
$days = 0;
if (request()->has('mo') && request()->input('mo') == 1) $days += DEL_MONDAY;
if (request()->has('tu') && request()->input('tu') == 1) $days += DEL_TUESDAY;
if (request()->has('we') && request()->input('we') == 1) $days += DEL_WEDNESDAY;
if (request()->has('th') && request()->input('th') == 1) $days += DEL_THURSDAY;
if (request()->has('fr') && request()->input('fr') == 1) $days += DEL_FRIDAY;
if (request()->has('sa') && request()->input('sa') == 1) $days += DEL_SATURDAY;
if (request()->has('su') && request()->input('su') == 1) $days += DEL_SUNDAY;

$x = "UPDATE `customers` SET `delivery_day_checking` = NOT `delivery_day_checking`, `delivery_days` = ? WHERE `id` = ?";

$y = prepareExecuteQuery($x,'ii',[$days,$id]);


?>