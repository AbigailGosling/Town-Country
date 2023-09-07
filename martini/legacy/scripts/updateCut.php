<?php
	require(__DIR__.'/../functions.php');
	
	$id = request()->input('id');
	$species_id = request()->input('species_id');
	$cutgroup_id = request()->input('cutgroup_id');
	$name = request()->input('name');
	$warning = request()->input('warning');
	$danger = request()->input('danger');
	if ($warning == null)$warning = "NULL";
	if ($danger == null)$danger = "NULL";
	$x = "UPDATE `cuts` SET species_id='$species_id', cutgroup_id='$cutgroup_id', name='$name', warning=$warning, danger=$danger WHERE id = '$id'";
	$y = prepareExecuteQuery($x);
?>
<script>
	window.location = '../manageCuts.php';
</script>
