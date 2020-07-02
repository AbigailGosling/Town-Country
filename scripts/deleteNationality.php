<?php
	require('../functions.php');
	
	$id = mysqli_real_escape_string($conn, $_GET['id']);
	
	$x = "DELETE FROM `nationality` WHERE id = '$id'";
	$y = mysqli_query($conn, $x);
?>
<script>
	window.location = '../manageNationalities.php';
</script>
