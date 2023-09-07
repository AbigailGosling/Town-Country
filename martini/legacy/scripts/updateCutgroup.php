<?php
	require(__DIR__.'/../functions.php');
	
	$id = request()->input('id');
	$name = request()->input('name'); 
	$species_id = request()->input('species_id'); 
	
	$x = "UPDATE `cutgroups` SET `name`= ?,`species_id`= ? WHERE `id` = ?";
	$y = prepareExecuteQuery($x,'ssi',[$name,$species_id,$id]);
?>
<script>
	window.location = '../manageCutgroups.php';
</script>
