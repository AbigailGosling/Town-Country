<?php
include_once('../functions.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$queryArray = array();
$entity_id = mysqli_real_escape_string($conn,$_POST['entity_id']);
$type_id = mysqli_real_escape_string($conn,$_POST['type_id']);
$user_id = mysqli_real_escape_string($conn,$_POST['user_id']);
$date_start = mysqli_real_escape_string($conn,$_POST['date_start']);
$date_end = mysqli_real_escape_string($conn,$_POST['date_end']);
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
    if (isset($_POST['date_date_endstart']) && $date_end != null && $date_end != "")
    {
        $date_end = DateTime::createFromFormat("d/m/Y" , $date_end);
        $date_end = $date_end->format('Y-m-d');
        $queryArray[] = "`comment_logging`.`datetime` <= '".$date_end."'";
    }
}
$sql = "SELECT `comment_logging`.`entity_id`, 
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
$res = mysqli_query($conn,$sql);
while ($row = mysqli_fetch_assoc($res))
{
    $date = DateTime::createFromFormat("Y-m-d H:i:s" , $row['datetime']);
    echo "<tr><td>$row[type]</td><td>$row[entity_id]</td><td>$row[name]</td><td>$row[body]</td><td>".$date->format('d/m/Y H:i:s')."</td></tr>";
}
?>