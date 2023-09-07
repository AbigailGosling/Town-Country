<?php
	require(__DIR__.'/../functions.php');
	
	$id = request()->input('id');
	
	$x = "DELETE FROM `nationality` WHERE id = ?";
	$y = prepareExecuteQuery($x,'i',[$id]);
?>
<script>
	window.location = '../manageNationalities.php';
</script>
