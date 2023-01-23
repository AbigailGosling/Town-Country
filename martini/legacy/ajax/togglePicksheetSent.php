<?php

	require(__DIR__.'/../functions.php');

	
	$picksheet = request('picksheet');
	$status = request('status');
 	
	
	$x = "UPDATE `pickerSheets` SET `sent`=? WHERE `id`=?";
	
	$y = prepareExecuteQuery($x,'si',[$status,$picksheet]);

	
?>