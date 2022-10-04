<?php
	require('../functions.php');
	
	$id = mysqli_real_escape_string($conn, $_POST['id']);
	$name = mysqli_real_escape_string($conn, $_POST['name']);
	$postcode = mysqli_real_escape_string($conn, $_POST['postcode']);
	$contact_name = mysqli_real_escape_string($conn, $_POST['contact_name']);
	$contact_number = mysqli_real_escape_string($conn, $_POST['contact_number']);
	$user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
	$internal_number = mysqli_real_escape_string($conn, $_POST['internal_number']);
	
	$x = "UPDATE `supplier` SET `name`='$name', 
		`postcode`='$postcode', 
		`contact_name`='$contact_name', 
		`contact_number`='$contact_number',
		`user_id`='$user_id', 
		`internal_number`='$internal_number' 
		WHERE id = '$id'";
	$y = mysqli_query($conn, $x);
?>
<script>
	window.location = '../manageSuppliers.php';
</script>