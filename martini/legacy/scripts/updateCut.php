<?php
	require(__DIR__.'/../functions.php');
	
	$id = $mysqli->real_escape_string( request()->input('id'));
	$species_id = $mysqli->real_escape_string( request()->input('species_id'));
	$cutgroup_id = $mysqli->real_escape_string( request()->input('cutgroup_id'));
	$name = $mysqli->real_escape_string( request()->input('name'));
	$warning = $mysqli->real_escape_string( request()->input('warning'));
	$danger = $mysqli->real_escape_string( request()->input('danger'));
	if ($warning == null)$warning = "NULL";
	if ($danger == null)$danger = "NULL";
	$x = "UPDATE `cuts` SET species_id='$species_id', cutgroup_id='$cutgroup_id', name='$name', warning=$warning, danger=$danger WHERE id = '$id'";
	$y = prepareExecuteQuery($x);
?>
<script>
	window.location = '../manageCuts.php';
</script>
