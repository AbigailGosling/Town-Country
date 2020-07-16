<?php
    require('../functions.php');
    
	$product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
	$weightIDs = mysqli_real_escape_string($conn, $_POST['weightid']);
    $intakeID = mysqli_real_escape_string($conn, $_POST['intakeid']);


    $weightIDs = rtrim($weightIDs, ',');
    $ids = explode(',', $weightIDs);

    if($_POST['weightid'] != ''){ # Specific weight ID's have been posted
        
        
        foreach($ids as $weightID){
            $x = "SELECT * FROM `weights` WHERE id='$weightID'";
            $y = mysqli_query($conn, $x);
            
            $weight = mysqli_fetch_array($y);
            
            $x = "UPDATE `weights` SET status_id='1', tampered='1' WHERE id='$weightID' LIMIT 1";
            $y = mysqli_query($conn, $x);
        }
    }else{ # no weights posted, mark all as sold

        $x = "UPDATE `weights` SET status_id='1', tampered='1' WHERE product_id='$product_id'";
        $y = mysqli_query($conn, $x) or die(mysqli_error($conn));
    }
?>

<script> window.location.href = '<?php echo $domain; ?>intake.php?id=<?php echo $intakeID; ?>'; </script>