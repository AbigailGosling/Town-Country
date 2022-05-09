<?php
require_once('functions.php');
require_once('ajax/customer_soa_results_function.php');
$erroronous = mysqli_query($conn,"SELECT `id` FROM `customers`");
while ($customer = mysqli_fetch_assoc($erroronous))
{   
    check_customer_outstanding_cache($customer['id']);
}
?>