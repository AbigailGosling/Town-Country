<?php

	require(__DIR__.'/../functions.php');
	
	
	$id = $mysqli->real_escape_string( request()->input('id'));
	
	if($id != ''){
		
		$x = "UPDATE `customers` SET `override` = IF(`override` = 1,0,1) WHERE id=? LIMIT 1";
		$y = prepareExecuteQuery($x,'i',[$id]);
		
	}
	
?>