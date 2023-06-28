<?php
include('../functions.php');
$weightid = $mysqli->real_escape_string(request()->input('id'));
//Weight Row
$q = mysqli_query($mysqli,"SELECT * FROM `tandc_live`.`weights` WHERE `id` = $weightid");
$weightrow = mysqli_fetch_assoc($q);
$q = mysqli_query($mysqli,"DELETE FROM `tandc_live`.`weights` WHERE `id` = $weightid");
//Product Row
$q = mysqli_query($mysqli,"SELECT * FROM `tandc_live`.`product` WHERE `id` =".$weightrow['product_id']);
$productrow = mysqli_fetch_assoc($q);   
mysqli_query($mysqli,"DELETE FROM `tandc_live`.`product` WHERE `id` =".$weightrow['product_id']);
//Cut Row
mysqli_query($mysqli,"DELETE FROM `tandc_live`.`cuts` WHERE `id` =".$productrow['cut_id']);

echo $weightid;
?>