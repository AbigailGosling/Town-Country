<?php
	ini_set('session.gc_maxlifetime', 3600);
	session_start();
    ini_set('post_max_size', '64M');
    ini_set('upload_max_filesize', '64M');
	
	require('config.php');

	$conn = mysqli_connect($dbHost,$dbUser,$dbPass,$dbName);
	$mysqli = new mysqli($dbHost,$dbUser,$dbPass,$dbName); 


	error_reporting(0);
 
	$userid = $_SESSION['USER'];
	
	
	$pageName = $_SERVER['REQUEST_URI'];
	
	$exit = 1;
	switch ($pageName)
	{
		case '/':
		case '/index.php':
		case '/script_login.php':
		case '/ajax/generatePDFstatement2.php':
		case '/scripts/SLabsNotifier.php':
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

	function averageDaysUntilPaidForCustomer($customer_id){
        global $conn;
        
        $invoice_payment_times = [];

        $invoiceResults = mysqli_query($conn, "SELECT date_completed,id FROM `pickerSheets` WHERE completed ='1' && customer_id='$customer_id'");

        // loop through all completed invoices associated with this customer_id
        while($invoice = mysqli_fetch_array($invoiceResults)){
            $invoice_id = $invoice['id'];
            $date_completed = $invoice['date_completed'];
            
            // get the final payment for this invoice
            $paymentResult = mysqli_query($conn, "SELECT created_at,id FROM `invoice_payments` WHERE invoice_id='$invoice_id' ORDER BY created_at DESC LIMIT 1");
            $count = mysqli_num_rows($paymentResult);

            // check we have a payment record, old completed invoices do not have any
            if($count == 1){
                $paymentData = mysqli_fetch_array($paymentResult);

                $fully_paid_date = $paymentData['created_at'];
                
                $difference = abs(strtotime($fully_paid_date) - strtotime($date_completed));
                
                $years = floor($difference / (365*60*60*24));
                $months = floor(($difference - $years * 365*60*60*24) / (30*60*60*24));

                $days = round(($difference - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24),1);

                // store the number of days it took to fully pay the invoice inside an array
                array_push($invoice_payment_times, $days);
            }
        }
        
        if(empty($invoice_payment_times)){
            return null;
        }

        $average_days_until_paid = round(array_sum($invoice_payment_times) / count($invoice_payment_times), 1);

        return $average_days_until_paid;
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

		# Ash Request - If a pallet is marked as available (0), the tampered status should be reset (0)
		if($status == 0){
			$tampered = 0;
		}else{
			$tampered = 1;
		}

		# Get all product IDS for this pallet & store in array
		$productsResult = mysqli_query($conn, "SELECT id FROM `product` WHERE pallet_id='$pallet_id'");
		while($product = mysqli_fetch_array($productsResult)){ array_push($productIDS, $product['id']); }
		$productIDS = implode(',', $productIDS);


		$weightsResult = mysqli_query($conn, "UPDATE `weights` SET status_id='$status', tampered=$tampered WHERE product_id IN ($productIDS)");
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
	
	function createPurchase($supplier_id,$transportation, $speciesString,$cutString,$priceString,$unitsString, $date_purchased, $purchased_by, $date_due, $purchase_comments, $file_name, $booking_ref_number, $haulier, $direct_drop, $temperature_id){
		global $conn;
		
		$x = "INSERT into purchase_form (supplier_id,species,cut,price,units,date_purchased,purchased_by,date_due,purchase_comments,dfile,booking_ref_number,transportation,haulier,direct_drop,temperature_id) 
		VALUES ('$supplier_id','$speciesString','$cutString','$priceString','$unitsString','$date_purchased','$purchased_by','$date_due','$purchase_comments','$file_name','$booking_ref_number','$transportation','$haulier','$direct_drop','$temperature_id')";
		$y = mysqli_query($conn, $x) or die(mysqli_error($conn));
         
		$id = mysqli_insert_id($conn);
		
		return $id;
	}
	
	
	function updatePurchase($id, $transportation, $supplier_id,$speciesString,$cutString,$unitsString, $priceString, $date_purchased, $purchased_by, $date_due, $purchase_comments, $file_name, $booking_ref_number,$haulier, $direct_drop, $temperature_id){
		global $conn;
		
		$x ="UPDATE `purchase_form` SET transportation='$transportation', supplier_id='$supplier_id',species='$speciesString', cut='$cutString',units='$unitsString', price='$priceString', date_purchased='$date_purchased',purchased_by='$purchased_by',date_due='$date_due',
            purchase_comments='$purchase_comments'";
            
        if($file_name != ''){
            $x .=",dfile='$file_name'";
		}
		
		if($temperature_id != ''){
            $x .=",temperature_id='$temperature_id'";
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
		global $mysqli;
		$email = $mysqli->real_escape_string($email);
		$password = sha1($mysqli->real_escape_string($password));

		$stmt = $mysqli->prepare("SELECT * FROM `users` WHERE `email` = ? && `password` = ? LIMIT 1");
		$stmt->bind_param('ss', $email, $password);
		
		$stmt->execute();
		
		$result = $stmt->get_result();
		$count = $result->num_rows;
		$row = $result->fetch_assoc();
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
		
		$x = "SELECT * FROM `product` WHERE id = '$product_id'";
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

	# Get Count Customer Sales By Salesman - expects 2 param, customer_id, user_id
	function countCustomerSalesBySalesman($customer_id, $user_id){
		global $conn;

		$queryResult = mysqli_query($conn, "SELECT COUNT(id) as count FROM `pickerSheets` WHERE user_from_id='$user_id' && customer_id='$customer_id'");

		$countData = mysqli_fetch_array($queryResult);

		return $countData['count'];
	}
	
	# Get Outstanding Picksheet Total Price - expects 1 param, picksheet_id
	function getOutstandingPicksheetTotal($picksheet_id){
		global $conn;

		$customerPicksheets = mysqli_query($conn, "SELECT pickerSheets.id, SUM(invoice_payments.amount) as paid FROM `pickerSheets` left join invoice_payments on pickerSheets.id = invoice_payments.invoice_id WHERE (pickerSheets.completed = 1 AND pickerSheets.id=$picksheet_id) GROUP by pickerSheets.id");

		$totalOutstanding = 0.00;

		$picksheet = mysqli_fetch_array($customerPicksheets);
		$this_price = (float) invoiceTotal($picksheet['id']);

		$epsilon = 0.00001;
		if(($this_price - $picksheet['paid']) <= $epsilon){
			$totalOutstanding = (float) 0;
		}else{
			$totalOutstanding = (float) $this_price - $picksheet['paid'];
		}
		
		return number_format((float)$totalOutstanding, 2, '.', '');
	}

	function getChargedPicksheetTotalList($picksheet_ids){
		global $conn;

		$customerPicksheets = mysqli_query($conn, "SELECT pickerSheets.id, SUM(invoice_payments.amount) as paid FROM `pickerSheets` left join invoice_payments on pickerSheets.id = invoice_payments.invoice_id WHERE (pickerSheets.completed = 1 AND pickerSheets.id IN (".implode(",",$picksheet_ids).")) GROUP by pickerSheets.id");

		$this_price = 0.00;

		while($picksheet = mysqli_fetch_array($customerPicksheets))
		{
			$this_price = $this_price + (float) invoiceTotal($picksheet['id']);
		}

		return $this_price;
	}
	function getPaidPicksheetTotalList($picksheet_ids){
		global $conn;
		$picksheetsResult = mysqli_query($conn, "SELECT SUM(amount) as paid FROM `invoice_payments` WHERE invoice_id IN (".implode(",",$picksheet_ids).")");
		$data = mysqli_fetch_array($picksheetsResult);

		if($data['paid'] == null){ return 0; }

		return $data['paid'];
	}
	function getTotalPaidByCustomerIDForUserID($customer_id, $user_id){
		global $conn;

		$customerPicksheets = mysqli_query($conn, "SELECT pickerSheets.*, SUM(invoice_payments.amount) as paid FROM `pickerSheets` left join invoice_payments on invoice_payments.payment_method != 'CREDIT_NOTE' && pickerSheets.id = invoice_payments.invoice_id WHERE (pickerSheets.completed = 1 AND pickerSheets.customer_id=$customer_id AND pickerSheets.user_from_id='$user_id')");

		$data = mysqli_fetch_array($customerPicksheets);

		if($data['paid'] == null){ return 0; }

		return $data['paid'];
	}

	function getTotalPaidByCustomerIDForUserWithinDates($customer_id, $user_id, $date_start, $date_end){
		global $conn;
		$dateQueryPiece = "";
		if ($date_start != "") $dateQueryPiece .= " && pickerSheets.date_completed >= '$date_start'";
		if ($date_end   != "") $dateQueryPiece .= " && pickerSheets.date_completed <= '$date_end'";

		$picksheetsResult = mysqli_query($conn, "SELECT GROUP_CONCAT(id) as ids FROM `pickerSheets` WHERE completed=1 && customer_id=$customer_id $dateQueryPiece");
		$picksheetData = mysqli_fetch_array($picksheetsResult);

		$pick_ids = $picksheetData['ids'];

 		$customerPicksheets = mysqli_query($conn, "SELECT pickerSheets.id, SUM(invoice_payments.amount) as paid FROM `pickerSheets` left join invoice_payments on pickerSheets.id = invoice_payments.invoice_id WHERE (pickerSheets.id in ('$pick_ids')) GROUP by pickerSheets.id");

		$data = mysqli_fetch_array($customerPicksheets);

		if($data['paid'] == null){ return 0; }

		return $data['paid'];

	}

	# Get Picksheet Total Paid - expects 1 param, picksheet_id
	function getPicksheetTotalPaid($picksheet_id){
		global $conn;

		$customerPicksheets = mysqli_query($conn, "SELECT SUM(invoice_payments.amount) as paid FROM `pickerSheets` left join invoice_payments on pickerSheets.id = invoice_payments.invoice_id WHERE (pickerSheets.completed = 1 AND pickerSheets.id=$picksheet_id AND invoice_payments.payment_type !=  'CREDIT_NOTE') GROUP by pickerSheets.id");

		$picksheet = mysqli_fetch_array($customerPicksheets);
 
		$totalPaid = (float) $picksheet['paid'];

		return $totalPaid;
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
	  
	function deleteIntake($intake_id){
		global $conn;

		$pallet_ids = array();
		$product_ids = array();
		$weight_ids = array();

		$palletsResult = mysqli_query($conn, "SELECT id FROM `pallet` WHERE intake_id='$intake_id'");
		while($pallet = mysqli_fetch_array($palletsResult)){ array_push($pallet_ids, $pallet['id']); }

		$temp_pallet_ids = implode(',', $pallet_ids);

		$productsResult = mysqli_query($conn, "SELECT id FROM `product` WHERE pallet_id IN ($temp_pallet_ids)");
		while($product = mysqli_fetch_array($productsResult)){ array_push($product_ids, $product['id']); }

		$temp_product_ids = implode(',', $product_ids);

		$weightsResult = mysqli_query($conn, "SELECT id FROM `weights` WHERE product_id IN ($temp_product_ids)");
		while($weight = mysqli_fetch_array($weightsResult)){ array_push($weight_ids, $weight['id']); }

		
		$pallet_ids = implode(',', $pallet_ids);
		$product_ids = implode(',', $product_ids);
		$weight_ids = implode(',', $weight_ids);

		// Delete related pallets
		$deletePalletResult = mysqli_query($conn, "DELETE FROM `pallet` WHERE id IN ($pallet_ids)");

		// Delete related products
		$deleteProductResult = mysqli_query($conn, "DELETE FROM `product` WHERE id IN ($product_ids)");

		// Delete related weight entries
		$deleteWeightsResult = mysqli_query($conn, "DELETE FROM `weights` WHERE id IN ($weight_ids)");

		// Delete the intake 
		$deleteIntakeResult = mysqli_query($conn, "DELETE FROM `intake` WHERE id = '$intake_id'");
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


	function getCutGroupNameFromCut($cut_id){
		global $conn;

		$cutResult = mysqli_query($conn, "SELECT cutgroup_id FROM `cuts` WHERE id=$cut_id");
		$cutData = mysqli_fetch_array($cutResult);
		$cutgroup_id = $cutData['cutgroup_id'];

		$cutGroupResult = mysqli_query($conn, "SELECT `name` FROM cutgroups WHERE id='$cutgroup_id'");
		$cutGroupData = mysqli_fetch_array($cutGroupResult);

		return $cutGroupData['name'];
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


	function getPalletCollection($pallet_id){

    	global $conn;

        $x = "SELECT intake_id FROM `pallet` WHERE id = '$pallet_id'";
        $y = mysqli_query($conn, $x);

        $intake_id = null;
        
        while($row = mysqli_fetch_array($y)){
            $intake_id = $row['intake_id'];
        }

        if(!empty($intake_id)){

        	return palletIDsFromIntakeID($intake_id);

        }else{

        	return null;
		}
    }

    function checkForSoldPallets($pallets){

    	$pallet_ids = implode(',', $pallets);

    	global $conn;

        $x = "SELECT * from `product` inner join `weights` on product.id = weights.product_id WHERE product.pallet_id in (".$pallet_ids.") AND weights.status_id = 1";
        $y = mysqli_query($conn, $x);
        return $y->num_rows;
        
    }
	
	function productCountOnIntakeNotCosted($intake_id){
        global $conn;

		$palletResult = mysqli_query($conn, "SELECT GROUP_CONCAT(id) AS ids FROM pallet WHERE intake_id='$intake_id'");
		$palletData = mysqli_fetch_array($palletResult);
		$pallet_ids = $palletData['ids'];		

		$productResult = mysqli_query($conn, "SELECT count(id) as count FROM product WHERE (cost is null && pallet_id IN ($pallet_ids)) || (cost = '0.00' && pallet_id IN ($pallet_ids))");
		$productData = mysqli_fetch_array($productResult);

		return $productData['count'];
	}

	function totalOutstandingForCustomer($customer_id){
		global $conn;

		$customerPicksheets = mysqli_query($conn, "SELECT pickerSheets.*, SUM(invoice_payments.amount) as paid FROM `pickerSheets` left join invoice_payments on invoice_payments.payment_method != 'CREDIT_NOTE' && pickerSheets.id = invoice_payments.invoice_id WHERE (pickerSheets.completed = 1 AND pickerSheets.customer_id=$customer_id) GROUP by pickerSheets.id ORDER BY pickerSheets.id ASC");
		    
		$totalOutstanding = 0.00;

		while($picksheet = mysqli_fetch_array($customerPicksheets)){
			$total_credit = totalValueCreditedOnInvoiceID($picksheet['id']);
			$this_price = (float) invoiceTotal($picksheet['id']);

			$epsilon = 0.00001;
			if(($this_price - $picksheet['paid']) <= $epsilon){
				$currentOutstanding = (float) $this_price - $picksheet['paid'] - $total_credit;
			}else{
				$currentOutstanding = (float) $this_price - $picksheet['paid'] - $total_credit;
			}
			
			$totalOutstanding += $currentOutstanding;
		}

		return number_format((float)$totalOutstanding, 2, '.', '');
	}

	function invoiceTotal($pickersheet_id){
		global $conn;

		$outpalletQuery = "SELECT * FROM `palletsOut` WHERE pickersheet_id='$pickersheet_id'";
		$outpalletResult2 = mysqli_query($conn, $outpalletQuery);
                
		$outpalletCount = mysqli_num_rows($outpalletResult2);


		while($outpallet = mysqli_fetch_array($outpalletResult2)){
			$weightids = explode(',', $outpallet['weight_ids']);
 
			$productIDArray = array();
			
			$x = "SELECT * FROM `weights` WHERE id IN (".$outpallet['weight_ids'].")";
			$y = mysqli_query($conn, $x);
			$weightsByProductID = array();
			while($weight = mysqli_fetch_array($y))
			{
				if(!in_array($weight['product_id'], $productIDArray)){
					array_push($productIDArray, $weight['product_id']);
					$weightsByProductID[$weight['product_id']] = array();
				}
				$weightsByProductID[$weight['product_id']][] = $weight;
			}
			$pickerItemByProductID = array();	
			foreach($productIDArray as $productID){
				$x1 = "SELECT * FROM `product` WHERE id='$productID'";
				$y1 = mysqli_query($conn, $x1);
				$product = mysqli_fetch_array($y1);

				$count = count($weightsByProductID[$productID]);
								
				$productID = $product['id'];
				$sheetproduct = $pickersheet_id . "_" . $productID;
				if(!array_key_exists($sheetproduct, $weightsByProductID)){
					$howManyX = "SELECT * FROM `pickerItems` WHERE pickersheet_id=$pickersheet_id AND product_id=$productID LIMIT 1";
					$howManyY = mysqli_query($conn, $howManyX);
					$pickerItemByProductID[$sheetproduct] = mysqli_fetch_array($howManyY);
				}
				
				$pickerItem = $pickerItemByProductID[$sheetproduct];
						
				$kg = 0;
						
				foreach($weightsByProductID[$productID] as $weightRow){
					
					if($weightRow['weight_tear'] == $weightRow['weight_gross']){
						$tw = $weightRow['weight_gross'];
					}else{
						$tw = $weightRow['weight_gross'] - $weightRow['weight_tear'];
					}
					
					$kg = $kg + $tw;
					
					$kg = number_format($kg, 3, '.', '');
				}
						
				if($product['unit'] == 'PPC'){
					$totalPrice += number_format((float)$count * $pickerItem['price'], 2, '.', '');
				}else{
					$totalPrice += number_format((float)$kg * $pickerItem['price'], 2, '.', '');
				}
			}
		}
        
		return $totalPrice;
	}

	function getInvoiceCreditNoteTotal($invoice_id){
		global $conn;

		$db_result = mysqli_query($conn, "SELECT SUM(amount) as total_credit FROM `invoice_payments` WHERE invoice_id='$invoice_id' && payment_method='CREDIT_NOTE'"); 
		$data = mysqli_fetch_array($db_result);

		return (float) $data['total_credit'];

	}

	function invoiceTotalCost($pickersheet_id){
		global $conn;

		$totalCost = 0;
		
		$outPalletQuery = "SELECT * FROM `palletsOut` WHERE pickersheet_id='$pickersheet_id'";
		$outPalletsResult = mysqli_query($conn, $outPalletQuery);
                
		$outPalletCount = mysqli_num_rows($outPalletsResult);

		while($outPallet = mysqli_fetch_array($outPalletsResult)){
			$weight_ids = $outPallet['weight_ids'];


			$weightCountResult = mysqli_query($conn, "SELECT count(id) as count FROM `weights` WHERE id in ($weight_ids)");
			$weightCountData = mysqli_fetch_array($weightCountResult);
			$this_weight_count = $weightCountData['count'];

			$weightResult = mysqli_query($conn, "SELECT * FROM `weights` WHERE id in ($weight_ids) GROUP BY product_id");
			$weightData = mysqli_fetch_array($weightResult);
			$product_id = $weightData['product_id'];
			
			
			$productResult = mysqli_query($conn, "SELECT * FROM product WHERE id = $product_id");
			$productData = mysqli_fetch_array($productResult);

			$totalCost += ($productData['cost'] * $this_weight_count);
		}

		return $totalCost;
	}

	function creditNoteTotal($invoice_payment_id){
		global $conn;

		$price = 0;
		$creditNoteResult = mysqli_query($conn, "SELECT * FROM `credit_note_items` WHERE payment_id =$invoice_payment_id");

		while($creditNoteItem = mysqli_fetch_array($creditNoteResult)){
			if($creditNoteItem['product_id'] == 0 || weightTypeOfProduct($creditNoteItem['product_id']) == 'PPC'){ # bespoke credit note, not attached product
				$price += $creditNoteItem['price'] * $creditNoteItem['quantity'];	
			}else{
				$weight = weightFromProductIDArray([$creditNoteItem['product_id']]);
				$price += ($creditNoteItem['price'] * $weight);
			}
		}
		
		return ceilDec($price,2);
	}

	function weightCountOfProductOnPicksheet($pick_id, $productID){
		global $conn;

		$outpalletQuery = "SELECT * FROM `palletsOut` WHERE pickersheet_id='$pick_id'";
        	$outpalletResult2 = mysqli_query($conn, $outpalletQuery);
    
		$total_count = 0;

    		while($outpallet = mysqli_fetch_array($outpalletResult2)){
            		$weightids = explode(',', $outpallet['weight_ids']);

            		$x2 = "SELECT * FROM `weights` WHERE id IN (".implode(",",$weightids).") && status_id='1' && product_id=$productID";

            		$y2 = mysqli_query($conn, $x2);
	
			$count = mysqli_num_rows($y2);
			$total_count += $count;
        }


		return $total_count;
	}

	function weightValueOfProductOnPicksheet($pick_id, $productID){
		global $conn;
		
		$outpalletQuery = "SELECT * FROM `palletsOut` WHERE pickersheet_id='$pick_id'";
        $outpalletResult2 = mysqli_query($conn, $outpalletQuery);
        
		$weight = 0;
        
		while($outpallet = mysqli_fetch_array($outpalletResult2)){
            $weightids = $outpallet['weight_ids'];
			
			$x = "SELECT * FROM `weights` WHERE id IN ($weightids) AND product_id = $productID";
			$y = mysqli_query($conn, $x);
            
			while($row = mysqli_fetch_array($y)){
				if($row['weight_tear'] == $row['weight_gross']){
					$w = $row['weight_gross'];
				}else{
					$w = $row['weight_gross'] - $row['weight_tear'];
				}
				
				$weight = $weight + $w;
			}
        }

		 
		return number_format($weight, 3, '.', '');
	}

	function totalValueCreditedOnInvoiceID($invoice_id){
		global $conn;
		$price = 0;
		$paymentsResult = mysqli_query($conn, "SELECT id FROM `invoice_payments` WHERE invoice_id='$invoice_id' AND payment_method = 'CREDIT_NOTE'");
		while ($paymentData = mysqli_fetch_assoc($paymentsResult))
		{
			$price = $price + creditNoteTotal($paymentData['id']);
		}		
		return ceilDec($price,2);	
 	}

	function doesInvoiceHaveReturns($invoice_id){
		global $conn;

		$result = mysqli_query($conn, "SELECT count(id) as count FROM `intake` WHERE returned=1 && delivery_note_number='$invoice_id'");
		$data = mysqli_fetch_array($result);

		if($data['count'] == 0){
			return false;
		}

		return true;
	}

	function doesInvoiceHaveCreditNote($invoice_id){
		global $conn;

		$result = mysqli_query($conn, "SELECT count(id) as count FROM `invoice_payments` WHERE payment_method='CREDIT_NOTE' && invoice_id='$invoice_id'");
		$data = mysqli_fetch_array($result);

		if($data['count'] == 0){
			return false;
		}

		return true;
	}

	function getInvoiceCreditNotes($invoice_id){
		global $conn;

		$result = mysqli_query($conn, "SELECT * FROM `invoice_payments` WHERE payment_method='CREDIT_NOTE' && invoice_id='$invoice_id'");
		$array = array();
		while ($row = mysqli_fetch_assoc($result))
		{	
			$row['noteItems'] = array();
			$cnq = mysqli_query($conn, 
				"SELECT 
					`credit_note_items`.id AS 'credit_note_items_id',
					`credit_note_items`.payment_id AS 'credit_note_items_payment_id',
					`credit_note_items`.product_id AS 'credit_note_items_product_id',
					`credit_note_items`.quantity AS 'credit_note_items_quantity',
					`credit_note_items`.price AS 'credit_note_items_price',
					`credit_note_items`.description AS 'credit_note_items_description',
					product.id AS 'product_id',
					product.pallet_id AS 'product_pallet_id',
					product.cut_id AS 'product_cut_id',
					product.brand_id AS 'product_brand_id',
					product.nationality_id AS 'product_nationality_id',
					product.cooling_id AS 'product_cooling_id',
					product.status AS 'product_status',
					product.range_from AS 'product_range_from',
					product.range_to AS 'product_range_to',
					product.ubbb AS 'product_ubbb',
					product.unit AS 'product_unit',
					product.comments AS 'product_comments',
					product.best_by AS 'product_best_by',
					product.pricetype AS 'product_pricetype',
					product.cost AS 'product_cost',
					product.price AS 'product_price',
					product.box_id AS 'product_box_id',
					product.weightnote AS 'product_weightnote',
					product.product_temp AS 'product_product_temp'
				FROM `credit_note_items`
				LEFT JOIN `product` ON `credit_note_items`.product_id = product.id WHERE credit_note_items.payment_id = ".$row['id']);
			
				while ($cnr = mysqli_fetch_assoc($cnq))
				{
					if($cnr['product_id'] == 0 || weightTypeOfProduct($cnr['product_id']) == 'PPC'){ # bespoke credit note, not attached product
						$cnr['finalValue'] = floorDec($cnr['credit_note_items_price'] * $cnr['credit_note_items_quantity']);
					}else{
						$weight = weightFromProductIDArray([$cnr['product_id']]);
						$cnr['finalValue'] = floorDec(($cnr['credit_note_items_price'] * $weight));
					}
					$row['noteItems'][] = $cnr;
				}
			$array[] = $row;
		}

   		return $array;
	}
	function floorDec($val, $precision = 2) {
		if ($precision < 0) { $precision = 0; }
		$numPointPosition = intval(strpos($val, '.'));
		if ($numPointPosition === 0) { //$val is an integer
			return $val;
		}
		return floatval(substr($val, 0, $numPointPosition + $precision + 1));
	}
	function ceilDec ( $value, $precision = 2 ) { 
		$offset = 0.5;
		if ($precision !== 0)
			$offset /= pow(10, $precision);
		$final = round($value + $offset, $precision, PHP_ROUND_HALF_DOWN);
		return ($final == -0 ? 0 : $final);
	} 
	function fuzzyCustomerSearch($name,$allSearch=false)
	{
		global $conn;
		$tests = array(
			$name,
			str_replace(" ","",$name),
			str_replace(" & "," and ",$name),
			str_replace("&"," & ",$name)
		);
		$allSearchControl = "";
		if ($allSearch == false) $allSearchControl ="AND (`credit_terms` > -1 || `override` = 1)";
		$queries = array(
			"SELECT * FROM `customers` WHERE businessname LIKE '%%%s%%' $allSearchControl",
			"SELECT * FROM `customers` WHERE MATCH(businessname) AGAINST ('%s') $allSearchControl",
			"SELECT * FROM `customers` WHERE businessnameDM LIKE CONCAT('%%',dm('%s'),'%%') $allSearchControl",
		);
		foreach ($tests as $test)
		{
			foreach ($queries as $query)
			{
				$x = sprintf($query,$test);			
				$y = mysqli_query($conn, $x);
				$count = mysqli_num_rows($y);
				if ($count > 0 && $count < 20)
				{
					break 2;
				}
			}
		}
		return $y;
	}
	function loggedDataChange($type,$entity_id,$body){
		global $conn;
		global $userid;
		$body = mysqli_real_escape_string($conn,$body);
		$x = "INSERT INTO `comment_logging` (`type`,`user_id`,`entity_id`,`body`) VALUES ('$type',$userid,$entity_id,'$body')";			
		$y = mysqli_query($conn, $x);
	}
	CONST PAYMENT_METHODS = ['CHEQUE', 'BACS', 'CASH','CREDIT_NOTE'];	
?>