<?php
	require(__DIR__.'/../functions.php');
	
	if(request()->input('id') != ''){
		
		$id = request()->input('id');
		$x = "SELECT `customer_id` FROM `pickerSheets` WHERE id=? LIMIT 1";
		$y = prepareExecuteQuery($x,'i',[$id]);
		$customer_id = $y->fetch_assoc()['customer_id'];

		$x = "UPDATE `customers` SET `allowPrint`='0' WHERE id=? LIMIT 1";
		$y = prepareExecuteQuery($x,'i',[$customer_id]);

		$x = "UPDATE `pickerSheets` SET invoice_printed='1' WHERE id=? LIMIT 1";
		$y = prepareExecuteQuery($x,'i',[$id]);
		
	}
?>