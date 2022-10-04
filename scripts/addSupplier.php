<?php
	require('../functions.php');
	
	$name = mysqli_real_escape_string($conn, $_POST['name']);
	$postcode = mysqli_real_escape_string($conn, $_POST['postcode']);
	$contact_name = mysqli_real_escape_string($conn, $_POST['contact_name']);
	$contact_number = mysqli_real_escape_string($conn, $_POST['contact_number']);
	$user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
	$internal_number = mysqli_real_escape_string($conn, $_POST['internal_number']);
	
	$x = "INSERT into `supplier` (`name`,`postcode`,`contact_name`,`contact_number`,`user_id`,`internal_number`) VALUES ('$name','$postcode','$contact_name','$contact_number','$user_id','$internal_number')";
	$y = mysqli_query($conn, $x);
?>
<script>
	window.location = '../manageSuppliers.php';
</script>
