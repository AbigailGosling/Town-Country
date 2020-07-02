<?php

	require('../functions.php');

	
	$productid = $_POST['productid'];
	$comment = $_POST['comment'];
	
	
	echo $x = "UPDATE `product` SET weightnote='$comment' WHERE id='$productid'";
	
	$y = mysqli_query($conn, $x) or die(mysqli_error($conn));

	
?>