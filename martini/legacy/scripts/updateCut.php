<?php
	require(__DIR__.'/../functions.php');
	
	$id = request()->input('id');
	$species_id = request()->input('species_id');
	$cutgroup_id = request()->input('cutgroup_id');
	$name = request()->input('name');
	$warning = request()->input('warning');
	$danger = request()->input('danger');
	$enabled = (int)request()->input('disabled',0);
	if ($warning == null)$warning = "NULL";
	if ($danger == null)$danger = "NULL";
	$x = "UPDATE `cuts` SET 
		species_id=?, 
		cutgroup_id=?, 
		name=?, 
		warning=?, 
		danger=?, 
		`disabled`=? 
		WHERE id = ?";
	$y = prepareExecuteQuery($x,'sssssii',[$species_id,$cutgroup_id,$name,$warning,$danger,$enabled,$id]);
?>
<script>
	window.location = '../manageCuts.php';
</script>
