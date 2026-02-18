<?php
	require(__DIR__.'/../functions.php');

	$id = request()->input('id');

	$x = "UPDATE `brands` SET `deleted` = 0 WHERE id = ?";
	$y = prepareExecuteQuery($x,'i',[$id]);
?>
<script>
	window.location = '../manageBrands.php';
</script>
