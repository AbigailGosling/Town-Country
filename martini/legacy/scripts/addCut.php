<?php
	require(__DIR__.'/../functions.php');
	$species_id = $mysqli->real_escape_string( request()->input('species_id'));
	$cutgroup_id = $mysqli->real_escape_string( request()->input('cutgroup_id'));
	$name = $mysqli->real_escape_string( request()->input('name'));
	$warning = $mysqli->real_escape_string( request()->input('warning'));
	$danger = $mysqli->real_escape_string( request()->input('danger'));
	if ($warning == null)$warning = "NULL";
	if ($danger == null)$danger = "NULL";
	$x = "INSERT into `cuts` (species_id,cutgroup_id,name,warning,danger) VALUES ('$species_id','$cutgroup_id','$name',$warning,$danger)";
	$y = prepareExecuteQuery($x);
?>
<script>
	window.location = '../manageCuts.php';
</script>
