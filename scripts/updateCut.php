<?php
	require('../functions.php');
	
	$id = mysqli_real_escape_string($conn, $_POST['id']);
	$species_id = mysqli_real_escape_string($conn, $_POST['species_id']);
	$cutgroup_id = mysqli_real_escape_string($conn, $_POST['cutgroup_id']);
	$name = mysqli_real_escape_string($conn, $_POST['name']);
	$warning = mysqli_real_escape_string($conn, $_POST['warning']);
	$danger = mysqli_real_escape_string($conn, $_POST['danger']);
	
	$x = "UPDATE `cuts` SET species_id='$species_id', cutgroup_id='$cutgroup_id', name='$name', warning=$warning, danger=$danger WHERE id = '$id'";
	$y = mysqli_query($conn, $x);
?>
<script>
	window.location = '../manageCuts.php';
</script>
