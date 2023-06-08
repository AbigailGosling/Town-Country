<?php
	require(__DIR__.'/../functions.php');
	
	$id = mysqli_real_escape_string($conn, request()->input('id'));
	$species_id = mysqli_real_escape_string($conn, request()->input('species_id'));
	$cutgroup_id = mysqli_real_escape_string($conn, request()->input('cutgroup_id'));
	$name = mysqli_real_escape_string($conn, request()->input('name'));
	$warning = mysqli_real_escape_string($conn, request()->input('warning'));
	$danger = mysqli_real_escape_string($conn, request()->input('danger'));
	if ($warning == null)$warning = "NULL";
	if ($danger == null)$danger = "NULL";
	$x = "UPDATE `cuts` SET species_id='$species_id', cutgroup_id='$cutgroup_id', name='$name', warning=$warning, danger=$danger WHERE id = '$id'";
	$y = prepareExecuteQuery($x);
?>
<script>
	window.location = '../manageCuts.php';
</script>
