<?php

	require(__DIR__.'/../functions.php');

	
	$productid = request('productid');
	$comment = request('comment');
	
	
	echo $x = "UPDATE `product` SET comments=? WHERE id=?";
	
	$y = prepareExecuteQuery($x,'si',[$comment,$productid]);

	
?>