<?php
require_once('functions.php');

$erroronous = mysqli_query($conn,"SELECT `id`,`credit_terms` FROM `customers` WHERE `credit_terms` REGEXP '[^0-9]' OR `credit_terms` = ''");
while ($customer = mysqli_fetch_assoc($erroronous))
{
    $credit_terms = trim($customer['credit_terms']);
    if (!is_numeric($credit_terms))
    {
        $now = time();
        $parse_attempt = strtotime($credit_terms);
        if ($parse_attempt !== false)
        {
            $datediff = $parse_attempt - $now;
            $credit_terms = "". round($datediff / (60 * 60 * 24));
        }
        else
        {
            $credit_terms = "-1";
        }
    }
    mysqli_query($conn,"UPDATE `customers` SET `credit_terms` = $credit_terms, `override` = 1 WHERE id =".$customer['id']);
}
?>