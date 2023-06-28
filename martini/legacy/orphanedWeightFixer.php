<?php
require_once('functions.php');

$pickersheet_id = $mysqli->real_escape_string(request()->input('pick'));

$itemProdQ = mysqli_query($mysqli,"SELECT GROUP_CONCAT(DISTINCT `product_id`) as `ids` FROM `pickerItems` WHERE `pickersheet_id` = ".$pickersheet_id);
$product_ids = explode(",",mysqli_fetch_assoc($itemProdQ)['ids']);
if (count($product_ids) == 0) die("no products?!");
$product_ids = implode(",",$product_ids);
$orphans = array();
$pickedWeightsQ = mysqli_query($mysqli,"SELECT `id` from `weights` where `status_id` = 1 and product_id in ($product_ids)");
while ($weight = mysqli_fetch_assoc($pickedWeightsQ)) 
{
    $isOrphanCheckQ = mysqli_query($mysqli,"SELECT * from `palletsOut` where `weight_ids` LIKE '%".$weight['id']."%'");
    if (mysqli_num_rows($isOrphanCheckQ) == 0)
    {
        $orphans[] = $weight['id'];
    }
}
if (count($orphans)== 0) die("no orphans to save!");
else 
{
    mysqli_query($mysqli,"UPDATE `weights` SET `status_id` = 0 WHERE `id` IN (".implode(",",$orphans).")");
    die (count($orphans). " orphans saved!");
}
?>