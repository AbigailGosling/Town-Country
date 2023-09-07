<?php

require(__DIR__.'/../functions.php');

	
$id = request()->input('id');
$amount = request()->input('amount');

$x = "UPDATE `customers` SET markup_enabled = NOT markup_enabled,markup_amount = ? WHERE id = ?";

$y = prepareExecuteQuery($x,'di',[$amount,$id]);


?>