<?php
	require(__DIR__.'/../functions.php');
	
	$weightID = request()->input('id');
	
	deleteWeight($weightID);
	
	$intakeID = request()->input('intakeid');
?>
<script>
	window.location = '../intake.php?id=<?php echo $intakeID; ?>';
</script>
