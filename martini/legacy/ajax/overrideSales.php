<?php

	require(__DIR__.'/../functions.php');
	
	
	$id = request()->input('id');
	
	if($id != ''){
		
		$x = "UPDATE `customers` SET `allowPrint` = IF(`override` = 1,0,1),`override` = IF(`override` = 1,0,1) WHERE id=? LIMIT 1";
		$y = prepareExecuteQuery($x,'i',[$id]);
		
	}
	
?>