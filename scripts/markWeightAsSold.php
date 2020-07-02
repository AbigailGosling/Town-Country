<?php
	require('../functions.php');
	
	$weightIDs = mysqli_real_escape_string($conn, $_POST['weightid']);
    

    $weightIDs = rtrim($weightIDs, ',');
    
    $ids = explode(',', $weightIDs);

	$intakeID = mysqli_real_escape_string($conn, $_POST['intakeid']);
	
    
    foreach($ids as $weightID){

        $x = "SELECT * FROM `weights` WHERE id='$weightID'";
        $y = mysqli_query($conn, $x);
        
        $weight = mysqli_fetch_array($y);
        
        
        $x = "UPDATE `weights` SET status_id='1', tampered='1' WHERE id='$weightID' LIMIT 1";
        $y = mysqli_query($conn, $x);
    }
?>

<script> window.location.href = '<?php echo $domain; ?>intake.php?id=<?php echo $intakeID; ?>'; </script>