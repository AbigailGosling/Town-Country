<?php
require_once('functions.php');
$erroronous = prepareExecuteQuery("SELECT * FROM `comment_logging` WHERE `type` = 'product_weightnote'");
while ($log = mysqli_fetch_assoc($erroronous))
{   
    $t = prepareExecuteQuery("SELECT `pallet_id` FROM `product` WHERE `id` = ".$log['entity_id']);
    $pallet = mysqli_fetch_assoc($t);
    $pallet_id = $pallet['pallet_id'];
    prepareExecuteQuery("UPDATE `comment_logging` SET `type` = 'pallet',`entity_id` = '$pallet_id' WHERE `id` = ".$log['id']);
}
?>