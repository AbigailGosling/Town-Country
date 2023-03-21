<?php
require_once('functions.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$q1 = mysqli_query($conn,"SELECT * FROM `weights` WHERE `product_id` = 89083 and weight_gross = '11.699'");
$r1 = mysqli_fetch_assoc($q1);

$q2 = mysqli_query($conn,"SELECT `weight_ids` FROM `palletsOut` WHERE `pickersheet_id` = 37198");
$r2 = mysqli_fetch_assoc($q2)['weight_ids'];
$r2 = explode(",",$r2);
$r2[]=$r1['id'];
$r2 = implode(",",$r2);
$q2 = mysqli_query($conn,"UPDATE `palletsOut` SET `weight_ids` = '".$r2."' WHERE `pickersheet_id` = 37198");
$q3 = mysqli_query($conn,"INSERT INTO `tandc_live`.`pickerItems` (`pickersheet_id`, `product_id`, `price`, `price_type`, `status`, `comment`, `deleted`, `target_weight`) VALUES (37198, 89083, 18.75, '', '1', 'TANG INSERT', '0', '0')");

$q4 = mysqli_query($conn,"SELECT * FROM `weights` WHERE `product_id` = 89083 and weight_gross <> '11.699'");
$r4 = mysqli_fetch_all($q4, MYSQLI_ASSOC);

$q5 = mysqli_query($conn,"SELECT `weight_ids` FROM `palletsOut` WHERE `pickersheet_id` = 37402");
$r5 = mysqli_fetch_assoc($q5)['weight_ids'];
$r5 = explode(",",$r5);

foreach($r4 as $row){
    $r5[]=$row['id'];
}
$q6 = mysqli_query($conn,"INSERT INTO `tandc_live`.`pickerItems` (`pickersheet_id`, `product_id`, `price`, `price_type`, `status`, `comment`, `deleted`, `target_weight`) VALUES (37402, 89083, 21.50, '', '1', 'TANG INSERT', '0', '0')");
$r5 = implode(",",$r5);
$q7 = mysqli_query($conn,"UPDATE `palletsOut` SET `weight_ids` = '".$r5."' WHERE `pickersheet_id` = 37402");
?>
