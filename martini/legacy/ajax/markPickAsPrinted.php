<?php
	require(__DIR__.'/../functions.php');
	
	if(request()->input('id') != ''){
		
		$id = request()->input('id');
		$x = "UPDATE `pickerSheets` SET deliverynote_printed='1' WHERE id=? LIMIT 1";
		$y = prepareExecuteQuery($x,'i',[$id]);
		
	}
?>