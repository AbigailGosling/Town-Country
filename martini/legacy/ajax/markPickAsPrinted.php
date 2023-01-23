<?php
	require(__DIR__.'/../functions.php');
	
	if(request('id') != ''){
		
		$id = $mysqli->real_escape_string( request('id'));
		$x = "UPDATE `pickerSheets` SET deliverynote_printed='1' WHERE id=? LIMIT 1";
		$y = prepareExecuteQuery($x,'i',[$id]);
		
	}
?>