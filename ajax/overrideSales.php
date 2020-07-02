<?php

	require('../functions.php');
	
	
	$id = mysqli_real_escape_string($conn, $_POST['id']);
	
	if($id != ''){
		
		$x = "UPDATE `customers` SET override='1' WHERE id='$id' LIMIT 1";
		$y = mysqli_query($conn, $x);
		
	}
	
?>