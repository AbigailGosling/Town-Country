<?php
include('../functions.php');
$name = mysqli_real_escape_string($conn,$_POST['name']);
$cost = mysqli_real_escape_string($conn,$_POST['cost']);
//Cut Row
mysqli_query($conn,"INSERT INTO `tandc_live`.`cuts` ( `species_id`, `name`, `cutgroup_id`) VALUES (-1, ".$name.", -1)");
$cutid = mysqli_insert_id($conn);
//Product Row
mysqli_query($conn,"INSERT INTO `tandc_live`.`product` (`pallet_id`, `cut_id`, `brand_id`, `nationality_id`, `cooling_id`, `status`, `range_from`, `range_to`, `ubbb`, `unit`, `comments`, `best_by`, `pricetype`, `cost`, `price`, `box_id`, `weightnote`, `product_temp`, `original_intake_id`, `original_pallet_id`, `note_units`, `note_weight`, `akg`, `quantity`) 
                                                VALUES (-1, $cutid, -1, -1, -1, 1, NULL, NULL, NULL, 'C', NULL, NULL, NULL, ".$cost.",".$cost.", NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 1)");
$productid = mysqli_insert_id($conn);                                                
//Weight Row
mysqli_query($conn,"INSERT INTO `tandc_live`.`weights` (`product_id`, `status_id`, `weight_gross`, `weight_tear`, `pallet_tare`, `tare_per_carton`, `number_of_cartons`, `original_gross`, `tampered`, `grosstare`) VALUES ($productid, 1, 1, 1, 1, 1, 1, 1, '0', '0')");
$weightid = mysqli_insert_id($conn);

echo '<tr id="basketRow-'.$weightid.'" name="basketRow-'.$weightid.'"><td>'.$name.'</td><td class="price">'.$cost.'</td><td><a href="javascript:;" onclick="deleteRow('.$weightid.')"><i class="fa fa-trash" aria-hidden="true" style="margin-left:30px;font-size:24px;color:#000;"></i></a></td></tr>';

?>