<?php
	require(__DIR__.'/../functions.php');
	
    $intake_id = $mysqli->real_escape_string( request()->input('intake_id'));
    $pallet_id = $mysqli->real_escape_string( request()->input('pallet_id'));
    $status = $mysqli->real_escape_string( request()->input('status'));
    
    if($status == 1 || $status == 0){
        markPalletAs($pallet_id, $status);
    }
?>

<script>
	window.location = '../intake.php?id=<?php echo $intake_id; ?>';
</script>