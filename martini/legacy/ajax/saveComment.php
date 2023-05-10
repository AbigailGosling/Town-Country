<?php

	require(__DIR__.'/../functions.php');

	
	$productid = request()->input('productid');
	$comment = request()->input('comment');
	
	
	echo $x = "UPDATE `product` SET comments=? WHERE id=?";
	
	$y = prepareExecuteQuery($x,'si',[$comment,$productid]);

	
?>