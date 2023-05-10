<?php
	require(__DIR__.'/../functions.php');
	
	$id = $mysqli->real_escape_string( request()->input('id'));
	
	$x = "DELETE FROM `cuts` WHERE id = ?";
	$y = prepareExecuteQuery($x,'i',[$id]);
?>
<script>
	window.location = '../manageCuts.php';
</script>
