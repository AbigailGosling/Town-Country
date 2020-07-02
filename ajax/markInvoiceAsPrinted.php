<?php
	require('../functions.php');
	
	if($_GET['id'] != ''){
		
		$id = mysqli_real_escape_string($conn, $_GET['id']);
		$x = "UPDATE `pickerSheets` SET invoice_printed='1' WHERE id='$id' LIMIT 1";
		$y = mysqli_query($conn, $x);
		
	}
?>