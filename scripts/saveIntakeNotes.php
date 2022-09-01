<?php
	require('../functions.php');
	
	$intakeID = $_POST['intakeid'];
	
	$notes = mysqli_real_escape_string($conn, $_POST['notes']);
	
	$x = "UPDATE `intake` SET notes='$notes' WHERE id ='$intakeID'";
	$y = mysqli_query($conn, $x);
	loggedDataChange("intake",$intakeID,$notes);
?>

<script type="text/javascript">
	window.location.href = '../intake.php?id=<?php echo $intakeID; ?>';
</script>

