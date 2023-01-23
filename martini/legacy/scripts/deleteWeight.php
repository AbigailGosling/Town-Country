<?php
	require(__DIR__.'/../functions.php');
	
	$weightID = request('id');
	
	deleteWeight($weightID);
	
	$intakeID = request('intakeid');
?>
<script>
	window.location = '../intake.php?id=<?php echo $intakeID; ?>';
</script>
