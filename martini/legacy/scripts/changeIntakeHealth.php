<?php
	require(__DIR__.'/../functions.php');


	$intake_id = request()->input('intake_id');
	$delivery_note_number = request()->input('health_id');


    $y = prepareExecuteQuery("UPDATE `intake` SET health_id=? WHERE id=? LIMIT 1",'si',[$delivery_note_number,$intake_id]);
?>
<script> window.location.href = '../intake.php?id=<?php echo $intake_id; ?>'; </script>
