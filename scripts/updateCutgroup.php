<?php
	require('../functions.php');
	
	$id = mysqli_real_escape_string($conn, $_POST['id']);
	$name = mysqli_real_escape_string($conn, $_POST['name']); 
	$species_id = mysqli_real_escape_string($conn, $_POST['species_id']); 
	
	$x = "UPDATE `cutgroups` SET name='$name',species_id='$species_id' WHERE id = '$id'";
	$y = mysqli_query($conn, $x);
?>
<script>
	window.location = '../manageCutgroups.php';
</script>
