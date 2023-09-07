<?php
	require(__DIR__.'/../functions.php');
	
	$id = request()->input('id');
	
	$x = "DELETE FROM `cuts` WHERE id = ?";
	$y = prepareExecuteQuery($x,'i',[$id]);
?>
<script>
	window.location = '../manageCuts.php';
</script>
