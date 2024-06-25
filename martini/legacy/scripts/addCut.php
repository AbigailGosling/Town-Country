<?php
	require(__DIR__.'/../functions.php');
	$species_id = request()->input('species_id');
	$cutgroup_id = request()->input('cutgroup_id');
	$name = request()->input('name');
	$warning = request()->input('warning');
	$danger = request()->input('danger');
	$enabled = (int)request()->input('disabled',0); 
	$x = "INSERT into `cuts` (species_id,cutgroup_id,name,warning,danger,`disabled`) VALUES (?,?,?,?,?,?)";
	$y = prepareExecuteQuery($x,'sssssi',[$species_id,$cutgroup_id,$name,$warning,$danger,$enabled]);
?>
<script>
	window.location = '../manageCuts.php';
</script>
