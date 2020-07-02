<?php
	require('../functions.php');
	
	$weightID = $_GET['id'];
	
	deleteWeight($weightID);
	
	$intakeID = $_GET['intakeid'];
?>
<script>
	window.location = '../intake.php?id=<?php echo $intakeID; ?>';
</script>
