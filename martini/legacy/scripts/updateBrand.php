<?php
	require(__DIR__.'/../functions.php');
	
	$id = request()->input('id');
	$name = request()->input('name');
	
	$x = "UPDATE `brands` SET `name`=? WHERE `id`=?";
	$y = prepareExecuteQuery($x,'si',[$name,$id]);
?>
<script>
	window.location = '../manageBrands.php';
</script>
