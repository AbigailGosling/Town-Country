<?php
	require(__DIR__.'/../functions.php');
	
	$pickersheet_id = $mysqli->real_escape_string( request()->input('id'));
    
	$functype = request()->input('functype');
    
    $weight_ids = request()->input('weightids');
    $weight_ids = rtrim($weight_ids,',');
    $weight_id_array = explode(",",$weight_ids);

    $checkSoldResult = prepareExecuteQuery("SELECT *  FROM `weights` WHERE `id` IN (".implode(",",array_fill(0,count($weight_id_array),"?")).") && `status_id` = 1",
        str_repeat("i",count($weight_id_array)),$weight_id_array);
    $weightsAlreadySold = mysqli_num_rows($checkSoldResult);

    if($weightsAlreadySold > 0){
    ?>
        <script>
            window.location = '../viewPickSheet.php?id=<?php echo $pickersheet_id; ?>&type=<?php echo request()->input('type'); ?>';
        </script>
    <?php
        exit();
    } 

 	if($functype == 'ADD'){ # add new weights to latest out pallet
		
		# last out pallet
		$x = "SELECT * FROM palletsOut WHERE `pickersheet_id` = ? ORDER BY `id` DESC LIMIT 1";
		$y = prepareExecuteQuery($x,'s',[$pickersheet_id]);
		$exists = mysqli_num_rows($y);
		if($exists){
            $outPallet = mysqli_fetch_array($y);
            $outPalletID = $outPallet['id'];

            $grossTareArray = explode(',', $outPallet['weight_ids']); 
            $grosstareEmpty = true;
            
            foreach(request()->input('grossids') as $weightID){
                
                  if(is_numeric($weightID) && request('gross_' . $weightID) != 0){
    
                    $grosstareEmpty = false;
    
    
                    # START GET WEIGHT ROW
                    $x1 = "SELECT * FROM `weights` WHERE `id` = ?";
                    $y1 = prepareExecuteQuery($x1,'i',[$weightID]);
                    $weight = mysqli_fetch_array($y1);
                    $tare = $weight['weight_gross'];
                    # END GET WEIGHT ROW 
                    
    
                    $product_id = $weight['product_id'];
                    
                    $weightOne = request('gross_' . $weightID);
                    $weightTwo = (float) $tare - (float) $weightOne;
                    
                    # START UPDATE CURRENT WEIGHT INFO
                    $x2 = "UPDATE `weights` SET weight_gross = ?, weight_tear = ?, grosstare='0', status_id='1' WHERE id = ?";
                    $y2 = prepareExecuteQuery($x2,'ssi',[$weightOne,$weightOne,$weightID]);
                    # END UPDATE CURRENT WEIGHT INFO
                    
                    array_push($grossTareArray, $weightID);
    
                    # START CREATE NEW WEIGHT FOR REMAINING GROSSTARE WEIGHT
                    $x3 = "INSERT into `weights` (product_id, weight_gross, weight_tear,status_id,grosstare) VALUES (?,?,?,'0',0)";
                    $y3 = prepareExecuteQuery($x3,'sss',[$product_id,$weightTwo,$weightTwo]);
                    # END CREATE NEW WEIGHT FOR REMAINING GROSSTARE WEIGHT
    
                }
            }

            // if($grosstareEmpty == false){
            //     $weightString = implode(',', $grossTareArray);		
            //     $x = "UPDATE `palletsOut` SET weight_ids='$weightString' WHERE id='$outPalletID'";
            //     $y = prepareExecuteQuery($x) or die(mysqli_error($mysqli));
            // }

        

            # START NORMAL WEIGHT
 
            $weightids = explode(',', request()->input('weightids'));

            foreach($weightids as $weightID){
                if($weightID != ''){

                    $x = "UPDATE `weights` SET status_id='1' WHERE id = ? LIMIT 1";
                    $y = prepareExecuteQuery($x,'i',[$weightID]);

                    array_push($grossTareArray, $weightID);  # add to that existing weights array
                }
            }

            if(!empty($grossTareArray)){
                $pickers = explode(",",$outPallet['picker_ids']);
                $pickers[] = $userid;
                $pickers = array_unique($pickers);
                $pickers = implode(",",$pickers);
                $weightString = implode(',', $grossTareArray);		
                $x = "UPDATE `palletsOut` SET weight_ids = ?, picker_ids = ? WHERE id = ?";
                $y = prepareExecuteQuery($x,'ssi',[$weightString,$pickers,$outPalletID]);
            }
            # END NORMAL WEIGHT

		}else{
			$functype = 'NEW';
		}
	}
	
	
	if($functype == 'NEW'){ # create new out pallet & add weights
        
        $grossTareArray = array(); 
        $grosstareEmpty = true;
        
		foreach(request()->input('grossids') as $weightID){
            
  			if(is_numeric($weightID) && request('gross_' . $weightID) != 0){

                $grosstareEmpty = false;


                # START GET WEIGHT ROW
                $x1 = "SELECT * FROM `weights` WHERE `id` = ?";
                $y1 = prepareExecuteQuery($x1,'i',[$weightID]);
                $weight = mysqli_fetch_array($y1);
				$tare = $weight['weight_gross'];
                # END GET WEIGHT ROW                

                $product_id = $weight['product_id'];
                
                $weightOne = request('gross_' . $weightID);
                $weightTwo = (float) $tare - (float) $weightOne;
                
				
                # START UPDATE CURRENT WEIGHT INFO
                $x2 = "UPDATE `weights` SET weight_gross = ?, weight_tear = ?, status_id='1' WHERE id = ?";
                $y2 = prepareExecuteQuery($x2,'ssi',[$weightOne,$weightOne,$weightID]);
                # END UPDATE CURRENT WEIGHT INFO
                
                array_push($grossTareArray, $weightID);

                # START CREATE NEW WEIGHT FOR REMAINING GROSSTARE WEIGHT
                $x3 = "INSERT into `weights` (product_id, weight_gross, weight_tear,status_id) VALUES (?,?,?,'0')";
                $y3 = prepareExecuteQuery($x3,'iss',[$product_id,$weightTwo,$weightTwo]);
                # END CREATE NEW WEIGHT FOR REMAINING GROSSTARE WEIGHT

            }
        }
        
        if($grosstareEmpty == false){
            $weightString = implode(',', $grossTareArray);		
		    $x = "INSERT INTO `palletsOut` (pickersheet_id,weight_ids,stringName,picker_ids) VALUES (?,?,'#',?)";
            $y = prepareExecuteQuery($x,'iss',[$pickersheet_id,$weightString,$userid]);
        }

        
        // $jointArray = array_merge($weightArray, $grossTareArray);
        

        # START NORMAL WEIGHT
        $weightArray = array();

        $weightids = explode(',', request()->input('weightids'));

        foreach($weightids as $weightID){


            if($weightID != ''){
                
                $x = "UPDATE `weights` SET status_id = '1' WHERE id = ? LIMIT 1";
                $y = prepareExecuteQuery($x,'i',[$weightID]);
            
                array_push($weightArray, $weightID);  # add to that existing weights array
            }
        }

        if(!empty($weightArray)){
            $exploded_weightArray = implode(',', $weightArray);		
		    $x = "INSERT INTO `palletsOut` (pickersheet_id,weight_ids,stringName,picker_ids) VALUES (?,?,'#',?)";
            $y = prepareExecuteQuery($x,'iss',[$pickersheet_id,$exploded_weightArray,$userid]);
        }
        # END NORMAL WEIGHT
	}
	
	
	
	
    ?>
<script>
	window.location = '../viewPickSheet.php?id=<?php echo $pickersheet_id; ?>&type=<?php echo request()->input('type'); ?>';
</script>