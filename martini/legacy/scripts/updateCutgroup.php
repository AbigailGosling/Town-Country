<?php
	require(__DIR__.'/../functions.php');
	
	$id = $mysqli->real_escape_string( request('id'));
	$name = $mysqli->real_escape_string( request('name')); 
	$species_id = $mysqli->real_escape_string( request('species_id')); 
	
	$x = "UPDATE `cutgroups` SET `name`= ?,`species_id`= ? WHERE `id` = ?";
	$y = prepareExecuteQuery($x,'ssi',[$name,$species_id,$id]);
?>
<script>
	window.location = '../manageCutgroups.php';
</script>
