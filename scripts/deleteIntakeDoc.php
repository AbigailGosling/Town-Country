<?php
	require('../functions.php');
	
	$intakeid = mysqli_real_escape_string($conn, $_GET['intakeid']);
	$docid = mysqli_real_escape_string($conn, $_GET['docid']);
	
	deleteIntakeDoc($intakeid, $docid);
?>
<script>
	window.location = '../intake.php?id=<?php echo $intakeid; ?>';
</script>