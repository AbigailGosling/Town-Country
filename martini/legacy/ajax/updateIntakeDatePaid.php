<?php
require_once(__DIR__.'/../functions.php');
$date = request()->input('date');
$date = DateTime::createFromFormat("d/m/Y" , $date);
$date = $date->format('Y-m-d');
$id = request()->input('id');
$ucheck = prepareExecuteQuery("UPDATE `intake` SET `date_paid` = ? WHERE `id` = ?",'si',[$date,$id]);
?>