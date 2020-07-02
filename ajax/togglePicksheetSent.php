<?php

	require('../functions.php');

	
	$picksheet = $_GET['picksheet'];
	$status = $_GET['status'];
 	
	
	$x = "UPDATE `pickerSheets` SET sent=$status WHERE id='$picksheet'";
	
	$y = mysqli_query($conn, $x) or die(mysqli_error($conn));

	
?>