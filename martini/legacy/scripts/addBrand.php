<?php
	require(__DIR__.'/../functions.php');
	
	$name = request()->input('name');
	
	$x = "INSERT into `brands` (`name`) VALUES (?)";
	$y = prepareExecuteQuery($x,'s',[$name]);
?>
<script>
	window.location = '../manageBrands.php';
</script>
