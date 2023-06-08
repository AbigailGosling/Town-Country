<?php
	require(__DIR__.'/../functions.php');
	
	$intakeid = $mysqli->real_escape_string( request()->input('intakeid'));
	$docid = $mysqli->real_escape_string( request()->input('docid'));
	
	deleteIntakeDoc($intakeid, $docid);
?>
<script>
	window.location = '../intake.php?id=<?php echo $intakeid; ?>';
</script>