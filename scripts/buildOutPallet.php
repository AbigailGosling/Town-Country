<?php
	require('../functions.php');
    debuglogging("palletOut Setup Started");
	$pickersheet_id = mysqli_real_escape_string($conn, $_GET['id']);
    
	$functype = $_POST['functype'];
    
    $weight_ids = $_POST['weightids'];
    $weight_ids = rtrim($weight_ids,',');

    $checkSoldResult = queryproxy($conn, "SELECT *  FROM `weights` WHERE id IN ($weight_ids) && status_id = 1");
    $weightsAlreadySold = mysqli_num_rows($checkSoldResult);

    if($weightsAlreadySold > 0){
    ?>
        <script>
            window.location = '../viewPickSheet.php?id=<?php echo $pickersheet_id; ?>&type=<?php echo $_GET['type']; ?>';
        </script>
    <?php
        exit();
    } 

 	if($functype == 'ADD'){ # add new weights to latest out pallet
		debuglogging("Starting ADD");
		# last out pallet
		$x = "SELECT * FROM palletsOut WHERE pickersheet_id='$pickersheet_id' ORDER BY id DESC LIMIT 1";
		$y = queryproxy($conn, $x);
		$exists = mysqli_num_rows($y);
		if($exists){
            $outPallet = mysqli_fetch_array($y);
            $outPalletID = $outPallet['id'];

            $grossTareArray = explode(',', $outPallet['weight_ids']); 
            $grosstareEmpty = true;
            
            foreach($_POST['grossids'] as $weightID){
                
                  if(is_numeric($weightID) && $_POST['gross_' . $weightID] != 0){
    
                    $grosstareEmpty = false;
                    debuglogging("Processing grossids");
    
                    # START GET WEIGHT ROW
                    $x1 = "SELECT * FROM `weights` WHERE id='$weightID'";
                    $y1 = queryproxy($conn, $x1);
                    $weight = mysqli_fetch_array($y1);
                    $tare = $weight['weight_gross'];
                    # END GET WEIGHT ROW 
                    
    
                    $product_id = $weight['product_id'];
                    
                    $weightOne = $_POST['gross_' . $weightID];
                    $weightTwo = (float) $tare - (float) $weightOne;
                    
                    # START UPDATE CURRENT WEIGHT INFO
                    $x2 = "UPDATE `weights` SET weight_gross='$weightOne', weight_tear='$weightOne', grosstare='0', status_id='1' WHERE id='$weightID'";
                    $y2 = queryproxy($conn, $x2) or die(mysqli_error($conn));
                    # END UPDATE CURRENT WEIGHT INFO
                    
                    array_push($grossTareArray, $weightID);
    
                    # START CREATE NEW WEIGHT FOR REMAINING GROSSTARE WEIGHT
                    $x3 = "INSERT into `weights` (product_id, weight_gross, weight_tear,status_id,grosstare) VALUES ('$product_id','$weightTwo','$weightTwo','0',0)";
                    $y3 = queryproxy($conn, $x3) or die(mysqli_error($conn));
                    # END CREATE NEW WEIGHT FOR REMAINING GROSSTARE WEIGHT
    
                }
            }

            // if($grosstareEmpty == false){
            //     $weightString = implode(',', $grossTareArray);		
            //     $x = "UPDATE `palletsOut` SET weight_ids='$weightString' WHERE id='$outPalletID'";
            //     $y = queryproxy($conn, $x) or die(mysqli_error($conn));
            // }

        

            # START NORMAL WEIGHT
 
            $weightids = explode(',', $_POST['weightids']);
            debuglogging("Processing normal weights");
            foreach($weightids as $weightID){
                if($weightID != ''){

                    $x = "UPDATE `weights` SET status_id='1' WHERE id='$weightID' LIMIT 1";
                    $y = queryproxy($conn, $x);

                    array_push($grossTareArray, $weightID);  # add to that existing weights array
                }
            }
            debuglogging("pre palletsOut update");
            if(!empty($grossTareArray)){
                debuglogging("palletsOut update started");
                $pickers = explode(",",$outPallet['picker_ids']);
                $pickers[] = $userid;
                $pickers = array_unique($pickers);
                $pickers = implode(",",$pickers);
                $weightString = implode(',', $grossTareArray);		
                $x = "UPDATE `palletsOut` SET weight_ids='$weightString', picker_ids = '$pickers' WHERE id='$outPalletID'";
                $y = queryproxy($conn, $x) or die(mysqli_error($conn));
            }
            # END NORMAL WEIGHT

		}else{
            debuglogging("redirected NEW");
			$functype = 'NEW';
		}
	}
	
	
	if($functype == 'NEW'){ # create new out pallet & add weights
        debuglogging("Started NEW");
        $grossTareArray = array(); 
        $grosstareEmpty = true;
        
		foreach($_POST['grossids'] as $weightID){
            
  			if(is_numeric($weightID) && $_POST['gross_' . $weightID] != 0){
                debuglogging("Processing grossids");
                $grosstareEmpty = false;


                # START GET WEIGHT ROW
                $x1 = "SELECT * FROM `weights` WHERE id='$weightID'";
                $y1 = queryproxy($conn, $x1);
                $weight = mysqli_fetch_array($y1);
				$tare = $weight['weight_gross'];
                # END GET WEIGHT ROW                

                $product_id = $weight['product_id'];
                
                $weightOne = $_POST['gross_' . $weightID];
                $weightTwo = (float) $tare - (float) $weightOne;
                
				
                # START UPDATE CURRENT WEIGHT INFO
                $x2 = "UPDATE `weights` SET weight_gross='$weightOne', weight_tear='$weightOne', status_id='1' WHERE id='$weightID'";
                $y2 = queryproxy($conn, $x2) or die(mysqli_error($conn));
                # END UPDATE CURRENT WEIGHT INFO
                
                array_push($grossTareArray, $weightID);

                # START CREATE NEW WEIGHT FOR REMAINING GROSSTARE WEIGHT
                $x3 = "INSERT into `weights` (product_id, weight_gross, weight_tear,status_id) VALUES ('$product_id','$weightTwo','$weightTwo','0')";
                $y3 = queryproxy($conn, $x3) or die(mysqli_error($conn));
                # END CREATE NEW WEIGHT FOR REMAINING GROSSTARE WEIGHT

            }
        }
        debuglogging("pre palletsOut insert for gross");
        if($grosstareEmpty == false){
            debuglogging("palletsOut insert started");
            $weightString = implode(',', $grossTareArray);		
		    $x = "INSERT INTO `palletsOut` (pickersheet_id,weight_ids,stringName,picker_ids) VALUES ('$pickersheet_id','$weightString','#',$userid)";
            $y = queryproxy($conn, $x) or die(mysqli_error($conn));
        }

        
        // $jointArray = array_merge($weightArray, $grossTareArray);
        

        # START NORMAL WEIGHT
        $weightArray = array();
        debuglogging("Processing normal weights");
        $weightids = explode(',', $_POST['weightids']);

        foreach($weightids as $weightID){


            if($weightID != ''){
                
                $x = "UPDATE `weights` SET status_id='1' WHERE id='$weightID' LIMIT 1";
                $y = queryproxy($conn, $x);
            
                array_push($weightArray, $weightID);  # add to that existing weights array
            }
        }
        debuglogging("pre palletsOut insert for normal");
        if(!empty($weightArray)){
            debuglogging("palletsOut insert started");
            $exploded_weightArray = implode(',', $weightArray);		
		    $x = "INSERT INTO `palletsOut` (pickersheet_id,weight_ids,stringName,picker_ids) VALUES ('$pickersheet_id','$exploded_weightArray','#',$userid)";
            $y = queryproxy($conn, $x) or die(mysqli_error($conn));
        }
        # END NORMAL WEIGHT
	}
	debuglogging("palletOut Setup Complete");
	
	
	
    ?>
<script>
	window.location = '../viewPickSheet.php?id=<?php echo $pickersheet_id; ?>&type=<?php echo $_GET['type']; ?>';
</script>