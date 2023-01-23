<?php
require_once('functions.php');
require_once('ajax/customer_soa_results_function.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('memory_limit','1G');
set_time_limit(3600);
$tsql = mysqli_query($conn, "UPDATE `weights` SET status_id = 0");
$x = 1;
$lastid = 0;

while($x > 0){
    $tsql = mysqli_query($conn, "SELECT `id`,`weight_ids` as `weight_ids` FROM `palletsOut` WHERE id > $x LIMIT 1");
    if (mysqli_num_rows($tsql) > 0)
    {
        $pall = mysqli_fetch_assoc($tsql);
        mysqli_query($conn, "UPDATE `weights` SET status_id = 1 WHERE id IN (".$pall['weight_ids'].")");
        $lastid = $x = $pall['id'];
    }
    else
    {
        $x = 0;
    }
    
}
echo $lastid;
?>
