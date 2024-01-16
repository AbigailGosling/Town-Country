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
$sql .= " ORDER BY `comment_logging`.`id` DESC LIMIT 1000 OFFSET ".($page*10000);
$res = prepareExecuteQuery($sql);
while ($row = mysqli_fetch_assoc($res))
{
    $date = DateTime::createFromFormat("Y-m-d H:i:s" , $row['datetime']);
    $row['body'] = stripslashes($row['body']);
    if ($row['type'] == "credit_override" || $row['type'] == "delivery_override" ) 
    {
        $customer = Customer::find($row['entity_id']);
        echo "<tr><td>$row[type]</td><td>$customer->businessname</td><td>$row[name]</td><td>$row[body]</td><td>".$date->format('d/m/Y H:i:s')."</td></tr>";
    }
    else if (strpos($row['type'],"product")===0) 
    {
        $product = Product::find($row['entity_id']);
        if ($product)
        {
            $pallet = Pallet::find($product->pallet_id);
            echo "<tr><td>$row[type]</td><td>$row[entity_id] ($pallet->intake_id)</td><td>$row[name]</td><td>$row[body]</td><td>".$date->format('d/m/Y H:i:s')."</td></tr>";
        }
        else
        {
            echo "<tr><td>$row[type]</td><td>$row[entity_id]</td><td>$row[name]</td><td>$row[body]</td><td>".$date->format('d/m/Y H:i:s')."</td></tr>";
        }
        
    }
    else {
        echo "<tr><td>$row[type]</td><td>$row[entity_id]</td><td>$row[name]</td><td>$row[body]</td><td>".$date->format('d/m/Y H:i:s')."</td></tr>";
    }
}
?>