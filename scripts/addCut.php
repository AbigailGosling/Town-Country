<?php
	require('../functions.php');
	
	$species_id = mysqli_real_escape_string($conn, $_POST['species_id']);
	$cutgroup_id = mysqli_real_escape_string($conn, $_POST['cutgroup_id']);
	$name = mysqli_real_escape_string($conn, $_POST['name']);
	$warning = mysqli_real_escape_string($conn, $_POST['warning']);
	$danger = mysqli_real_escape_string($conn, $_POST['danger']);
	
	$x = "INSERT into `cuts` (species_id,cutgroup_id,name,warning,danger) VALUES ('$species_id','$cutgroup_id','$name',$warning,$danger)";
	$y = mysqli_query($conn, $x);
?>
<script>
	window.location = '../manageCuts.php';
</script>
