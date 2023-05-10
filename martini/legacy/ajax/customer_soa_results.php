<?php
   	require(__DIR__.'/../functions.php');    
    require('customer_soa_results_function.php');
    $adv = request()->has("adv");
    echo json_encode(get_customer_soa_results(request()->input('customer_id'),$adv));
?>
