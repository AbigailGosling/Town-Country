<?php
	require('../functions.php');

	
	$intake_id = $_POST['intake_id'];
	$supplier_id = $_POST['supplier_id'];
    
    
    $y = mysqli_query($conn, "UPDATE `intake` SET supplier_id='$supplier_id' WHERE id='$intake_id' LIMIT 1");
?>
<script> window.location.href = '/intake.php?id=<?php echo $intake_id; ?>'; </script>