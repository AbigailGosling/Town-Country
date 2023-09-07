<?php
require_once('functions.php');
require_once("ajax/customer_soa_results_function.php");
echo json_encode(get_customer_soa_results(610,false));
?>
