<?php
require_once('../functions.php');
$date = mysqli_real_escape_string($conn,$_POST['date']);
$date = DateTime::createFromFormat("d/m/Y" , $date);
$date = $date->format('Y-m-d');
$id = mysqli_real_escape_string($conn,$_POST['id']);
$ucheck = mysqli_query($conn, "UPDATE `intake` SET `date_paid` = '$date' WHERE `id` = $id");
?>