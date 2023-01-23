<?php
	require(__DIR__.'/../functions.php');
	
	$id = $mysqli->real_escape_string( request('id'));
	$name = $mysqli->real_escape_string( request('name'));
	
	
	$x = "UPDATE `nationality` SET `name`= ? WHERE `id` = ?";
	$y = prepareExecuteQuery($x,'si',[$name,$id]);
?>
<script>
	window.location = '../manageNationalities.php';
</script>