<?php
	require('../functions.php');
	
	$species_id = mysqli_real_escape_string($conn, $_POST['species_id']);
	$cutgroup_id = mysqli_real_escape_string($conn, $_POST['cutgroup_id']);
	$name = mysqli_real_escape_string($conn, $_POST['name']);
	
	$x = "INSERT into `cuts` (species_id,cutgroup_id,name) VALUES ('$species_id','$cutgroup_id ','$name')";
	$y = mysqli_query($conn, $x);
?>
<script>
	window.location = '../manageCuts.php';
</script>
