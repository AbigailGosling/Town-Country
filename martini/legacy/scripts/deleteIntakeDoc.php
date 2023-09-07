<?php
	require(__DIR__.'/../functions.php');
	
	$intakeid = request()->input('intakeid');
	$docid = request()->input('docid');
	
	deleteIntakeDoc($intakeid, $docid);
?>
<script>
	window.location = '../intake.php?id=<?php echo $intakeid; ?>';
</script>