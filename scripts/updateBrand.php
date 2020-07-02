<?php
	require('../functions.php');
	
	$id = mysqli_real_escape_string($conn, $_POST['id']);
	$name = mysqli_real_escape_string($conn, $_POST['name']);
	
	$x = "UPDATE `brands` SET name='$name' WHERE id='$id'";
	$y = mysqli_query($conn, $x);
?>
<script>
	window.location = '../manageBrands.php';
</script>
