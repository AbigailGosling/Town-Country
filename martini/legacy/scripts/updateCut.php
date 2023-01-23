<?php
	require(__DIR__.'/../functions.php');
	
	$id = $mysqli->real_escape_string( request('id'));
	$species_id = $mysqli->real_escape_string( request('species_id'));
	$cutgroup_id = $mysqli->real_escape_string( request('cutgroup_id'));
	$name = $mysqli->real_escape_string( request('name'));
	
	$x = "UPDATE `cuts` SET species_id = ?, cutgroup_id = ?, `name` = ? WHERE id = ?";
	$y = prepareExecuteQuery($x,'sssi',[$species_id,$cutgroup_id,$name,$id]);
?>
<script>
	window.location = '../manageCuts.php';
</script>
