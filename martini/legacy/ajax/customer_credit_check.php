<?php
   	require_once(__DIR__.'/../functions.php');
    require_once(__DIR__.'/customer_soa_results_function.php');
    echo json_encode(precredit_check(request()->input('customer_id')))??"";
?>
