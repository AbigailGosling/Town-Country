<?php
	require(__DIR__.'/../functions.php');
	
	$purchaseID = $mysqli->real_escape_string( request('purchase_id'));
	
	deletePurchase($purchaseID);
?>
<script>
	window.location = '../purchaseList.php';
</script>