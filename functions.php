<?php
	ini_set('session.gc_maxlifetime', 3600);
	session_start();
    ini_set('post_max_size', '64M');
    ini_set('upload_max_filesize', '64M');
	
	require('config.php');

	$conn = mysqli_connect($dbHost,$dbUser,$dbPass,$dbName);


	error_reporting(0);
 
	$userid = $_SESSION['USER'];
	
	
	$pageName = $_SERVER['REQUEST_URI'];
	
	$exit = 1;
	if($pageName == '/' || $pageName == '/index.php' || $pageName == '/script_login.php'){
		$exit = 0;
	}
	
	
	if(!$_SESSION['USER'] && $exit == 1){ header('location:index.php'); }
	
	if($userid != ''){
		$x = "SELECT * FROM users WHERE id='$userid'";
		$y = mysqli_query($conn, $x);
		
		$user = mysqli_fetch_array($y);
	}
	
	// sendEmail(['kez@phenixdigital.co.uk'], 'test', 'hello');
	function sendEmail($toArray, $mail_subject, $mail_message, $name = 'Town & Country'){
		 
        $domain_email='webform@'.str_replace("www.", "", $_SERVER['HTTP_HOST']);
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: ".$name."<".$domain_email.">\r\n";
        $headers .= "Subject: {$subject}";
        
        
         
        $to = implode(", ", $toArray);
        $boSend = mail($to, $mail_subject, $mail_message, $headers);
	}
	
	function getPicksheetValue($pickersheet_id){
		global $conn;
		
		# sell price * weight
		$outpalletQuery = "SELECT * FROM `palletsOut` WHERE pickersheet_id='$pickersheet_id'";
		$outpalletResult = mysqli_query($conn, $outpalletQuery);

		while($outpallet = mysqli_fetch_array($outpalletResult)){
		$weightids = explode(',', $outpallet['weight_ids']);
			$queryBits = '';
			$queryBits2 = ''; 

			foreach($weightids as $weightid){
				$queryBits .= ' id = ' . $weightid . ' || ';
			}

			$queryBits = rtrim($queryBits," || ");

			$x = "SELECT * FROM `weights` WHERE $queryBits";
			$y = mysqli_query($conn, $x);

			$count = mysqli_num_rows($y);

			$kg = 0;
			while($weight = mysqli_fetch_array($y)){
				$queryBits2 .= ' id = ' . $weight['product_id'] . ' || '; 
				
				
				if($weight['weight_tear'] == $weight['weight_gross']){
					$w = $weight['weight_gross'];
				}else{
					$w = $weight['weight_gross'] - $weight['weight_tear'];
				}
				
				$kg = $kg + $w;
			 
			}
			$queryBits2 = rtrim($queryBits2," || ");

			$x = "SELECT * FROM `product` WHERE $queryBits2 GROUP BY cut_id";
			$y = mysqli_query($conn, $x);
			
			while($product = mysqli_fetch_array($y)){
		 
					$productID = $product['id'];
					$howManyX = "SELECT * FROM `pickerItems` WHERE pickersheet_id='$pickersheet_id' AND product_id='$productID'";
					$howManyY = mysqli_query($conn, $howManyX);
					$pickerItem = mysqli_fetch_array($howManyY);
					
					$totalPrice += number_format((float)$kg * $pickerItem['price'], 2, '.', '');
			
			}
		}
		
		
		return $totalPrice;
	}
	
	function sql($table, $data, $id = '#'){
		global $conn;
		
		if($id != '#'){
			
			foreach ($data as $key => $value) {
				$value = mysqli_real_escape_string($conn, $value);
				$values .= "`$key` = '$value',";
			}
			
			$values = rtrim($values,',');

			$x = "UPDATE `".$table."` SET " . $values . " WHERE id = " .$id; 
		}else{
			
			foreach ($data as $key => $value) {
				$value = mysqli_real_escape_string($conn, $value);
				$cols .= $key .',';
				$vals .= "'$value',";
			}
			
			$cols = rtrim($cols,',');
			$vals = rtrim($vals,',');

			$x = "INSERT INTO `".$table."` (". $cols .") VALUES (". $vals .")";
		}
		
		$y = mysqli_query($conn, $x);

	}
	
	
	
	
	function getPurchase($purchaseid){
		global $conn;
		
		$x = "SELECT * FROM `purchase_form` WHERE id='$purchaseid'";
		$y = mysqli_query($conn, $x);
		
		$row = mysqli_fetch_array($y);
		
		return $row;
	}

	function markPalletAs($pallet_id, $status){
		global $conn;

		$productIDS = array();

		# Get all product IDS for this pallet & store in array
		$productsResult = mysqli_query($conn, "SELECT id FROM `product` WHERE pallet_id='$pallet_id'");
		while($product = mysqli_fetch_array($productsResult)){ array_push($productIDS, $product['id']); }
		$productIDS = implode(',', $productIDS);


		$weightsResult = mysqli_query($conn, "UPDATE `weights` SET status_id='$status', tampered=1 WHERE product_id IN ($productIDS)");
	}

	function isPalletSold($pallet_id){
		global $conn;

		$productIDS = array();

		# Get all product IDS for this pallet & store in array
		$productsResult = mysqli_query($conn, "SELECT id FROM `product` WHERE pallet_id='$pallet_id'");
		while($product = mysqli_fetch_array($productsResult)){ array_push($productIDS, $product['id']); }
		$productIDS = implode(',', $productIDS);

		$allProductWeights = mysqli_query($conn, "SELECT id FROM `weights` WHERE product_id IN ($productIDS)");
		$totalWeights = mysqli_num_rows($allProductWeights);

		$soldProductWeights = mysqli_query($conn, "SELECT id FROM `weights` WHERE status_id='1' && product_id IN ($productIDS)");
		$soldWeights = mysqli_num_rows($soldProductWeights);
		
		if($soldWeights == $totalWeights){
			// entire pallet is sold
			return 1;
		}else{
			// pallet still has some available stock 
			return 0;
		}


		
	}
	
	function createPurchase($supplier_id,$transportation, $speciesString,$cutString,$priceString,$unitsString, $date_purchased, $purchased_by, $date_due, $purchase_comments, $file_name, $booking_ref_number, $haulier, $direct_drop){
		global $conn;
		
		$x = "INSERT into purchase_form (supplier_id,species,cut,price,units,date_purchased,purchased_by,date_due,purchase_comments,dfile,booking_ref_number,transportation,haulier,direct_drop) 
		VALUES ('$supplier_id','$speciesString','$cutString','$priceString','$unitsString','$date_purchased','$purchased_by','$date_due','$purchase_comments','$file_name','$booking_ref_number','$transportation','$haulier','$direct_drop')";
		$y = mysqli_query($conn, $x) or die(mysqli_error($conn));
         
		$id = mysqli_insert_id($conn);
		
		return $id;
	}
	
	
	function updatePurchase($id, $transportation, $supplier_id,$speciesString,$cutString,$unitsString, $priceString, $date_purchased, $purchased_by, $date_due, $purchase_comments, $file_name, $booking_ref_number,$haulier, $direct_drop){
		global $conn;
		
		$x ="UPDATE `purchase_form` SET transportation='$transportation', supplier_id='$supplier_id',species='$speciesString', cut='$cutString',units='$unitsString', price='$priceString', date_purchased='$date_purchased',purchased_by='$purchased_by',date_due='$date_due',
            purchase_comments='$purchase_comments'";
            
        if($file_name != ''){
            $x .=",dfile='$file_name'";
        }

        $x .=",booking_ref_number='$booking_ref_number', haulier='$haulier',direct_drop='$direct_drop' WHERE id='$id'";
	
		$y = mysqli_query($conn, $x) or die(mysqli_error($conn));
		
		
	}
	
	function addIntakeFromPurchase($supplier_id, $purchase_id, $date_received, $vehicle_reg, $vehicle_temperature, $product_temperature, $delivery_note_number,$purchased_id){
		global $conn;
		
		$x = "INSERT into `intake` (supplier_id, purchase_id, date_received, vehicle_reg, vehicle_temperature,product_temperature,delivery_note_number,user_id) 
							VALUES ('$supplier_id', '$purchase_id', '$date_received','$vehicle_reg','$vehicle_temperature','$product_temperature','$delivery_note_number','$purchased_id')";
		$y = mysqli_query($conn, $x) or die(mysqli_error($conn));
		
		$id = mysqli_insert_id($conn);
		
		return $id;
		
	}
	
	
	
	 
	
	
	function intakeIDfromPalletID($id){
		global $conn;
		// ??: Why get everything if all we need is the intake_id?
		$x = "SELECT * FROM `pallet` WHERE id='$id'";
		$y = mysqli_query($conn, $x);
		$row = mysqli_fetch_array($y);
		
		return $row['intake_id'];
		
	}
	
	function getWeightOfProduct($product_id){ #kezkez
		global $conn;
		
		$x = "SELECT * FROM `weights` WHERE product_id='$product_id'";
		$y = mysqli_query($conn, $x);
		
		$value = 0;
		
		while($weights = mysqli_fetch_array($y)){
			// $value = $value + $weights['weight_gross'];
			
			if($weights['weight_tear'] !=''){
				$value = $value + ($weights['weight_gross'] - $weights['weight_tear']);
			}
		}
		
		return $value;
	}
	
	
	function getProductIDfromWeightID($weight_id){
		global $conn;
		
		$x = "SELECT * FROM `weights` WHERE id='$weight_id'";
		$y = mysqli_query($conn, $x);
		$row = mysqli_fetch_array($y);
		
		return $row['product_id'];
	}
	
	# returns count of weight entries for the products past in []
	function countFromProductIDArray($PRODUCT_IDS){
		global $conn;

		$PRODUCT_IDS = implode($PRODUCT_IDS, ',');

		$y = mysqli_query($conn, "SELECT id FROM `weights` WHERE product_id IN ($PRODUCT_IDS)");
		$count = mysqli_num_rows($y);

		return $count;
	}

	# returns total weight of products past in []
	function weightFromProductIDArray($PRODUCT_IDS){
		global $conn;

		$PRODUCT_IDS = implode($PRODUCT_IDS, ',');

		$y = mysqli_query($conn, "SELECT * FROM `weights` WHERE product_id IN ($PRODUCT_IDS)");

			
		$weight = 0;
		
		while($row = mysqli_fetch_array($y)){
			if($row['weight_tear'] == $row['weight_gross']){
				$w = $row['weight_gross'];
			}else{
				$w = $row['weight_gross'] - $row['weight_tear'];
			}
			
			$weight = $weight + $w;
		}
		
		return $weight;
	}
	

	# should swap all uses of this function to the one above
	function weightFromProductID($productID){
		global $conn;
		
		$x = "SELECT * FROM `weights` WHERE product_id = $productID";
		$y = mysqli_query($conn, $x);
		
		$weight = 0;
		
		while($row = mysqli_fetch_array($y)){
			if($row['weight_tear'] == $row['weight_gross']){
				$w = $row['weight_gross'];
			}else{
				$w = $row['weight_gross'] - $row['weight_tear'];
			}
			
			$weight = $weight + $w;
		}
		
		
		return $weight;
	}
	
	function weightSoldFromProductID($productID){
		global $conn;
		// ??: Assuming status_id 0 is available & 1 is sold, this checks for unsold instead of sold
		$x = "SELECT * FROM `weights` WHERE status_id != '1' && product_id = $productID";
		//$x = "SELECT * FROM `weights` WHERE product_id = $productID";
		$y = mysqli_query($conn, $x);
		
		$weight = 0;
		
		while($row = mysqli_fetch_array($y)){
			if($row['weight_tear'] == $row['weight_gross']){
				$w = $row['weight_gross'];
			}else{
				$w = $row['weight_gross'] - $row['weight_tear'];
			}
			
			$weight = $weight + $w;
		}
		
		
		return $weight;
    }

    function weightsAvailableOnProducts($productIDs){
        global $conn;

        $productIDs = implode(',', $productIDs);

        $x = "select count(*)
                    from
                    (
                      select count(id) tot
                      from weights 
                      where product_id in($productIDs)
                      group by id
                    ) src;";
        $y = mysqli_query($conn, $x);
        
        $row = mysqli_fetch_array($y);
        
        return $row[0];
    }

	function weightsAvailableOnProduct($productID){
		global $conn;
		
		$x = "SELECT id FROM `weights` WHERE status_id != '1' && product_id = $productID";
 		$y = mysqli_query($conn, $x);
		
		$count = mysqli_num_rows($y);
		
		return $count;
		
		
	}
	
    function getWeightFromProductID($productID){
		global $conn;
		
		$x = "SELECT * FROM `weights` WHERE product_id = $productID";
		$y = mysqli_query($conn, $x);
		
		$weight = 0;
		
		while($row = mysqli_fetch_array($y)){
			if($row['weight_tear'] == $row['weight_gross']){
				$w = $row['weight_gross'];
			}else{
				$w = $row['weight_gross'] - $row['weight_tear'];
			}
			
			$weight = $weight + $w;
		}
		
		
		return $weight;
	}
	
	# Generic get */1 record from any table
	function getData($table, $id = '#'){
		global $conn;
		
		if($id == '#'){
			$x = "SELECT * FROM `$table`";
		}else{
			$x = "SELECT * FROM `$table` WHERE id='$id'";
		}
		
		$y = mysqli_query($conn, $x);
		
		
		return $y;
	}

	
	# Login form
	# Just checking for a email/pass match. Passwords are sha1 encrypted
	function check_login($email, $password){
		global $conn;
		
		$email = mysqli_real_escape_string($conn, $_POST['email']);
		$password = sha1(mysqli_real_escape_string($conn, $_POST['password']));
		
		$x = "SELECT * FROM `users` WHERE email = '$email' && password = '$password' LIMIT 1";
		$y = mysqli_query($conn, $x);
		
		$row = mysqli_fetch_array($y);
		$count = mysqli_num_rows($y);
							
		if($count != 0){
			
			$_SESSION['USER'] = $row['id'];
			$result = 1;
			
		}else{
			$result = 0; # Should really do some error handling here
		}
		
		return $result;	
	
	}
	
	function deleteWeight($weightID){
		global $conn;
		
		$x = "DELETE FROM weights WHERE id='$weightID' LIMIT 1";
		$y = mysqli_query($conn, $x);
	}
	
	function deleteIntakeDoc($intakeid, $docid){
		global $conn;
		
		$x = "DELETE FROM intakeDocs WHERE id='$docid' && intakeid='$intakeid' LIMIT 1";
		$y = mysqli_query($conn, $x);
	}
	
	function deletePurchase($id){
		global $conn;
		
		$x = "DELETE FROM purchase_form WHERE id='$id' LIMIT 1";
		$y = mysqli_query($conn, $x);
	}
	
	# Get Cut name from id
	function getCut($id){
		global $conn;
		// ??: Why get everything if we only want the name?
		$x = "SELECT name FROM cuts WHERE id = '$id'";
		$y = mysqli_query($conn, $x);
		
		$row = mysqli_fetch_array($y);
		
		return $row['name']; 
	}
	
	
	function getTotesNumProductsForCutOnIntake($intake_id, $cut_id){
		global $conn;
		
		$x = "SELECT * FROM `pallet` WHERE intake_id ='$intake_id'";
		$y = mysqli_query($conn, $x);
		
		$totalNum = 0;
		
		while($palletRow = mysqli_fetch_array($y)){
			$pallet_id = $palletRow['id'];
			
			$x2 = "SELECT * FROM `product` WHERE pallet_id='$pallet_id' AND cut_id='$cut_id'";
			$y2 = mysqli_query($conn, $x2);
			
			
			 while($productRow = mysqli_fetch_array($y2)){
				$product_id = $productRow['id'];
				$x3 = "SELECT * FROM `weights` WHERE product_id='$product_id'";
				$y3 = mysqli_query($conn, $x3);
				
				$count = mysqli_num_rows($y3);
				
				$totalNum = $totalNum + $count;
			}
			
		}
		
		return $totalNum;
	}
	
	function getTotesWeightOfCutOnIntake($intake_id, $cut_id){
		global $conn;
		
		$x = "SELECT * FROM `pallet` WHERE intake_id ='$intake_id'";
		$y = mysqli_query($conn, $x);
		
		$totalWeight = 0;
		
		while($palletRow = mysqli_fetch_array($y)){
			$pallet_id = $palletRow['id'];
			
			$x2 = "SELECT * FROM `product` WHERE pallet_id='$pallet_id' AND cut_id='$cut_id'";
			$y2 = mysqli_query($conn, $x2);
			
			while($productRow = mysqli_fetch_array($y2)){
				$totalWeight = $totalWeight + (double) getTotalWeightOfProduct($productRow['id']);
			}
			
		}
		
		return $totalWeight;
	}
	
	function getTotalWeightOfProduct($product_id){
		global $conn;
		
		$xWeight = "SELECT * FROM `weights` WHERE product_id='$product_id'";
		$yWeight = mysqli_query($conn, $xWeight);
		
		$totalWeight = 0;
		
		while($rowWeight = mysqli_fetch_array($yWeight)){			
            if($rowWeight['weight_tear'] != '' && $rowWeight['weight_tear'] != $rowWeight['weight_gross']){
                $c = (double) $rowWeight['weight_gross'] - (double) $rowWeight['weight_tear'];
                $totalWeight = $totalWeight +   $c;
            }else{
                $totalWeight =  $totalWeight + (double) $rowWeight['weight_gross'];
            }
		}
		
		
		return $totalWeight;
    }

    function countNumProductsForCutOnPallet($pallet_id, $cut_id){
        global $conn;

        $x = "SELECT COUNT(weights.id) as count FROM `weights` INNER JOIN `product` ON weights.product_id=product.id WHERE product.cut_id='$cut_id' && product.pallet_id='$pallet_id' && weights.status_id != '1'";
        $y = mysqli_query($conn, $x);
        $row = mysqli_fetch_array($y);
    
        return $row['count'];
    }
 

    function countNumProductsForCutOnPalletArrays($palletIDS, $cutIDS, $nationalityID){
        global $conn;
 
        
        $palletIDS = implode(',', $palletIDS);
        $cutIDS = implode(',', $cutIDS);

        $x = "SELECT COUNT(weights.id) as num FROM `weights` INNER JOIN `product` ON weights.product_id=product.id WHERE product.cut_id IN ($cutIDS) && product.pallet_id IN ($palletIDS) && weights.status_id != 1 && product.nationality_id='$nationalityID'";
		$y = mysqli_query($conn, $x);
        $row = mysqli_fetch_array($y);
	

		if($row["num"] == 0)
		{
			return 0;
		}

        $x1 = "SELECT pickerItems.id, product.id AS productid  FROM `pickerItems` INNER JOIN `product` ON pickerItems.product_id=product.id && pickerItems.deleted !=1 && pickerItems.status = '0' && product.pallet_id IN ($palletIDS) && product.cut_id IN ($cutIDS)";
        $y1 = mysqli_query($conn, $x1);
		$numInPicking = mysqli_num_rows($y1);
		
        return $row['num'] - $numInPicking;
    }

	function numWeightsAvailableFromProductID($product_id){
		global $conn;


        $x = "SELECT COUNT(weights.id) as num FROM `weights` INNER JOIN `product` ON weights.product_id=product.id WHERE product.id = $product_id && weights.status_id != 1";
		$y = mysqli_query($conn, $x);
		$row = mysqli_fetch_array($y);

		$x1 = "SELECT id FROM `pickerItems` WHERE product_id='$product_id' && status = '0' && deleted !=1";
		$y1 = mysqli_query($conn, $x1);
		$numInPicking = mysqli_num_rows($y1);
		
		return $row['num'] - $numInPicking;
	}


    function totalWeightOfAdvisedKGProduct($intake_id, $nationalityID){
        global $conn;
        
        $x = "SELECT id FROM `pallet` WHERE intake_id='$intake_id'";
        $y = mysqli_query($conn, $x);
        $countPallets = mysqli_num_rows($y);
        
        $qPallets = '';
        
        while($row = mysqli_fetch_array($y)){
            $rowid = $row['id'];
            
            $qPallets .= " pallet_id = '$rowid' OR";
        }
        
        $qPallets = substr($qPallets, 0, -2);
        
        
        $t_count = 0;
        $q = "SELECT * FROM product WHERE nationality_id=$nationalityID AND (" . $qPallets . ")";
        $countQuery = mysqli_query($conn, $q);

        while($countRow = mysqli_fetch_array($countQuery)){
            $t_count += $countRow['akg'];
        }

        return $t_count;
    }
    function totalWeightOfProduct($productIDS){
        global $conn;
                
        $productIDS = implode(',', $productIDS);



        $x = "SELECT * FROM `weights` WHERE status_id != '1' && product_id IN ($productIDS)";
		$y = mysqli_query($conn, $x);
		
		$weight = 0;
		
		while($row = mysqli_fetch_array($y)){
			if($row['weight_tear'] == $row['weight_gross']){
				$w = $row['weight_gross'];
			}else{
				$w = $row['weight_gross'] - $row['weight_tear'];
			}
			
			$weight = $weight + $w;
		}
		
		
		return $weight;
        
    }


    function countNumProductsForCutOnPalletThatIsntPicked($pallet_id,$cut_id){
        global $conn;

	    //SELECT pickerItems.id FROM `pickerItems` INNER JOIN `product` ON pickerItems.product_id=product.id
        $x1 = "SELECT pickerItems.id, product.id AS productid  FROM `pickerItems` INNER JOIN `product` ON pickerItems.product_id=product.id && product.pallet_id='$pallet_id' && product.cut_id='$cut_id'";
        $y1 = mysqli_query($conn, $x1);
        $numInPicking = mysqli_num_rows($y1);

        $xBit = '';
        while($row = mysqli_fetch_array($y1)){
            $productid = $row['productid'];

            $xBit .= "product_id='$productid' ||";
        }
        $xBit = substr($xBit, 0, -2);

        $x2 = "SELECT id FROM `weights` WHERE status_id != '1' && ($xBit)";
        
        $y2 = mysqli_query($conn, $x2);
        $f = mysqli_num_rows($y2);
        $numAvailable = $f - $numInPicking;
        
        return $numAvailable; 
    }
 
    
    function getTotalNumOfWeights($intake_id, $cut_id){
		global $conn;
		
		$x11 = "SELECT * FROM `pallet` WHERE intake_id = $intake_id";
		$y11 = mysqli_query($conn, $x11);
		
 		$count = 0;
		while($pallet = mysqli_fetch_array($y11)){
			
			$pallet_id = $pallet['id'];
			 
			$x = "SELECT * FROM `product` WHERE pallet_id='$pallet_id' && cut_id='$cut_id'";
            $y = mysqli_query($conn, $x);
            
             
            while($row = mysqli_fetch_array($y)){
                $product_id = $row['id'];
                
                $x1 = "SELECT * FROM `pickerItems` WHERE product_id = '$product_id'";
                $y1 = mysqli_query($conn, $x1);

                $numInPicking = mysqli_num_rows($y1);
                
                // $x2 = "SELECT * FROM `weights` WHERE product_id='$product_id' && status_id='0'";
                $x2 = "SELECT * FROM `weights` WHERE status_id != '1' && product_id='$product_id'";
                $y2 = mysqli_query($conn, $x2);

                $f = mysqli_num_rows($y2);

                $count = $count + $f;

                $count = $count - $numInPicking;
            }
            
             
		}
		return $count;
 	}

	function areWeightsAllTheSame($product_id){
		global $conn;
		
		$x = "SELECT * FROM `weights` WHERE product_id='$product_id' GROUP BY weight_gross";
		$y = mysqli_query($conn, $x);
		
		$count = mysqli_num_rows($y);
		
		return $count;
		
	}
	
	
	# Get Species name from id
	function getSpecies($id){
		global $conn;
		
		$x = "SELECT * FROM species WHERE id = '$id'";
		$y = mysqli_query($conn, $x);
		
		$row = mysqli_fetch_array($y);
		
		return $row['name']; 
	}

	function getSpeciesFromCut($cut_id){
		global $conn;
		
		$x = "SELECT * FROM cuts WHERE id = '$cut_id'";
		$y = mysqli_query($conn, $x);
		
		$row = mysqli_fetch_array($y);
		
		return $row['species_id']; 
	}
	
	function getSpeciesFromCutID($cut_id){
		$speciesid = getSpeciesFromCut($cut_id);
		
		
		$name = getSpecies($speciesid);
		
		return $name;
	}
	
	// ??: Should be renamed or return full nationality entry
	# Get nationality name from id
	// cache the results
	$nationalities = [];
	function getNationality($id){
		global $conn;
		global $nationalities;

		$result = searchInNestedArray($nationalities, "id", $id);
		
		if($result)
		{
			return $result['name'];
		}
		
		$x = "SELECT * FROM nationality";
		$y = mysqli_query($conn, $x);
		$nationalities = mysqli_fetch_all($y, MYSQLI_ASSOC);
		
		$result = searchInNestedArray($nationalities, "id", $id);
		
		if($result)
		{
			return $result['name'];
		}

		return null; 
	}

	# Get Temp - returns temp text for specific tempid
	$temperatures = [];
	function getTemp($tempid){
		global $conn;
		global $temperatures;

		$result = searchInNestedArray($temperatures, "id", $tempid);
		
		if($result)
		{
			return $result['temperature'];
		}
		
		$x = "SELECT * FROM temperature";
		$y = mysqli_query($conn, $x);
		$temperatures = mysqli_fetch_all($y, MYSQLI_ASSOC);

		$result = searchInNestedArray($temperatures, "id", $tempid);
		
		if($result)
		{
			return $result['temperature'];
		}
		return null;
	}

	# Get brand name from id
	$brands = [];
	function getBrand($id){
		global $conn;
		global $brands;

		$result = searchInNestedArray($brands, "id", $id);
		
		if($result)
		{
			return $result['name'];
		}

		$x = "SELECT * FROM brands";
		$y = mysqli_query($conn, $x);
		
		$brands = mysqli_fetch_all($y, MYSQLI_ASSOC);
		
		$result = searchInNestedArray($brands, "id", $id);
		
		if($result)
		{
			return $result['name'];
		}
		return null; 
	}

	function searchInNestedArray($array, $field, $value)
	{
		$result = null;

		foreach ($array as $key => $val) {
			if ($val[$field] === $value) {
				$result = $val;
				return $result;
			}
		}
	}
	
	# weight of product
	function weightOfProduct($product_id){
		global $conn;
		
		$x = "SELECT * FROM `weights` WHERE product_id = '$product_id'";
		$y = mysqli_query($conn, $x);
		$row = mysqli_fetch_array($y);
		
		return $row['weight_gross']; # I dont think this is right..
	}
	
	
	# weight type of product
	function weightTypeOfProduct($product_id){
		global $conn;
		
		$x = "SELECT * FROM `boxes` WHERE product_id = '$product_id'";
		$y = mysqli_query($conn, $x);
		$row = mysqli_fetch_array($y);
		
		return $row['unit']; 
	}
	
	# convert type to name
	function getTypeName($unit){
		if($unit == 'PP'){
			$unittext = 'Packet';
		}else if ($unit == 'C'){
			$unittext = 'Case';
		}else if($unit == 'P'){
			$unittext = 'Pallet';
		}else{
			$unittext = $unit;
		}
		
		return $unittext;
	}
	
	# temp mauybe
	function getWeightFor($product_id){
		global $conn;
		$weight = 0;
		
		$x = "SELECT * FROM `boxes` WHERE product_id = '$product_id'";
		$y = mysqli_query($conn, $x);
		
		while($row = mysqli_fetch_array($y)){
			$weight = $weight + (int) $row['weight']; 
		}
		
		return $weight;
	}
	
	# Get Supplier name from id
	function supplierName($id){
		global $conn;
		
		$x = "SELECT * FROM supplier WHERE id = '$id'";
		$y = mysqli_query($conn, $x);
		
		$row = mysqli_fetch_array($y);
		
		return $row['name']; 
	}
	
	
	function customerName($id){
		global $conn;
		
		$x = "SELECT * FROM customers WHERE id = '$id'";
		$y = mysqli_query($conn, $x);
		
		$row = mysqli_fetch_array($y);
		
		return $row['businessname']; 
	}
	
	
	# Get Intake - expects 1 param, intake_id
	function getIntake($id){
		global $conn;
		
		$x = "SELECT * FROM `intake` WHERE id='$id'";
		$y = mysqli_query($conn, $x);
		
		$row = mysqli_fetch_array($y);
		
		return $row;
		
	}
	
	# Get Supplier - expects 1 param, supplier_id
	function getCustomer($id){
		global $conn; 
		
		$x = "SELECT * FROM `customers` WHERE id = '$id'";
		$y = mysqli_query($conn, $x);
		
		$row = mysqli_fetch_array($y);
		
		return $row;
	}
	
	# Get Supplier - expects 1 param, supplier_id
	function getSupplier($id){
		global $conn; 
		
		$x = "SELECT * FROM `supplier` WHERE id = '$id'";
		$y = mysqli_query($conn, $x);
		
		$row = mysqli_fetch_array($y);
		
		return $row;
	}
	
	function getCuts(){
		global $conn;
		
		
		$x = "SELECT * FROM cuts ORDER BY name ASC";
		$y = mysqli_query($conn, $x) or die(mysqli_error($conn));
		
		return $y;
	}
	
	function getCutsFor($species){
		global $conn;
		
		
		$x = "SELECT * FROM cuts WHERE species_id='$species' ORDER BY name ASC";
		$y = mysqli_query($conn, $x) or die(mysqli_error($conn));
		
		return $y;
	}
	
	# Get Boxes For - returns boxes for specific product_id
	function getBoxesFor($product_id){
		global $conn;
		
		$x = "SELECT * FROM boxes WHERE product_id = '$product_id'";
		$y = mysqli_query($conn, $x);
		$row = mysqli_fetch_array($y);
		return $row;
	}
	
	# Get Username - returns username for specific userid
	function getUsername($userid){
		global $conn;
		
		$x = "SELECT * FROM users WHERE id = '$userid'";
		$y = mysqli_query($conn, $x);
		$row = mysqli_fetch_array($y);
		return $row['name'];
	}
	
	function deleteProductEntry($product_id){
		global $conn;
		
		$x = "DELETE FROM `product` WHERE id = '$product_id'";
		$y = mysqli_query($conn, $x);
	}

	# Delete Boxes for specific product_id
	function deleteWeightsFor($product_id){
		global $conn;
		
		$x = "DELETE FROM `weights` WHERE product_id = '$product_id'";
		$y = mysqli_query($conn, $x);
	}
	
	# Delete products for specific pallet_id
	function deleteProductsFor($pallet_id){
		global $conn;
		
		$x = "DELETE FROM `product` WHERE pallet_id = '$pallet_id'";
		$y = mysqli_query($conn, $x);
	}
	
	# Delete pallet
	function deletePallet($pallet_id){
		global $conn;
		
		$x = "DELETE FROM `pallet` WHERE id = '$pallet_id'";
		$y = mysqli_query($conn, $x);
	}
	
	# Add new Intake
	function addIntakeDupe($supplier_id, $date_received, $vehicle_reg, $vehicle_temperature, $product_temperature, $delivery_note_number, $staff_id, $security_id, $purchase_id){
		global $conn;
		
		if($purchase_id != '#'){
			$x = "INSERT into `intake` (supplier_id,security_id, date_received, vehicle_reg, vehicle_temperature,product_temperature,delivery_note_number,user_id,purchase_id) 
			VALUES ('$supplier_id','$security_id','$date_received','$vehicle_reg','$vehicle_temperature','$product_temperature','$delivery_note_number','$staff_id','$purchase_id')";
		}else{
			$x = "INSERT into `intake` (supplier_id,security_id, date_received, vehicle_reg, vehicle_temperature,product_temperature,delivery_note_number,user_id) 
			VALUES ('$supplier_id','$security_id','$date_received','$vehicle_reg','$vehicle_temperature','$product_temperature','$delivery_note_number','$staff_id')";
		}
		
		$y = mysqli_query($conn, $x) or die(mysqli_error($conn));
		
		$v = mysqli_insert_id($conn);
		return $v;
	}
	
	function addReturnIntake($supplier_id, $date_received, $vehicle_reg, $vehicle_temperature, $product_temperature, $delivery_note_number, $staff_id, $security_id, $purchase_id){
		global $conn;
		
		if($purchase_id != '#'){
			$x = "INSERT into `intake` (returned, supplier_id,security_id, date_received, vehicle_reg, vehicle_temperature,product_temperature,delivery_note_number,user_id,purchase_id) 
			VALUES (1,'$supplier_id','$security_id','$date_received','$vehicle_reg','$vehicle_temperature','$product_temperature','$delivery_note_number','$staff_id','$purchase_id')";
		}else{
			$x = "INSERT into `intake` (returned, supplier_id,security_id, date_received, vehicle_reg, vehicle_temperature,product_temperature,delivery_note_number,user_id) 
			VALUES (1,'$supplier_id','$security_id','$date_received','$vehicle_reg','$vehicle_temperature','$product_temperature','$delivery_note_number','$staff_id')";
		}
		
		$y = mysqli_query($conn, $x) or die(mysqli_error($conn));
		
		$v = mysqli_insert_id($conn);
		return $v;
	}
	
	function getSecurityName($id){
		global $conn;
		
		$x = "SELECT * FROM `security` WHERE id='$id'";
		$y = mysqli_query($conn, $x);
		$row = mysqli_fetch_array($y);
		
		return $row['name'];
	}
	
	
 
	
	function countTypeOnIntake($intake_id, $unit){
		global $conn;
		
		$intake_id = mysqli_real_escape_string($conn, $intake_id);
		$unit = mysqli_real_escape_string($conn, $unit);
		
		$x = "SELECT * FROM `pallets` WHERE intake_id='$intake_id' AND unit='$unit'";
		$y = mysqli_query($conn, $x);
		
		$row = mysqli_fetch_array($y);
		
		$count = mysqli_num_rows($y);
		
		return $count;
		
	}
	
	function getTypeOnIntake($intake_id, $unit){
		global $conn;
		
		$intake_id = mysqli_real_escape_string($conn, $intake_id);
		$unit = mysqli_real_escape_string($conn, $unit);
		
		$x = "SELECT * FROM `pallets` WHERE intake_id='$intake_id' AND unit='$unit'";
		$y = mysqli_query($conn, $x);
		
		return $y;
	}
 
	
	function weightFromWeight($weightID){
		global $conn;
		
		$x = "SELECT * FROM `weights` WHERE id='$weightID'";
		$y = mysqli_query($conn, $x);
		$row = mysqli_fetch_array($y);
		
		if($row['weight_tear'] != ''){
			return $row['weight_gross'];
		}else{
			return ($row['weight_gross'] - $row['weight_tear']);
		}
	}
	  
	function deleteIntake($id){
		global $conn;
		
		$x = "DELETE FROM `intake` WHERE id='$id'";
		$y = mysqli_query($conn, $x);
		
	}
	
	function getPallet($id){
		global $conn;
		
		$x = "SELECT * FROM `pallet` WHERE id='$id'";
		$y = mysqli_query($conn, $x);
		
		$row = mysqli_fetch_array($y);
		
		return $row;	
	}
	
	function getPalletsOnThisIntake($intake_id){
		global $conn;
		
		$x = "SELECT * FROM `pallet` WHERE intake_id='$intake_id'";
		$y = mysqli_query($conn, $x);
		
		return $y;
	}
	
	
	function getPalletsOnThisIntake2($intake_id){
		global $conn;
		
		$xKez = "SELECT id FROM `pallet` WHERE intake_id='$intake_id'";
		$yKez = mysqli_query($conn, $xKez) or die(mysqli_error($conn));
		
		$counter = mysqli_num_rows($yKez);
		
		if($counter > 0){
			$ids = '';
			
			
			while($allPallets = mysqli_fetch_array($yKez)){
				$palletid = $allPallets['id'];
				
				$x1Kez = "SELECT id FROM product WHERE pallet_id='$palletid'";
				$y1Kez = mysqli_query($conn, $x1Kez) or die(mysqli_error($conn));
				
				$count = mysqli_num_rows($y1Kez);
				
				if($count > 0){
					$ids .= ' id ='. $palletid . ' ||';
				}
				
			}
			
			
			
			$ids = substr($ids, 0, -3);
			
			$x2Kez = "SELECT * FROM `pallet` WHERE $ids";
			$y2Kez = mysqli_query($conn, $x2Kez) or die(mysqli_error($conn));
		}else{
			$y2Kez = null;
		}
		return $y2Kez;
	}
	
	
	function addIntake($supplier_id, $date_received, $vehicle_reg, $vehicle_temperature){
		global $conn;
		
		$x = "INSERT into `intake` (supplier_id, date_received, vehicle_reg, vehicle_temperature) VALUES ('$supplier_id','$date_received','$vehicle_reg','$vehicle_temperature')";
		$y = mysqli_query($conn, $x);
	}
	

	
	function getPallets($id){
		global $conn; 
		
		$x = "SELECT * FROM `pallets` WHERE intake_id = '$id'";
		$y = mysqli_query($conn, $x);
		
		
		return $y;
	}
	
	
	
	function getStatus($id){
		global $conn;
		
		$x = "SELECT * FROM statuses WHERE id = '$id'";
		$y = mysqli_query($conn, $x);
		
		$row = mysqli_fetch_array($y);
		
		return $row['status_name']; 
	}
	

    function cutsFromCutGroup($species_id, $cutgroup_id){
        global $conn;

        $x = "SELECT id from `cuts` WHERE species_id='$species_id' && cutgroup_id='$cutgroup_id'";
        $y = mysqli_query($conn, $x);
        
        $data = [];
        
        while($row = mysqli_fetch_array($y)){
            array_push($data, $row['id']);
        }

        return $data;
    }


    function palletIDsFromIntakeID($intake_id){
        global $conn;

        $x = "SELECT id from `pallet` WHERE intake_id='$intake_id'";
        $y = mysqli_query($conn, $x);
        
        $data = [];
        
        while($row = mysqli_fetch_array($y)){
            array_push($data, $row['id']);
        }

        return $data;
    }


    function intakePriceComplete($intake_id){
        global $conn;

        $r = 1;

        $x = "SELECT id FROM `pallet` WHERE intake_id='$intake_id'";
        $y = mysqli_query($conn, $x);
        $countPallets = mysqli_num_rows($y);
        
        if($countPallets == 0){
            $r = 0;
        }else{
            $palletIDs = [];
            while($row = mysqli_fetch_array($y)){ array_push($palletIDs, $row['id']); }
            
            $palletString = implode(',', $palletIDs);
            

            $x = "SELECT * FROM product WHERE pallet_id IN ($palletString) GROUP BY cut_id";
            $y = mysqli_query($conn, $x);
            
            while($row = mysqli_fetch_array($y)){
                if($row['cost'] == 0){
                    $r = 0;
                }
            }
        }

        return $r;
    }
	
?>