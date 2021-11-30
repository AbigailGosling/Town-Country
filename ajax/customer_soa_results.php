<?php
   	require('../functions.php');    
    require('customer_soa_results_function.php');

    echo json_encode(get_customer_soa_results($_POST['customer_id']));
?>
