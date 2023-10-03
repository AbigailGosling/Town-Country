<?php
require_once('functions.php');
define('DEL_SUNDAY',     1);
define('DEL_SATURDAY',   2);
define('DEL_FRIDAY',     4);
define('DEL_THURSDAY',   8);
define('DEL_WEDNESDAY', 16);
define('DEL_TUESDAY',   32);
define('DEL_MONDAY',    64);

if ($test & DEL_MONDAY) echo "MONDAY,";
if ($test & DEL_TUESDAY) echo "TUESDAY,";
if ($test & DEL_WEDNESDAY) echo "WEDNESDAY,";
if ($test & DEL_THURSDAY) echo "THURSDAY,";
if ($test & DEL_FRIDAY) echo "FRIDAY,";
if ($test & DEL_SATURDAY) echo "SATURDAY,";
if ($test & DEL_SUNDAY) echo "SUNDAY";
?>
