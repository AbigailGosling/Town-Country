<?php
	require(__DIR__.'/../functions.php');
	
	$id = $mysqli->real_escape_string( request()->input('id'));
	$name = $mysqli->real_escape_string( request()->input('name'));
	
	$x = "UPDATE `brands` SET `name`=? WHERE `id`=?";
	$y = prepareExecuteQuery($x,'si',[$name,$id]);
?>
<script>
	window.location = '../manageBrands.php';
</script>
