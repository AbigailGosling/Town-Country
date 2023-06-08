<?php
	require(__DIR__.'/../functions.php');
	
	$id = $mysqli->real_escape_string( request()->input('id'));
	$name = $mysqli->real_escape_string( request()->input('name')); 
	$species_id = $mysqli->real_escape_string( request()->input('species_id')); 
	
	$x = "UPDATE `cutgroups` SET `name`= ?,`species_id`= ? WHERE `id` = ?";
	$y = prepareExecuteQuery($x,'ssi',[$name,$species_id,$id]);
?>
<script>
	window.location = '../manageCutgroups.php';
</script>
