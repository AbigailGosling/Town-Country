<?php
   	require('../functions.php');
    require('customer_soa_results_function.php');
    $adv = array_key_exists("adv",$_POST);
    echo json_encode(get_customer_soa_results($_POST['customer_id'],$adv));
?>
