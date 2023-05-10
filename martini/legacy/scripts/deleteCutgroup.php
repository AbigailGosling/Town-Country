<?php
	require(__DIR__.'/../functions.php');
	
	$id = $mysqli->real_escape_string( request()->input('id'));
	
	$x = "DELETE FROM `cutgroups` WHERE id = ?";
	$y = prepareExecuteQuery($x,'i',[$id]);
?>
<script>
	window.location = '../manageCutgroups.php';
</script>
