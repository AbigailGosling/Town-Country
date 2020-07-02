<?php
	require('../functions.php');
	
	$name = mysqli_real_escape_string($conn, $_POST['name']);
	
	$x = "INSERT into `brands` (name) VALUES ('$name')";
	$y = mysqli_query($conn, $x);
?>
<script>
	window.location = '../manageBrands.php';
</script>
