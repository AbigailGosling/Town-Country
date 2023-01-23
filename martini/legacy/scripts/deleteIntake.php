<?php
	require(__DIR__.'/../functions.php');
	
	$intake_id = $mysqli->real_escape_string( request('intake_id'));
	
	deleteIntake($intake_id);
?>
<script>
	window.location = '../intakeList.php';
</script>
