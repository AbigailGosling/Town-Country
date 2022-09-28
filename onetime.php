<?php
require_once('functions.php');
require_once('ajax/customer_soa_results_function.php');
$erroronous = mysqli_query($conn,"SELECT `id`,`override` FROM `customers`");
while ($customer = mysqli_fetch_assoc($erroronous))
{   
    mysqli_query($conn,"UPDATE `customers` SET `override` = 0, `credit_enabled` = ".$customer['override']." WHERE id = ".$customer['id']);
}
?>