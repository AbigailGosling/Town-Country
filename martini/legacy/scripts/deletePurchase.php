<?php
	require(__DIR__.'/../functions.php');
	
	$purchaseID = request()->input('purchase_id',request()->input('id'));
	
	deletePurchase($purchaseID);
	if (request()->has('ts')) {
		?>
<script>
	window.location.href = '../calendar.php?ts=<?php echo request()->input('ts')?>';
</script>
		<?php
	}
	else
	{
?>
<script>
	window.location.href = '../purchaseList.php';
</script>
<?php
	}
?>