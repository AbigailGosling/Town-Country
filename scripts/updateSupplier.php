<?php
	require('../functions.php');
	
	$id = mysqli_real_escape_string($conn, $_POST['id']);
	$name = mysqli_real_escape_string($conn, $_POST['name']);
	$postcode = mysqli_real_escape_string($conn, $_POST['postcode']);
	$contact = mysqli_real_escape_string($conn, $_POST['contact']);
	
	$x = "UPDATE `supplier` SET name='$name', postcode='$postcode', contact_number='$contact' WHERE id = '$id'";
	$y = mysqli_query($conn, $x);
?>
<script>
	window.location = '../manageSuppliers.php';
</script>