<?php
	require('../functions.php');
	
	$intake_id = mysqli_real_escape_string($conn, $_GET['intake_id']);
	
	deleteIntake($intake_id);
?>
<script>
	window.location = '../intakeList.php';
</script>
