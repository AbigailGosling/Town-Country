<?php
	require(__DIR__.'/../functions.php');
	
    $intake_id = request()->input('intake_id');
    $pallet_id = request()->input('pallet_id');
    $status = request()->input('status');
    
    if($status == 1 || $status == 0){
        markPalletAs($pallet_id, $status);
    }
?>

<script>
	window.location = '../intake.php?id=<?php echo $intake_id; ?>';
</script>