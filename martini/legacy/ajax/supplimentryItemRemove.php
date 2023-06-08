<?php
include('../functions.php');
$weightid = mysqli_real_escape_string($conn,request()->input('id'));
//Weight Row
$q = mysqli_query($conn,"SELECT * FROM `tandc_live`.`weights` WHERE `id` = $weightid");
$weightrow = mysqli_fetch_assoc($q);
$q = mysqli_query($conn,"DELETE FROM `tandc_live`.`weights` WHERE `id` = $weightid");
//Product Row
$q = mysqli_query($conn,"SELECT * FROM `tandc_live`.`product` WHERE `id` =".$weightrow['product_id']);
$productrow = mysqli_fetch_assoc($q);   
mysqli_query($conn,"DELETE FROM `tandc_live`.`product` WHERE `id` =".$weightrow['product_id']);
//Cut Row
mysqli_query($conn,"DELETE FROM `tandc_live`.`cuts` WHERE `id` =".$productrow['cut_id']);

echo $weightid;
?>