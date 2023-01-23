<?php
	require(__DIR__.'/../functions.php');

	
	$intake_id = request('intake_id');
	$delivery_note_number = request('delivery_note_number');
    
    
    $y = prepareExecuteQuery("UPDATE `intake` SET delivery_note_number=? WHERE id=? LIMIT 1",'si',[$delivery_note_number,$intake_id]);
?>
<script> window.location.href = '/intake.php?id=<?php echo $intake_id; ?>'; </script>