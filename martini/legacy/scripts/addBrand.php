<?php
	require(__DIR__.'/../functions.php');
	
	$name = $mysqli->real_escape_string( request('name'));
	
	$x = "INSERT into `brands` (`name`) VALUES (?)";
	$y = prepareExecuteQuery($x,'s',[$name]);
?>
<script>
	window.location = '../manageBrands.php';
</script>
