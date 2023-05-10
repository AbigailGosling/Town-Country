<?php

	require(__DIR__.'/../functions.php');

	
	$picksheet = request()->input('picksheet');
	$status = request()->input('status');
 	
	
	$x = "UPDATE `pickerSheets` SET invoicesent=? WHERE id=?";
	
	$y = prepareExecuteQuery($x,'si',[$status,$picksheet]);

	
?>