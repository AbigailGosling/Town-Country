<?php
require_once('functions.php');
require_once('ajax/customer_soa_results_function.php');
$tsql = mysqli_query($conn, "SELECT `id`,`businessname` FROM `customers` ORDER BY id");
echo "[<br/>";
while($customer = mysqli_fetch_assoc($tsql)){
    $customer['outstanding'] = check_customer_outstanding_cache($customer['id'],true)['outstanding'];
    echo json_encode($customer).",<br/>";
}
?>
]