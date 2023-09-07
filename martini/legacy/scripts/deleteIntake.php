<?php
	require(__DIR__.'/../functions.php');
	
	$intake_id = request()->input('intake_id');
	
	deleteIntake($intake_id);
?>
<script>
	window.location = '../intakeList.php';
</script>
