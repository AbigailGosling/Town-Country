<?php
	require('../functions.php');
	
	$pickersheet_id = mysqli_real_escape_string($conn, $_GET['id']);
    
	$functype = $_POST['functype'];
    
 	if($functype == 'ADD'){ # add new weights to latest out pallet
		
		# last out pallet
		$x = "SELECT * FROM palletsOut WHERE pickersheet_id='$pickersheet_id' ORDER BY id DESC LIMIT 1";
		$y = mysqli_query($conn, $x);
		$exists = mysqli_num_rows($y);
		if($exists){
            $outPallet = mysqli_fetch_array($y);
            $outPalletID = $outPallet['id'];

            $grossTareArray = explode(',', $outPallet['weight_ids']); 
            $grosstareEmpty = true;
            
            foreach($_POST['grossids'] as $weightID){
                
                  if(is_numeric($weightID) && $_POST['gross_' . $weightID] != 0){
    
                    $grosstareEmpty = false;
    
    
                    # START GET WEIGHT ROW
                    $x1 = "SELECT * FROM `weights` WHERE id='$weightID'";
                    $y1 = mysqli_query($conn, $x1);
                    $weight = mysqli_fetch_array($y1);
                    # END GET WEIGHT ROW
    
                    # START GROSSTARE WEIGHT CALCULATIONS
                    $original_gross = number_format($weight['original_gross'], 2, '.', '');
                    $num_cartons = number_format($weight['number_of_cartons'], 2, '.', '');
                    $pallet_tare = number_format($weight['pallet_tare'], 2, '.', '');
                    $tare_per_carton = number_format($weight['tare_per_carton'], 2, '.', '');
                    
                    $carton_tare = $num_cartons * $tare_per_carton;
                    $total_tare = $carton_tare + $pallet_tare;
                    $tare = $original_gross - $total_tare;
                    # END GROSSTARE WEIGHT CALCULATIONS
                    
    
                    $product_id = $weight['product_id'];
                    
                    $weightOne = $_POST['gross_' . $weightID];
                    $weightTwo = (float) $tare - (float) $weightOne;
                    
                    
                    # START UPDATE CURRENT WEIGHT INFO
                    $x2 = "UPDATE `weights` SET weight_gross='$weightOne', weight_tear='$weightOne', grosstare='0', status_id='1' WHERE id='$weightID'";
                    $y2 = mysqli_query($conn, $x2) or die(mysqli_error($conn));
                    # END UPDATE CURRENT WEIGHT INFO
                    
                    array_push($grossTareArray, $weightID);
    
                    # START CREATE NEW WEIGHT FOR REMAINING GROSSTARE WEIGHT
                    $x3 = "INSERT into `weights` (product_id, weight_gross, weight_tear,status_id,grosstare) VALUES ('$product_id','$weightTwo','$weightTwo','0',0)";
                    $y3 = mysqli_query($conn, $x3) or die(mysqli_error($conn));
                    # END CREATE NEW WEIGHT FOR REMAINING GROSSTARE WEIGHT
    
                }
            }

            // if($grosstareEmpty == false){
            //     $weightString = implode(',', $grossTareArray);		
            //     $x = "UPDATE `palletsOut` SET weight_ids='$weightString' WHERE id='$outPalletID'";
            //     $y = mysqli_query($conn, $x) or die(mysqli_error($conn));
            // }

        

            # START NORMAL WEIGHT
 
            $weightids = explode(',', $_POST['weightids']);

            foreach($weightids as $weightID){
                if($weightID != ''){

                    $x = "UPDATE `weights` SET status_id='1' WHERE id='$weightID' LIMIT 1";
                    $y = mysqli_query($conn, $x);

                    array_push($grossTareArray, $weightID);  # add to that existing weights array
                }
            }

            if(!empty($grossTareArray)){
                $weightString = implode(',', $grossTareArray);		
                $x = "UPDATE `palletsOut` SET weight_ids='$weightString' WHERE id='$outPalletID'";
                $y = mysqli_query($conn, $x) or die(mysqli_error($conn));
            }
            # END NORMAL WEIGHT

		}else{
			$functype = 'NEW';
		}
	}
	
	
	if($functype == 'NEW'){ # create new out pallet & add weights
        
        $grossTareArray = array(); 
        $grosstareEmpty = true;
        
		foreach($_POST['grossids'] as $weightID){
            
  			if(is_numeric($weightID) && $_POST['gross_' . $weightID] != 0){

                $grosstareEmpty = false;


                # START GET WEIGHT ROW
                $x1 = "SELECT * FROM `weights` WHERE id='$weightID'";
                $y1 = mysqli_query($conn, $x1);
                $weight = mysqli_fetch_array($y1);
				$tare = $weight['weight_gross'];
                # END GET WEIGHT ROW                

                $product_id = $weight['product_id'];
                
                $weightOne = $_POST['gross_' . $weightID];
                $weightTwo = (float) $tare - (float) $weightOne;
                
				
                # START UPDATE CURRENT WEIGHT INFO
                $x2 = "UPDATE `weights` SET weight_gross='$weightOne', weight_tear='$weightOne', status_id='1' WHERE id='$weightID'";
                $y2 = mysqli_query($conn, $x2) or die(mysqli_error($conn));
                # END UPDATE CURRENT WEIGHT INFO
                
                array_push($grossTareArray, $weightID);

                # START CREATE NEW WEIGHT FOR REMAINING GROSSTARE WEIGHT
                $x3 = "INSERT into `weights` (product_id, weight_gross, weight_tear,status_id) VALUES ('$product_id','$weightTwo','$weightTwo','0')";
                $y3 = mysqli_query($conn, $x3) or die(mysqli_error($conn));
                # END CREATE NEW WEIGHT FOR REMAINING GROSSTARE WEIGHT

            }
        }
        
        if($grosstareEmpty == false){
            $weightString = implode(',', $grossTareArray);		
		    $x = "INSERT INTO `palletsOut` (pickersheet_id,weight_ids,stringName) VALUES ('$pickersheet_id','$weightString','#')";
            $y = mysqli_query($conn, $x) or die(mysqli_error($conn));
        }

        
        // $jointArray = array_merge($weightArray, $grossTareArray);
        

        # START NORMAL WEIGHT
        $weightArray = array();

        $weightids = explode(',', $_POST['weightids']);

        foreach($weightids as $weightID){


            if($weightID != ''){
                
                $x = "UPDATE `weights` SET status_id='1' WHERE id='$weightID' LIMIT 1";
                $y = mysqli_query($conn, $x);
            
                array_push($weightArray, $weightID);  # add to that existing weights array
            }
        }

        if(!empty($weightArray)){
            $exploded_weightArray = implode(',', $weightArray);		
		    $x = "INSERT INTO `palletsOut` (pickersheet_id,weight_ids,stringName) VALUES ('$pickersheet_id','$exploded_weightArray','#')";
            $y = mysqli_query($conn, $x) or die(mysqli_error($conn));
        }
        # END NORMAL WEIGHT
	}
	
	
	
	
    ?>
<script>
	window.location = '../viewPickSheet.php?id=<?php echo $pickersheet_id; ?>&type=<?php echo $_GET['type']; ?>';
</script>