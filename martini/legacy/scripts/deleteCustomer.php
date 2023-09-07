<?php
	require(__DIR__.'/../functions.php');
	
	$id = request()->input('id');
	
	$x = "DELETE FROM `customers` WHERE id = ?";
	$y = prepareExecuteQuery($x,'i',[$id]);
?>
<script>
	window.location = '../manageCustomers.php';
</script>
