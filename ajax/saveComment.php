<?php

	require('../functions.php');

	
	$productid = $_GET['productid'];
	$comment = $_GET['comment'];
	
	
	echo $x = "UPDATE `product` SET comments='$comment' WHERE id='$productid'";
	
	$y = mysqli_query($conn, $x) or die(mysqli_error($conn));

	
?>