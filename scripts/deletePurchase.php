<?php
	require('../functions.php');
	
	$purchaseID = mysqli_real_escape_string($conn, $_GET['purchase_id']);
	
	deletePurchase($purchaseID);
?>
<script>
	window.location = '../purchaseList.php';
</script>