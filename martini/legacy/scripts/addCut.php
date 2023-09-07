<?php
	require(__DIR__.'/../functions.php');
	$species_id = request()->input('species_id');
	$cutgroup_id = request()->input('cutgroup_id');
	$name = request()->input('name');
	$warning = request()->input('warning');
	$danger = request()->input('danger');
	if ($warning == null)$warning = "NULL";
	if ($danger == null)$danger = "NULL";
	$x = "INSERT into `cuts` (species_id,cutgroup_id,name,warning,danger) VALUES ('$species_id','$cutgroup_id','$name',$warning,$danger)";
	$y = prepareExecuteQuery($x);
?>
<script>
	window.location = '../manageCuts.php';
</script>
