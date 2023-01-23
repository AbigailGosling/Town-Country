<?php
	require(__DIR__.'/../functions.php');
	
	$id = $mysqli->real_escape_string( request('id'));
	
	$x = "DELETE FROM `customers` WHERE id = ?";
	$y = prepareExecuteQuery($x,'i',[$id]);
?>
<script>
	window.location = '../manageCustomers.php';
</script>
