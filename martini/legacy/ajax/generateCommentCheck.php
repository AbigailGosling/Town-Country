<?php

use App\Models\Customer;
use App\Models\Pallet;
use App\Models\Product;

include_once(__DIR__.'/../functions.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$queryArray = array();
$entity_id = request()->input('entity_id');
$type_id = request()->input('type_id');
$user_id = request()->input('user_id');
$date_start = request()->input('date_start');
$date_end = request()->input('date_end');
$page = (int)request()->input('page',1)-1;
if (isset($entity_id) && $entity_id != null && $entity_id != "")
    $queryArray[] = "`comment_logging`.`entity_id` = '".$entity_id."'";
if (isset($type_id) && $type_id != null && $type_id != "")
    $queryArray[] = "`comment_logging`.`type` = '".$type_id."'";
if (isset($user_id) && $user_id != null && $user_id != "")
    $queryArray[] = "`comment_logging`.`user_id` = '".$user_id."'";
if (isset($date_start) && $date_start != null && $date_start != "" && isset($date_end) && $date_end != null && $date_end != "")
{
    $date_start = DateTime::createFromFormat("d/m/Y" , $date_start);
    $date_start = $date_start->format('Y-m-d');
    $date_end = DateTime::createFromFormat("d/m/Y" , $date_end);
    $date_end = $date_end->format('Y-m-d');
    $queryArray[] = "(`comment_logging`.`datetime` BETWEEN '".$date_start."' AND '".$date_end."')";
}
else
{
    if (isset($date_start) && $date_start != null && $date_start != "")
    {
        $date_start = DateTime::createFromFormat("d/m/Y" , $date_start);
        $date_start = $date_start->format('Y-m-d');
        $queryArray[] = "`comment_logging`.`datetime` >= '".$date_start."'";
    }
    if (request()->input('date_date_endstart') !== null && $date_end != null && $date_end != "")
    {
        $date_end = DateTime::createFromFormat("d/m/Y" , $date_end);
        $date_end = $date_end->format('Y-m-d');
        $queryArray[] = "`comment_logging`.`datetime` <= '".$date_end."'";
    }
}
$sql = "SELECT `comment_logging`.`id`,
        `comment_logging`.`entity_id`,
        `comment_logging`.`type`,
        `comment_logging`.`user_id`,
        `comment_logging`.`body`,
        `comment_logging`.`datetime`,
        `users`.`name`
        FROM `comment_logging` INNER JOIN `users` ON `users`.`id` = `comment_logging`.`user_id`";

if (count($queryArray) > 0)
{
    $sql .= " WHERE ".implode(" AND ",$queryArray);
}
$sql .= " ORDER BY `comment_logging`.`id` DESC LIMIT 1000 OFFSET ".($page*1000);
$res = prepareExecuteQuery($sql);
$lastProductIDs = array();
$lastProductFound = null;
$lastProductEntry = null;
while ($row = mysqli_fetch_assoc($res))
{
    $date = DateTime::createFromFormat("Y-m-d H:i:s" , $row['datetime']);
    $row['body'] = stripslashes($row['body']);

    if (strpos($row['type'],"product")===0)
    {
        $product = Product::find($row['entity_id']);
        if ($product)
        {
            if ($lastProductFound != null && ($row['body'] != $lastProductEntry['body']))
            {
                if (count($lastProductIDs)>0)
                {
                    productExport($lastProductIDs,$lastProductEntry,$lastProductFound);
                }
            }
            $lastProductIDs[] = $row['entity_id'];
            $lastProductFound = $product;
            $lastProductEntry = $row;

        }
        else
        {
            if (count($lastProductIDs)>0)
            {
                productExport($lastProductIDs,$lastProductEntry,$lastProductFound);
            }
            echo "<tr><td title='$row[id]'>$row[type]</td><td>$row[entity_id]</td><td>$row[name]</td><td>$row[body]</td><td>".$date->format('d/m/Y H:i:s')."</td></tr>";
        }

    }
    else
    {
        if (count($lastProductIDs)>0)
        {
            productExport($lastProductIDs,$lastProductEntry,$lastProductFound);
        }
        if ($row['type'] == "customer_saleday_control" || $row['type'] == "credit_enabled" || $row['type'] == "credit_override" || $row['type'] == "delivery_day" || $row['type'] == "delivery_override" )
        {
            $customer = Customer::find($row['entity_id']);
            echo "<tr><td title='$row[id]'>$row[type]</td><td>$customer->businessname</td><td>$row[name]</td><td>$row[body]</td><td>".$date->format('d/m/Y H:i:s')."</td></tr>";
        }
        else {
            echo "<tr><td title='$row[id]'>$row[type]</td><td>$row[entity_id]</td><td>$row[name]</td><td>$row[body]</td><td>".$date->format('d/m/Y H:i:s')."</td></tr>";
        }
    }
}
if (count($lastProductIDs)>0)
{
    productExport($lastProductIDs,$lastProductEntry,$lastProductFound);
}
function productExport(array &$lastProductIDs,array &$lastProductEntry,&$lastProductFound){
    array_unique($lastProductIDs,SORT_NUMERIC);
    $additionalIDs= (count($lastProductIDs)>1)?"*":"";
    $pallet = Pallet::find($lastProductFound->pallet_id);
    $intake_id = ($pallet)?$pallet->intake_id:"";
    $date2 = DateTime::createFromFormat("Y-m-d H:i:s" , $lastProductEntry['datetime']);
    echo "<tr><td title='$lastProductEntry[id]'>$lastProductEntry[type]</td><td title='".implode(",",$lastProductIDs)."'>$lastProductIDs[0]$additionalIDs ($intake_id)</td><td>$lastProductEntry[name]</td><td>$lastProductEntry[body]</td><td>".$date2->format('d/m/Y H:i:s')."</td></tr>";
    $lastProductIDs = array();
    $lastProductEntry=null;
    $lastProductFound=null;
}
?>
