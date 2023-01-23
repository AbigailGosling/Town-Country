<?php
    require(__DIR__.'/../functions.php');
    
	$product_id = $mysqli->real_escape_string( request('product_id'));
	$weightIDs = $mysqli->real_escape_string( request('weightid'));
    $intakeID = $mysqli->real_escape_string( request('intakeid'));


    $weightIDs = rtrim($weightIDs, ',');
    $ids = explode(',', $weightIDs);

    if(request('weightid') != ''){ # Specific weight ID's have been posted
        
        
        foreach($ids as $weightID){
            $x = "SELECT * FROM `weights` WHERE id=?";
            $y = prepareExecuteQuery($x,'i',[$weightID]);
            
            $weight = mysqli_fetch_array($y);
            
            $x = "UPDATE `weights` SET status_id='1', tampered='1' WHERE id=? LIMIT 1";
            $y = prepareExecuteQuery($x,'i',[$weightID]);
        }
    }else{ # no weights posted, mark all as sold

        $x = "UPDATE `weights` SET status_id='1', tampered='1' WHERE product_id=?";
        $y = prepareExecuteQuery($x,'i',[$product_id]);
    }
?>

<script> window.location.href = '<?php echo $domain; ?>intake.php?id=<?php echo $intakeID; ?>'; </script>