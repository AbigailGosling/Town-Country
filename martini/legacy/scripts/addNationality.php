<?php
	require(__DIR__.'/../functions.php');
	
	$name = $mysqli->real_escape_string( request()->input('name'));
	
	$x = "INSERT into `nationality` (`name`) VALUES (?)";
	$y = prepareExecuteQuery($x,'s',[$name]);
?>
<script>
	window.location = '../manageNationalities.php';
</script>
