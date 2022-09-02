<?php
require_once('functions.php');
require_once('ajax/customer_soa_results_function.php');
$erroronous = mysqli_query($conn,"SELECT `id`,`credit_terms` FROM `customers`");
while ($customer = mysqli_fetch_assoc($erroronous))
{   
    $credit_grace=$customer['credit_terms'];
    $credit_terms=$credit_grace-7;
    $due_warning =$credit_terms-7;
    mysqli_query($conn,"UPDATE `customers` SET due_warning = $due_warning, credit_terms=$credit_terms, credit_grace=$credit_grace WHERE id = ".$customer['id']);
}
?>