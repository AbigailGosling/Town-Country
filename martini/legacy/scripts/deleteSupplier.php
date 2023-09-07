<?php
	require(__DIR__.'/../functions.php');
	
	$id = request()->input('id');
	
	$x = "DELETE FROM `supplier` WHERE id = ?";
	$y = prepareExecuteQuery($x,'i',[$id]);
?>
<script>
	window.location = '../manageSuppliers.php';
</script>
