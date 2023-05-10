<?php
require_once(__DIR__.'/../functions.php');
$date = $mysqli->real_escape_string(request()->input('date'));
$date = DateTime::createFromFormat("d/m/Y" , $date);
$date = $date->format('Y-m-d');
$id = $mysqli->real_escape_string(request()->input('id'));
$ucheck = prepareExecuteQuery("UPDATE `intake` SET `date_paid` = ? WHERE `id` = ?",'si',[$date,$id]);
?>