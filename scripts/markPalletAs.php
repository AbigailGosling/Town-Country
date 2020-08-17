<?php
	require('../functions.php');
	
    $intake_id = mysqli_real_escape_string($conn, $_GET['intake_id']);
    $pallet_id = mysqli_real_escape_string($conn, $_GET['pallet_id']);
    $status = mysqli_real_escape_string($conn, $_GET['status']);
    
    if($status == 1 || $status == 0){
        markPalletAs($pallet_id, $status);
    }
?>

<script>
	window.location = '../intake.php?id=<?php echo $intake_id; ?>';
</script>