<?php
	require('../functions.php');

	
	$intake_id = $_POST['intake_id'];
	$delivery_note_number = $_POST['delivery_note_number'];
    
    
    $y = mysqli_query($conn, "UPDATE `intake` SET delivery_note_number='$delivery_note_number' WHERE id='$intake_id' LIMIT 1");
?>
<script> window.location.href = '/intake.php?id=<?php echo $intake_id; ?>'; </script>