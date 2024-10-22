<?php
	require(__DIR__.'/../functions.php');


	$intake_id = request()->input('intake_id');
	$delivery_note_number = request()->input('import_num');


    $y = prepareExecuteQuery("UPDATE `intake` SET import_num=? WHERE id=? LIMIT 1",'ss',[$delivery_note_number,$intake_id]);
?>
<script> window.location.href = '../intake.php?id=<?php echo $intake_id; ?>'; </script>
