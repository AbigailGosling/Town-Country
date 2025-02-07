<?php
require_once(__DIR__.'/../functions.php');
$product_id = request()->input('product_id');
$body = trim(request()->input('body'));
$x = "UPDATE `product` SET `comments` = ? WHERE `id` = ?";
prepareExecuteQuery($x,'si',[$body,$product_id]);
loggedDataChange("product_comment",$product_id,$body);
?>
