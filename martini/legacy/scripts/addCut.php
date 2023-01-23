<?php
	require(__DIR__.'/../functions.php');
	
	$species_id = $mysqli->real_escape_string( request('species_id'));
	$cutgroup_id = $mysqli->real_escape_string( request('cutgroup_id'));
	$name = $mysqli->real_escape_string( request('name'));
	
	$x = "INSERT into `cuts` (`species_id`,`cutgroup_id`,`name`) VALUES (?,?,?)";
	$y = prepareExecuteQuery($x,'sss',[$species_id,$cutgroup_id,$name]);
?>
<script>
	window.location = '../manageCuts.php';
</script>
