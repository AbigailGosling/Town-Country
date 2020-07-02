<?php
	require('../functions.php');
	
	$name = mysqli_real_escape_string($conn, $_POST['name']);
	$postcode = mysqli_real_escape_string($conn, $_POST['postcode']);
	$contact = mysqli_real_escape_string($conn, $_POST['contact']);
	
	$x = "INSERT into `supplier` (name,postcode,contact_number) VALUES ('$name','$postcode','$contact')";
	$y = mysqli_query($conn, $x);
?>
<script>
	window.location = '../manageSuppliers.php';
</script>
