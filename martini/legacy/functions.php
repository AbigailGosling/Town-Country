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
	
	
	$pageName = request()->server('REQUEST_URI');
	
	$exit = 1;
	switch ($pageName)
	{
		case '/':
		case '/index.php':
		case '/legacy/script_login.php':
		case '/legacy/ajax/generatePDFstatement2.php':
		case '/ajax/deletePick.php':
		case '/legacy/scripts/SLabsNotifier.php':
			$exit = 0;
	}
	
	if(!$_SESSION['USER'] && $exit == 1){ header('location:/index.php'); }
	
	if($userid != ''){
		$x = "SELECT * FROM users WHERE id=?";
		$y = prepareExecuteQuery($x,'i',[$userid]);
		
		$user = $y->fetch_assoc();
	}
	function prepareExecuteQuery(string $sql, string $varTypes = null, array $vars = null)
	{
		global $mysqli;
		$stmt = $mysqli->prepare($sql);
		if ($varTypes != null && $vars != null) $stmt->bind_param($varTypes,...$vars);
		$stmt->execute();
		$res = $stmt->get_result();
		if ($res) return $res;
		else return new mysqli_result($mysqli);
	}
	// sendEmail(['kez@phenixdigital.co.uk'], 'test', 'hello');
	function sendEmail($toArray, $mail_subject, $mail_message, $name = 'Town & Country'){
		 
        $domain_email='webform@'.str_replace("www.", "", request()->server('HTTP_HOST'));
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: ".$name."<".$domain_email.">\r\n";
        $headers .= "Subject: {$mail_subject}";
        
        
         
        $to = implode(", ", $toArray);
        $boSend = mail($to, $mail_subject, $mail_message, $headers);
	}
	
	function getPicksheetValue($pickersheet_id){
		global $mysqli;
		
		# sell price * weight
		$outpalletQuery = "SELECT * FROM `palletsOut` WHERE pickersheet_id=?";
		$outpalletResult = prepareExecuteQuery($outpalletQuery,'i',[$pickersheet_id]);
		$totalPrice = 0;
		while($outpallet = $outpalletResult->fetch_assoc()){
		$weightids = explode(',', $outpallet['weight_ids']);
			$queryBits = '';
			$queryBits2 = ''; 

			foreach($weightids as $weightid){
				$queryBits .= ' id = ? || ';
			}

			$queryBits = rtrim($queryBits," || ");

			$x = "SELECT * FROM `weights` WHERE $queryBits";
			$y = prepareExecuteQuery($x,str_repeat('i',count($weightids)),$weightids);

			$count = $y->num_rows;

			$kg = 0;
			$productids = array();
			while($weight = $y->fetch_assoc()){
				$queryBits2 .= ' id = ? || '; 
				$productids[]=$weight['product_id'];
				
				if($weight['weight_tear'] == $weight['weight_gross']){
					$w = $weight['weight_gross'];
				}else{
					$w = $weight['weight_gross'] - $weight['weight_tear'];
				}
				
				$kg = $kg + $w;
			 
			}
			$queryBits2 = rtrim($queryBits2," || ");

			$x = "SELECT * FROM `product` WHERE $queryBits2 GROUP BY cut_id";
			$y = prepareExecuteQuery($x,str_repeat('i',count($productids)),$productids);
			
			while($product = $y->fetch_assoc()){
		 
					$productID = $product['id'];
					$howManyX = "SELECT * FROM `pickerItems` WHERE pickersheet_id=? AND product_id=?";
					$howManyY = prepareExecuteQuery($howManyX,'ii',[$pickersheet_id,$productID]);
					$pickerItem = $howManyY->fetch_assoc();
					
					$totalPrice += number_format((float)$kg * $pickerItem['price'], 2, '.', '');
			
			}
		}
		
		
		return $totalPrice;
	}

	function averageDaysUntilPaidForCustomer($customer_id){
        global $mysqli;
        
        $invoice_payment_times = [];

        $invoiceResults = prepareExecuteQuery("SELECT date_completed,id FROM `pickerSheets` WHERE completed ='1' && customer_id=?",'i',[$customer_id]);

        // loop through all completed invoices associated with this customer_id
        while($invoice = $invoiceResults->fetch_assoc()){
            $invoice_id = $invoice['id'];
            $date_completed = $invoice['date_completed'];
            
            // get the final payment for this invoice
            $paymentResult = prepareExecuteQuery("SELECT created_at,id FROM `invoice_payments` WHERE invoice_id=? ORDER BY created_at DESC LIMIT 1",'i',[$invoice_id]);
            $count = $paymentResult->num_rows;

            // check we have a payment record, old completed invoices do not have any
            if($count == 1){
                $paymentData = $paymentResult->fetch_assoc();

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
	
	function getPurchase($purchaseid){
		global $mysqli;
		
		$x = "SELECT * FROM `purchase_form` WHERE id=?";
		$y = prepareExecuteQuery($x,'i',[$purchaseid]);
		
		$row = $y->fetch_assoc();
		
		return $row;
	}

	function markPalletAs($pallet_id, $status){
		global $mysqli;

		$productIDS = array($status);

		# Ash Request - If a pallet is marked as available (0), the tampered status should be reset (0)
		if($status == 0){
			$tampered = 0;
		}else{
			$tampered = 1;
		}
		$productIDS[]=$tampered;
		# Get all product IDS for this pallet & store in array
		$productsResult = prepareExecuteQuery("SELECT id FROM `product` WHERE pallet_id=?",'i',[$pallet_id]);
		while($product = $productsResult->fetch_assoc()){ $productIDS[]= $product['id']; }


		$weightsResult = prepareExecuteQuery("UPDATE `weights` SET status_id='$status', tampered=$tampered WHERE product_id IN (".implode(",",array_fill(0,count($productIDS),"?")).")",
			"s".str_repeat("i",count($productIDS)-1),
			$productIDS);
	}

	function isPalletSold($pallet_id){
		global $mysqli;

		$productIDS = array();

		# Get all product IDS for this pallet & store in array
		$productsResult = prepareExecuteQuery("SELECT id FROM `product` WHERE pallet_id=?",'i',[$pallet_id]);
		while($product = $productsResult->fetch_assoc()){ array_push($productIDS, $product['id']); }

		$allProductWeights = prepareExecuteQuery("SELECT id FROM `weights` WHERE product_id IN (".implode(",",array_fill(0,count($productIDS),"?")).")",str_repeat('i',count($productIDS)),$productIDS);
		$totalWeights = $allProductWeights->num_rows;

		$soldProductWeights = prepareExecuteQuery("SELECT id FROM `weights` WHERE status_id='1' && product_id IN (".implode(",",array_fill(0,count($productIDS),"?")).")",str_repeat('i',count($productIDS)),$productIDS);
		$soldWeights = $soldProductWeights->num_rows;
		
		if($soldWeights == $totalWeights){
			// entire pallet is sold
			return 1;
		}else{
			// pallet still has some available stock 
			return 0;
		}


		
	}
	
	function createPurchase($supplier_id,$transportation, $speciesString,$cutString,$priceString,$unitsString, $date_purchased, $purchased_by, $date_due, $purchase_comments, $file_name, $booking_ref_number, $haulier, $direct_drop, $temperature_id){
		global $mysqli;
		
		$x = "INSERT into purchase_form (supplier_id,species,cut,price,units,date_purchased,purchased_by,date_due,purchase_comments,dfile,booking_ref_number,transportation,haulier,direct_drop,temperature_id) 
		VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
		prepareExecuteQuery($x,'isssssssssssss',
			[$supplier_id,$speciesString,$cutString,$priceString,$unitsString,$date_purchased,$purchased_by,$date_due,$purchase_comments,$file_name,$booking_ref_number,$transportation,$haulier,$direct_drop,$temperature_id]);
         
		$id = $mysqli->insert_id;
		
		return $id;
	}
	
	
	function updatePurchase($id, $transportation, $supplier_id,$speciesString,$cutString,$unitsString, $priceString, $date_purchased, $purchased_by, $date_due, $purchase_comments, $file_name, $booking_ref_number,$haulier, $direct_drop, $temperature_id){
		global $mysqli;
		
		$x ="UPDATE `purchase_form` SET transportation=?, supplier_id=?,species=?, cut=?,units=?, price=?, date_purchased=?,purchased_by=?,date_due=?,purchase_comments=?";
		$vars = [$transportation,$supplier_id,$speciesString,$cutString,$unitsString,$priceString,$date_purchased,$purchased_by,$date_due,$purchase_comments];
        if($file_name != ''){
            $x .=",dfile=?";
			$vars[] =$file_name;
		}
		
		if($temperature_id != ''){
            $x .=",temperature_id=?";
			$vars[]=$temperature_id;
        }

        $x .=",booking_ref_number=?, haulier=?,direct_drop=? WHERE id=?";
		$vars[]=$booking_ref_number; $vars[]=$haulier; $vars[]=$direct_drop; $vars[]=$id;
		prepareExecuteQuery($x,str_repeat('s',count($vars)),$vars);
		
		
	}
	
	function addIntakeFromPurchase($supplier_id, $purchase_id, $date_received, $vehicle_reg, $vehicle_temperature, $product_temperature, $delivery_note_number,$purchased_id){
		global $mysqli;
		
		$x = "INSERT into `intake` (supplier_id, purchase_id, date_received, vehicle_reg, vehicle_temperature,product_temperature,delivery_note_number,user_id) 
				VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
		$y = prepareExecuteQuery($x,'iissssss',[$supplier_id,$purchase_id,$date_received,$vehicle_reg,$vehicle_temperature,$product_temperature,$delivery_note_number,$purchased_id]);
		
		$id = $mysqli->insert_id;

		return $id;
		
	}
	
	
	
	 
	
	
	function intakeIDfromPalletID($id){
		global $mysqli;
		// ??: Why get everything if all we need is the intake_id?
		$x = "SELECT intake_id FROM `pallet` WHERE id=?";
		$y = prepareExecuteQuery($x,'i',[$id]);
		$row = $y->fetch_assoc();
		
		return $row['intake_id'];
		
	}
	
	function getWeightOfProduct($product_id){ #kezkez
		global $mysqli;
		
		$x = "SELECT * FROM `weights` WHERE product_id=?";
		$y = prepareExecuteQuery($x,'i',[$product_id]);
		
		$value = 0;
		
		while($weights = $y->fetch_assoc()){
			// $value = $value + $weights['weight_gross'];
			
			if($weights['weight_tear'] !=''){
				$value = $value + ($weights['weight_gross'] - $weights['weight_tear']);
			}
		}
		
		return $value;
	}
	
	
	function getProductIDfromWeightID($weight_id){
		global $mysqli;
		
		$x = "SELECT product_id FROM `weights` WHERE id=?";
		$y = prepareExecuteQuery($x,'i',[$weight_id]);
		$row = $y->fetch_assoc();
		
		return $row['product_id'];
	}
	
	# returns count of weight entries for the products past in []
	function countFromProductIDArray($PRODUCT_IDS){
		global $mysqli;

		$y = prepareExecuteQuery("SELECT id FROM `weights` WHERE product_id IN (".implode(",",array_fill(0,count($PRODUCT_IDS),"?")).")");
		$count = $y->num_rows;

		return $count;
	}

	# returns total weight of products past in []
	function weightFromProductIDArray($PRODUCT_IDS){
		global $mysqli;

		$y = prepareExecuteQuery("SELECT weight_tear,weight_gross FROM `weights` WHERE product_id IN (".implode(",",array_fill(0,count($PRODUCT_IDS),"?")).")",
	str_repeat("s",count($PRODUCT_IDS)),$PRODUCT_IDS);

			
		$weight = 0;
		
		while($row = $y->fetch_assoc()){
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
		global $mysqli;
		
		$x = "SELECT * FROM `weights` WHERE product_id = ?";
		$y = prepareExecuteQuery($x,'i',[$productID]);
		
		$weight = 0;
		
		while($row = $y->fetch_assoc()){
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
		global $mysqli;
		// ??: Assuming status_id 0 is available & 1 is sold, this checks for unsold instead of sold
		$x = "SELECT * FROM `weights` WHERE status_id != '1' && product_id = ?";
		//$x = "SELECT * FROM `weights` WHERE product_id = $productID";
		$y = prepareExecuteQuery($x,'i',[$productID]);
		
		$weight = 0;
		
		while($row = $y->fetch_assoc()){
			if($row['weight_tear'] == $row['weight_gross']){
				$w = $row['weight_gross'];
			}else{
				$w = $row['weight_gross'] - $row['weight_tear'];
			}
			
			$weight = $weight + (int)$w;
		}
		
		
		return $weight;
    }

    function weightsAvailableOnProducts($productIDs){
        global $conn;

        $x = "select count(*)
                    from
                    (
                      select count(id) tot
                      from weights 
                      where product_id in(".implode(",",array_fill(0,count($productIDs),"?")).")
                      group by id
                    ) src;";
        $y = prepareExecuteQuery($x,str_repeat("i",count($productIDs)),$productIDs);
        
        $row = mysqli_fetch_array($y);
        
        return $row[0];
    }

	function weightsAvailableOnProduct($productID){
		global $mysqli;
		
		$x = "SELECT id FROM `weights` WHERE status_id != '1' && product_id = ?";
 		$y = prepareExecuteQuery($x,'i',[$productID]);
		
		$count = $y->num_rows;
		
		return $count;
		
		
	}
	
    function getWeightFromProductID($productID){
		global $mysqli;
		
		$x = "SELECT * FROM `weights` WHERE product_id = ?";
		$y = prepareExecuteQuery($x,'i',[$productID]);
		
		$weight = 0;
		
		while($row = $y->fetch_assoc()){
			if($row['weight_tear'] == $row['weight_gross']){
				$w = $row['weight_gross'];
			}else{
				$w = $row['weight_gross'] - $row['weight_tear'];
			}
			
			$weight = $weight + $w;
		}
		
		
		return $weight;
	}

	# Login form
	# Just checking for a email/pass match. Passwords are sha1 encrypted
	function check_login($email, $password){
		global $mysqli;
		$email = $mysqli->real_escape_string($email);
		$password = sha1($mysqli->real_escape_string($password));

		$result = prepareExecuteQuery("SELECT * FROM `users` WHERE `email` = ? && `password` = ? LIMIT 1",'ss', [$email, $password]);
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
		global $mysqli;
		
		$x = "DELETE FROM weights WHERE id=? LIMIT 1";
		$y = prepareExecuteQuery($x,'i',[$weightID]);
	}
	
	function deleteIntakeDoc($intakeid, $docid){
		global $mysqli;
		
		$x = "DELETE FROM intakeDocs WHERE id=? && intakeid=? LIMIT 1";
		$y = prepareExecuteQuery($x,'ii',[$docid,$intakeid]);
	}
	
	function deletePurchase($id){
		global $mysqli;
		
		$x = "DELETE FROM purchase_form WHERE id=? LIMIT 1";
		$y = prepareExecuteQuery($x,'i',[$id]);
	}
	
	# Get Cut name from id
	function getCut($id){
		global $mysqli;
		// ??: Why get everything if we only want the name?
		$x = "SELECT `name` FROM `cuts` WHERE `id` = ?";
		$y = prepareExecuteQuery($x,'i',[$id]);
		
		$row = $y->fetch_assoc();
		
		return $row['name']; 
	}
	
	
	function getTotesNumProductsForCutOnIntake($intake_id, $cut_id){
		global $mysqli;
		
		$x = "SELECT * FROM `pallet` WHERE intake_id =?";
		$y = prepareExecuteQuery($x,'i',[$intake_id]);
		
		$totalNum = 0;
		
		while($palletRow = $y->fetch_assoc()){
			$pallet_id = $palletRow['id'];
			
			$x2 = "SELECT * FROM `product` WHERE pallet_id=? AND cut_id=?";
			$y2 = prepareExecuteQuery($x2,'ii',[$pallet_id,$cut_id]);
			
			
			 while($productRow = $y2->fetch_assoc()){
				$product_id = $productRow['id'];
				$x3 = "SELECT * FROM `weights` WHERE product_id=?";
				$y3 = prepareExecuteQuery($x3,'i',[$product_id]);
				
				$count = $y3->num_rows;
				
				$totalNum = $totalNum + $count;
			}
			
		}
		
		return $totalNum;
	}
	
	function getTotesWeightOfCutOnIntake($intake_id, $cut_id){
		global $mysqli;
		
		$x = "SELECT * FROM `pallet` WHERE intake_id =?";
		$y = prepareExecuteQuery($x,'i',[$intake_id]);
		
		$totalWeight = 0;
		
		while($palletRow = $y->fetch_assoc()){
			$pallet_id = $palletRow['id'];
			
			$x2 = "SELECT * FROM `product` WHERE pallet_id=? AND cut_id=?";
			$y2 = prepareExecuteQuery($x2,'ii',[$pallet_id,$cut_id]);
			
			while($productRow = $y2->fetch_assoc()){
				$totalWeight = $totalWeight + (double) getTotalWeightOfProduct($productRow['id']);
			}
			
		}
		
		return $totalWeight;
	}
	
	function getTotalWeightOfProduct($product_id){
		global $mysqli;
		
		$xWeight = "SELECT * FROM `weights` WHERE product_id=?";
		$yWeight = prepareExecuteQuery($xWeight,'i',[$product_id]);
		
		$totalWeight = 0;
		
		while($rowWeight = $yWeight->fetch_assoc()){			
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
        global $mysqli;

        $x = "SELECT COUNT(weights.id) as count FROM `weights` INNER JOIN `product` ON weights.product_id=product.id WHERE product.cut_id=? && product.pallet_id=? && weights.status_id != '1'";
        $y = prepareExecuteQuery($x,'ii',[$cut_id,$pallet_id]);
        $row = $y->fetch_assoc();
    
        return $row['count'];
    }
 

    function countNumProductsForCutOnPalletArrays($palletIDS, $cutIDS, $nationalityID){
        global $mysqli;
        
        $palletIDS = implode(',', $palletIDS);
        $cutIDS = implode(',', $cutIDS);

        $x = "SELECT COUNT(weights.id) as num FROM `weights` INNER JOIN `product` ON weights.product_id=product.id WHERE product.cut_id IN ($cutIDS) && product.pallet_id IN ($palletIDS) && weights.status_id != 1 && product.nationality_id=?";
		$y = prepareExecuteQuery($x,'i',[$nationalityID]);
        $row = $y->fetch_assoc();
	

		if($row["num"] == 0)
		{
			return 0;
		}

        $x1 = "SELECT pickerItems.id, product.id AS productid  FROM `pickerItems` INNER JOIN `product` ON pickerItems.product_id=product.id && pickerItems.deleted !=1 && pickerItems.status = '0' && product.pallet_id IN ($palletIDS) && product.cut_id IN ($cutIDS)";
        $y1 = prepareExecuteQuery($x1);
		$numInPicking = $y1->num_rows;
		
        return $row['num'] - $numInPicking;
    }

	function numWeightsAvailableFromProductID($product_id){
		global $mysqli;


        $x = "SELECT COUNT(weights.id) as num FROM `weights` INNER JOIN `product` ON weights.product_id=product.id WHERE product.id = ? && weights.status_id != 1";
		$y = prepareExecuteQuery($x,'i',[$product_id]);
		$row = $y->fetch_assoc();

		$x1 = "SELECT id FROM `pickerItems` WHERE product_id=? && status = '0' && deleted !=1";
		$y1 = prepareExecuteQuery($x1,'i',[$product_id]);
		$numInPicking = $y1->num_rows;
		
		return $row['num'] - $numInPicking;
	}


    function totalWeightOfAdvisedKGProduct($intake_id, $nationalityID){
        global $mysqli;
        
        $x = "SELECT id FROM `pallet` WHERE intake_id=?";
        $y = prepareExecuteQuery($x,'i',[$intake_id]);
        
        $qPallets = array();
        
        while($row = $y->fetch_assoc()){
            $rowid = $row['id'];
            
            $qPallets[]=$rowid;
        }

        $t_count = 0;
        $q = "SELECT * FROM product WHERE nationality_id=$nationalityID AND pallet_id IN (" . implode(",",array_fill(0,count($qPallets),"?")) . ")";
        $countQuery = prepareExecuteQuery($q,str_repeat('i',count($qPallets)),$qPallets);

        while($countRow = $countQuery->fetch_assoc()){
            $t_count += $countRow['akg'];
        }

        return $t_count;
    }
    function totalWeightOfProduct($productIDS){
        global $mysqli;
                
        $x = "SELECT * FROM `weights` WHERE status_id != '1' && product_id IN (" . implode(",",array_fill(0,count($productIDS),"?")) . ")";
		$y = prepareExecuteQuery($x,str_repeat('i',count($productIDS)),$productIDS);
		
		$weight = (float)0;
		
		while($row = $y->fetch_assoc()){
			if($row['weight_tear'] == $row['weight_gross']){
				$w = (float)$row['weight_gross'];
			}else{
				$w = (float)$row['weight_gross'] - (float)$row['weight_tear'];
			}
			
			$weight = (float)$weight + (float)$w;
		}
		
		
		return $weight;
        
    }


    function countNumProductsForCutOnPalletThatIsntPicked($pallet_id,$cut_id){
        global $mysqli;

	    //SELECT pickerItems.id FROM `pickerItems` INNER JOIN `product` ON pickerItems.product_id=product.id
        $x1 = "SELECT pickerItems.id, product.id AS productid  FROM `pickerItems` INNER JOIN `product` ON pickerItems.product_id=product.id && product.pallet_id=? && product.cut_id=?";
        $y1 = prepareExecuteQuery($x1,'ii',[$pallet_id,$cut_id]);
        $numInPicking = $y1->num_rows;

        $xBit = [];
        while($row = $y1->fetch_assoc()){
            $productid = $row['productid'];

            $xBit[]=$productid;
        }

        $x2 = "SELECT id FROM `weights` WHERE status_id != '1' && (".implode(",",array_fill(0,count($xBit),"?")).")";
        
        $y2 = prepareExecuteQuery($x2,str_repeat('i',count($xBit)),$xBit);
        $f = $y2->num_rows;
        $numAvailable = $f - $numInPicking;
        
        return $numAvailable; 
    }
 
    
    function getTotalNumOfWeights($intake_id, $cut_id){
		global $mysqli;
		
		$x11 = "SELECT * FROM `pallet` WHERE intake_id = ?";
		$y11 = prepareExecuteQuery($x11,'i',[$intake_id]);
		
 		$count = 0;
		while($pallet = $y11->fetch_assoc()){
			
			$pallet_id = $pallet['id'];
			 
			$x = "SELECT * FROM `product` WHERE pallet_id=? && cut_id=?";
            $y = prepareExecuteQuery($x,'ii',[$pallet_id,$cut_id]);
            
             
            while($row = $y->fetch_assoc()){
                $product_id = $row['id'];
                
                $x1 = "SELECT * FROM `pickerItems` WHERE product_id = ?";
                $y1 = prepareExecuteQuery($x1,'i',[$product_id]);

                $numInPicking = $y1->num_rows;
                
                // $x2 = "SELECT * FROM `weights` WHERE product_id='$product_id' && status_id='0'";
                $x2 = "SELECT * FROM `weights` WHERE status_id != '1' && product_id=?";
                $y2 = prepareExecuteQuery($x2,'i',[$product_id]);

                $f = $y2->num_rows;

                $count = $count + $f;

                $count = $count - $numInPicking;
            }
            
             
		}
		return $count;
 	}

	function areWeightsAllTheSame($product_id){
		global $mysqli;
		
		$x = "SELECT * FROM `weights` WHERE product_id=? GROUP BY weight_gross";
		$y = prepareExecuteQuery($x,'i',[$product_id]);
		
		$count = $y->num_rows;
		
		return $count;
		
	}
	
	
	# Get Species name from id
	function getSpecies($id){
		global $mysqli;
		
		$x = "SELECT * FROM species WHERE id = ?";
		$y = prepareExecuteQuery($x,'i',[$id]);
		
		$row = $y->fetch_assoc();
		
		return $row['name']; 
	}

	function getSpeciesFromCut($cut_id){
		global $mysqli;
		
		$x = "SELECT * FROM cuts WHERE id = ?";
		$y = prepareExecuteQuery($x,'i',[$cut_id]);
		
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
		global $mysqli;
		global $nationalities;

		$result = searchInNestedArray($nationalities, "id", $id);
		
		if($result)
		{
			return $result['name'];
		}
		
		$x = "SELECT * FROM nationality";
		$y = prepareExecuteQuery($x);
		$nationalities = $y->fetch_all(MYSQLI_ASSOC);
		
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
		global $mysqli;
		global $temperatures;

		$result = searchInNestedArray($temperatures, "id", $tempid);
		
		if($result)
		{
			return $result['temperature'];
		}
		
		$x = "SELECT * FROM temperature";
		$y = prepareExecuteQuery($x);
		$temperatures = $y->fetch_all(MYSQLI_ASSOC);
		
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
		global $mysqli;
		global $brands;

		$result = searchInNestedArray($brands, "id", $id);
		
		if($result)
		{
			return $result['name'];
		}

		$x = "SELECT * FROM brands";
		$y = prepareExecuteQuery($x);
		
		$brands = $y->fetch_all(MYSQLI_ASSOC);
		
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
		
		foreach ($array as $val) {
			if ($val[$field] == $value) {
				$result = $val;
				return $result;
			}
		}
	}
	
	# weight of product
	function weightOfProduct($product_id){
		global $mysqli;
		
		$x = "SELECT * FROM `weights` WHERE product_id = ?";
		$y = prepareExecuteQuery($x,'i',[$product_id]);
		$row = $y->fetch_assoc();
		
		return $row['weight_gross']; # I dont think this is right..
	}
	
	
	# weight type of product
	function weightTypeOfProduct($product_id){
		global $mysqli;
		
		$x = "SELECT * FROM `product` WHERE id = ?";
		$y = prepareExecuteQuery($x,'i',[$product_id]);
		$row = $y->fetch_assoc();
		
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
		global $mysqli;
		$weight = 0;
		
		$x = "SELECT * FROM `boxes` WHERE product_id = ?";
		$y = prepareExecuteQuery($x,'i',[$product_id]);
		
		while($row = $y->fetch_assoc()){
			$weight = $weight + (int) $row['weight']; 
		}
		
		return $weight;
	}
	
	# Get Supplier name from id
	function supplierName($id){
		global $mysqli;
		
		$x = "SELECT * FROM supplier WHERE id = ?";
		$y = prepareExecuteQuery($x,'i',[$id]);
		
		$row = $y->fetch_assoc();
		
		return $row['name']; 
	}
	
	
	function customerName($id){
		global $mysqli;
		
		$x = "SELECT * FROM customers WHERE id = ?";
		$y = prepareExecuteQuery($x,'i',[$id]);
		
		$row = $y->fetch_assoc();
		
		return $row['businessname']; 
	}
	
	
	# Get Intake - expects 1 param, intake_id
	function getIntake($id){
		global $mysqli;
		
		$x = "SELECT * FROM `intake` WHERE id=?";
		$y = prepareExecuteQuery($x,'i',[$id]);
		
		$row = $y->fetch_assoc();
		
		return $row;
		
	}
	
	# Get Supplier - expects 1 param, supplier_id
	function getCustomer($id){
		global $mysqli; 
		
		$x = "SELECT * FROM `customers` WHERE id = ?";
		$y = prepareExecuteQuery($x,'i',[$id]);
		
		$row = $y->fetch_assoc();
		
		return $row;
	}
	
	# Get Supplier - expects 1 param, supplier_id
	function getSupplier($id){
		global $mysqli; 
		
		$x = "SELECT * FROM `supplier` WHERE id = ?";
		$y = prepareExecuteQuery($x,'i',[$id]);
		
		$row = $y->fetch_assoc();
		
		return $row;
	}

	# Get Count Customer Sales By Salesman - expects 2 param, customer_id, user_id
	function countCustomerSalesBySalesman($customer_id, $user_id){
		global $mysqli;

		$queryResult = prepareExecuteQuery("SELECT COUNT(id) as count FROM `pickerSheets` WHERE user_from_id=? && customer_id=?",'ii',[$user_id,$customer_id]);

		$countData = $queryResult->fetch_assoc();

		return $countData['count'];
	}
	
	# Get Outstanding Picksheet Total Price - expects 1 param, picksheet_id
	function getOutstandingPicksheetTotal($picksheet_id){
		global $mysqli;

		$customerPicksheets = prepareExecuteQuery("SELECT pickerSheets.id, SUM(invoice_payments.amount) as paid FROM `pickerSheets` left join invoice_payments on pickerSheets.id = invoice_payments.invoice_id WHERE (pickerSheets.completed = 1 AND pickerSheets.id=?) GROUP by pickerSheets.id",'i',[$picksheet_id]);

		$totalOutstanding = 0.00;

		$picksheet = $customerPicksheets->fetch_assoc();
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
		global $mysqli;

		$customerPicksheets = prepareExecuteQuery("SELECT pickerSheets.id, SUM(invoice_payments.amount) as paid FROM `pickerSheets` left join invoice_payments on pickerSheets.id = invoice_payments.invoice_id WHERE (pickerSheets.completed = 1 AND pickerSheets.id IN (".implode(",",array_fill(0,count($picksheet_ids),"?")).")) GROUP by pickerSheets.id",
	str_repeat("i",count($picksheet_ids)),$picksheet_ids);

		$this_price = 0.00;

		while($picksheet = $customerPicksheets->fetch_assoc())
		{
			$this_price = $this_price + (float) invoiceTotal($picksheet['id']);
		}

		return $this_price;
	}
	function getPaidPicksheetTotalList($picksheet_ids){
		global $mysqli;
		$picksheetsResult = prepareExecuteQuery("SELECT SUM(amount) as paid FROM `invoice_payments` WHERE invoice_id IN (".implode(",",array_fill(0,count($picksheet_ids),"?")).")",
	str_repeat("i",count($picksheet_ids)),$picksheet_ids);
		$data = $picksheetsResult->fetch_assoc();

		if($data['paid'] == null){ return 0; }

		return $data['paid'];
	}
	function getTotalPaidByCustomerIDForUserID($customer_id, $user_id){
		global $mysqli;

		$customerPicksheets = prepareExecuteQuery("SELECT pickerSheets.*, SUM(invoice_payments.amount) as paid FROM `pickerSheets` left join invoice_payments on invoice_payments.payment_method != 'CREDIT_NOTE' && pickerSheets.id = invoice_payments.invoice_id WHERE (pickerSheets.completed = 1 AND pickerSheets.customer_id= AND pickerSheets.user_from_id=?)",
	'ii',[$customer_id,$user_id]);

		$data = $customerPicksheets->fetch_assoc();

		if($data['paid'] == null){ return 0; }

		return $data['paid'];
	}

	function getTotalPaidByCustomerIDForUserWithinDates($customer_id, $user_id, $date_start, $date_end){
		global $mysqli;
		$dateQueryPiece = "SELECT GROUP_CONCAT(id) as ids FROM `pickerSheets` WHERE completed=1 && customer_id=?";
		$vars = [$customer_id];
		$varStr='i';
		if ($date_start != "") {
			$dateQueryPiece .= " && pickerSheets.date_completed >= ?";
			$vars[]=$date_start;
			$varStr.='s';
		}
		if ($date_end   != "") {
			$dateQueryPiece .= " && pickerSheets.date_completed <= ?";
			$vars[]=$date_end;
			$varStr.='s';
		}

		$picksheetsResult = prepareExecuteQuery($dateQueryPiece,$varStr,$vars);
		$picksheetData = $picksheetsResult->fetch_assoc();

		$pick_ids = $picksheetData['ids'];

 		$customerPicksheets = prepareExecuteQuery("SELECT pickerSheets.id, SUM(invoice_payments.amount) as paid FROM `pickerSheets` left join invoice_payments on pickerSheets.id = invoice_payments.invoice_id WHERE (pickerSheets.id in ('$pick_ids')) GROUP by pickerSheets.id");

		$data = $customerPicksheets->fetch_assoc();

		if($data['paid'] == null){ return 0; }

		return $data['paid'];

	}

	# Get Picksheet Total Paid - expects 1 param, picksheet_id
	function getPicksheetTotalPaid($picksheet_id){
		global $mysqli;

		$customerPicksheets = prepareExecuteQuery("SELECT SUM(invoice_payments.amount) as paid FROM `pickerSheets` left join invoice_payments on pickerSheets.id = invoice_payments.invoice_id WHERE (pickerSheets.completed = 1 AND pickerSheets.id=? AND invoice_payments.payment_type !=  'CREDIT_NOTE') GROUP by pickerSheets.id",
			'i',[$picksheet_id]);

		$picksheet = $customerPicksheets->fetch_assoc();
 
		$totalPaid = (float) $picksheet['paid'];

		return $totalPaid;
	}
	
	function getCutsFor($species){
		global $mysqli;
		
		
		$x = "SELECT * FROM cuts WHERE species_id=? ORDER BY name ASC";
		$y = prepareExecuteQuery($x,'i',[$species]);
		
		return $y;
	}
	
	# Get Boxes For - returns boxes for specific product_id
	function getBoxesFor($product_id){
		global $mysqli;
		
		$x = "SELECT * FROM boxes WHERE product_id = ?";
		$y = prepareExecuteQuery($x,'i',[$product_id]);
		$row = $y->fetch_assoc();
		return $row;
	}
	
	# Get Username - returns username for specific userid
	function getUsername($userid){
		global $mysqli;
		
		$x = "SELECT * FROM users WHERE id = ?";
		$y = prepareExecuteQuery($x,'i',[$userid]);
		$row = $y->fetch_assoc();
		return $row['name'];
	}
	
	function deleteProductEntry($product_id){
		global $mysqli;
		
		$x = "DELETE FROM `product` WHERE id = ?";
		$y = prepareExecuteQuery($x,'i',[$product_id]);
	}

	# Delete Boxes for specific product_id
	function deleteWeightsFor($product_id){
		global $mysqli;
		
		$x = "DELETE FROM `weights` WHERE product_id = ?";
		$y = prepareExecuteQuery($x,'i',[$product_id]);
	}
	
	# Delete products for specific pallet_id
	function deleteProductsFor($pallet_id){
		global $mysqli;
		
		$x = "DELETE FROM `product` WHERE pallet_id = ?";
		$y = prepareExecuteQuery($x,'i',[$pallet_id]);
	}
	
	# Delete pallet
	function deletePallet($pallet_id){
		global $mysqli;
		
		$x = "DELETE FROM `pallet` WHERE id = ?";
		$y = prepareExecuteQuery($x,'i',[$pallet_id]);
	}
	
	# Add new Intake
	function addIntakeDupe($supplier_id, $date_received, $vehicle_reg, $vehicle_temperature, $product_temperature, $delivery_note_number, $staff_id, $security_id, $purchase_id){
		global $mysqli;
		
		if($purchase_id != '#'){
			$x = "INSERT into `intake` (supplier_id,security_id, date_received, vehicle_reg, vehicle_temperature,product_temperature,delivery_note_number,user_id,purchase_id) 
			VALUES (?,?,?,?,?,?,?,?,?)";
			$vars = [$supplier_id,$security_id,$date_received,$vehicle_reg,$vehicle_temperature,$product_temperature,$delivery_note_number,$staff_id,$purchase_id];
			$varSt= str_repeat('s',count($vars));
		}else{
			$x = "INSERT into `intake` (supplier_id,security_id, date_received, vehicle_reg, vehicle_temperature,product_temperature,delivery_note_number,user_id) 
			VALUES (?,?,?,?,?,?,?,?)";
			$vars = [$supplier_id,$security_id,$date_received,$vehicle_reg,$vehicle_temperature,$product_temperature,$delivery_note_number,$staff_id];
			$varSt= str_repeat('s',count($vars));
		}
		
		$y = prepareExecuteQuery($x,$varSt,$vars);
		
		$v = $mysqli->insert_id;
		return $v;
	}
	
	function addReturnIntake($supplier_id, $date_received, $vehicle_reg, $vehicle_temperature, $product_temperature, $delivery_note_number, $staff_id, $security_id, $purchase_id){
		global $mysqli;
		
		if($purchase_id != '#'){
			$x = "INSERT into `intake` (returned, supplier_id,security_id, date_received, vehicle_reg, vehicle_temperature,product_temperature,delivery_note_number,user_id,purchase_id) 
			VALUES (1,'$supplier_id','$security_id','$date_received','$vehicle_reg','$vehicle_temperature','$product_temperature','$delivery_note_number','$staff_id','$purchase_id')";
			$vars = [1,$supplier_id,$security_id,$date_received,$vehicle_reg,$vehicle_temperature,$product_temperature,$delivery_note_number,$staff_id,$purchase_id];
			$varSt= str_repeat('s',count($vars));
		}else{
			$x = "INSERT into `intake` (returned, supplier_id,security_id, date_received, vehicle_reg, vehicle_temperature,product_temperature,delivery_note_number,user_id) 
			VALUES (1,'$supplier_id','$security_id','$date_received','$vehicle_reg','$vehicle_temperature','$product_temperature','$delivery_note_number','$staff_id')";
			$vars = [1,$supplier_id,$security_id,$date_received,$vehicle_reg,$vehicle_temperature,$product_temperature,$delivery_note_number,$staff_id];
			$varSt= str_repeat('s',count($vars));
		}
		
		$y = prepareExecuteQuery($x,$varSt,$vars);
		
		$v = $mysqli->insert_id;
		return $v;
	}
	
	function getSecurityName($id){
		global $mysqli;
		
		$x = "SELECT * FROM `security` WHERE id= ?";
		$y = prepareExecuteQuery($x,'i',[$id]);
		$row = $y->fetch_assoc();
		
		return $row['name'];
	}
	
	
 
	
	function countTypeOnIntake($intake_id, $unit){
		global $mysqli;
		
		$intake_id = $mysqli->real_escape_string( $intake_id);
		$unit = $mysqli->real_escape_string( $unit);
		
		$x = "SELECT * FROM `pallets` WHERE intake_id=? AND unit=?";
		$y = prepareExecuteQuery($x,'is',[$intake_id,$unit]);
		
		$row = $y->fetch_assoc();
		
		$count = $y->num_rows;
		
		return $count;
		
	}
	
	function getTypeOnIntake($intake_id, $unit){
		global $mysqli;
		
		$intake_id = $mysqli->real_escape_string( $intake_id);
		$unit = $mysqli->real_escape_string( $unit);
		
		$x = "SELECT * FROM `pallets` WHERE intake_id=? AND unit=?";
		$y = prepareExecuteQuery($x,'is',[$intake_id,$unit]);
		
		return $y;
	}
 
	
	function weightFromWeight($weightID){
		global $mysqli;
		
		$x = "SELECT * FROM `weights` WHERE id= ?";
		$y = prepareExecuteQuery($x,'i',[$weightID]);
		$row = $y->fetch_assoc();
		
		if($row['weight_tear'] != ''){
			return $row['weight_gross'];
		}else{
			return ($row['weight_gross'] - $row['weight_tear']);
		}
	}
	  
	function deleteIntake($intake_id){
		global $mysqli;

		$pallet_ids = array();
		$product_ids = array();
		$weight_ids = array();

		$palletsResult = prepareExecuteQuery("SELECT id FROM `pallet` WHERE intake_id=?",'i',[$intake_id]);
		while($pallet = $palletsResult->fetch_assoc()){ array_push($pallet_ids, $pallet['id']); }

		$temp_pallet_ids = implode(',', $pallet_ids);

		$productsResult = prepareExecuteQuery("SELECT id FROM `product` WHERE pallet_id IN ($temp_pallet_ids)");
		while($product = $productsResult->fetch_assoc()){ array_push($product_ids, $product['id']); }

		$temp_product_ids = implode(',', $product_ids);

		$weightsResult = prepareExecuteQuery("SELECT id FROM `weights` WHERE product_id IN ($temp_product_ids)");
		while($weight = $weightsResult->fetch_assoc()){ array_push($weight_ids, $weight['id']); }

		
		$pallet_ids = implode(',', $pallet_ids);
		$product_ids = implode(',', $product_ids);
		$weight_ids = implode(',', $weight_ids);

		// Delete related pallets
		$deletePalletResult =  prepareExecuteQuery("DELETE FROM `pallet` WHERE id IN ($pallet_ids)");

		// Delete related products
		$deleteProductResult =  prepareExecuteQuery("DELETE FROM `product` WHERE id IN ($product_ids)");

		// Delete related weight entries
		$deleteWeightsResult =  prepareExecuteQuery("DELETE FROM `weights` WHERE id IN ($weight_ids)");

		// Delete the intake 
		$deleteIntakeResult =  prepareExecuteQuery("DELETE FROM `intake` WHERE id = ?",'i',[$intake_id]);
	}
	
	function getPallet($id){
		global $mysqli;
		
		$x = "SELECT * FROM `pallet` WHERE id= ?";
		$y = prepareExecuteQuery($x,'i',[$id]);
		
		$row = $y->fetch_assoc();
		
		return $row;	
	}
	
	function getPalletsOnThisIntake($intake_id){
		global $mysqli;
		
		$x = "SELECT * FROM `pallet` WHERE intake_id=?";
		$y = prepareExecuteQuery($x,'i',[$intake_id]);
		
		return $y;
	}
	
	
	function getPalletsOnThisIntake2($intake_id){
		global $mysqli;
		
		$xKez = "SELECT id FROM `pallet` WHERE intake_id=?";
		$yKez = prepareExecuteQuery($xKez,'i',[$intake_id]);
		
		$counter = $yKez->num_rows;
		
		if($counter > 0){
			$ids = array();
			
			
			while($allPallets = $yKez->fetch_assoc()){
				$palletid = $allPallets['id'];
				
				$x1Kez = "SELECT id FROM product WHERE pallet_id=?";
				$y1Kez = prepareExecuteQuery($x1Kez,'i',[$palletid]);
				
				$count = $y1Kez->num_rows;
				
				if($count > 0){
					$ids []= $palletid;
				}
				
			}
						
			$x2Kez = "SELECT * FROM `pallet` WHERE id IN (".implode(",",array_fill(0,count($ids),"?")).")";
			$y2Kez = prepareExecuteQuery($x2Kez,str_repeat("i",count($ids)),$ids);
		}else{
			$y2Kez = prepareExecuteQuery("SELECT * FROM `pallet` WHERE id = -1");
		}
		return $y2Kez;
	}
	
	
	function addIntake($supplier_id, $date_received, $vehicle_reg, $vehicle_temperature){
		global $mysqli;
		
		$x = "INSERT into `intake` (supplier_id, date_received, vehicle_reg, vehicle_temperature) VALUES (?,?,?,?)";
		$y = prepareExecuteQuery($x,'isss',[$supplier_id,$date_received,$vehicle_reg,$vehicle_temperature]);
	}
	

	
	function getPallets($id){
		global $mysqli; 
		
		$x = "SELECT * FROM `pallets` WHERE intake_id = ?";
		$y = prepareExecuteQuery($x,'i',[$id]);
		
		
		return $y;
	}
	
	
	
	function getStatus($id){
		global $mysqli;
		
		$x = "SELECT * FROM statuses WHERE id = ?";
		$y = prepareExecuteQuery($x,'i',[$id]);
		
		$row = $y->fetch_assoc();
		
		return $row['status_name']; 
	}
	

    function cutsFromCutGroup($species_id, $cutgroup_id){
        global $mysqli;

        $x = "SELECT id from `cuts` WHERE species_id=? && cutgroup_id=?";
        $y = prepareExecuteQuery($x,'ii',[$species_id,$cutgroup_id]);
        
        $data = [];
        
        while($row = $y->fetch_assoc()){
            array_push($data, $row['id']);
        }

        return $data;
    }


	function getCutGroupNameFromCut($cut_id){
		global $mysqli;

		$cutResult = prepareExecuteQuery("SELECT cutgroup_id FROM `cuts` WHERE id=?",'i',[$cut_id]);
		$cutData = $cutResult->fetch_assoc();
		$cutgroup_id = $cutData['cutgroup_id'];

		$cutGroupResult = prepareExecuteQuery("SELECT `name` FROM cutgroups WHERE id=?",'i',[$cutgroup_id]);
		$cutGroupData = $cutGroupResult->fetch_assoc();

		return $cutGroupData['name'];
	}


    function palletIDsFromIntakeID($intake_id){
        global $mysqli;

        $x = "SELECT id from `pallet` WHERE intake_id=?";
        $y = prepareExecuteQuery($x,'i',[$intake_id]);
        
        $data = [];
        
        while($row = $y->fetch_assoc()){
            array_push($data, $row['id']);
        }

        return $data;
    }


	function getPalletCollection($pallet_id){

    	global $mysqli;

        $x = "SELECT intake_id FROM `pallet` WHERE id = ?";
        $y = prepareExecuteQuery($x,'i',[$pallet_id]);

        $intake_id = null;
        
        while($row = $y->fetch_assoc()){
            $intake_id = $row['intake_id'];
        }

        if(!empty($intake_id)){

        	return palletIDsFromIntakeID($intake_id);

        }else{

        	return null;
		}
    }

    function checkForSoldPallets($pallets){

    	global $mysqli;

        $x = "SELECT * from `product` inner join `weights` on product.id = weights.product_id WHERE product.pallet_id in (".implode(",",array_fill(0,count($pallets),"?")).") AND weights.status_id = 1";
        $y = prepareExecuteQuery($x,str_repeat('i',count($pallets)),$pallets);
        return $mysqli->num_rows;
        
    }
	
	function productCountOnIntakeNotCosted($intake_id){
        global $mysqli;

		$palletResult = prepareExecuteQuery("SELECT GROUP_CONCAT(id) AS ids FROM pallet WHERE intake_id=?",'i',[$intake_id]);
		$palletData = $palletResult->fetch_assoc();
		$pallet_ids = $palletData['ids'];		

		$productResult = prepareExecuteQuery("SELECT count(id) as count FROM product WHERE (cost is null && pallet_id IN ($pallet_ids)) || (cost = '0.00' && pallet_id IN ($pallet_ids))");
		$productData = $productResult->fetch_assoc();

		return $productData['count'];
	}

	function totalOutstandingForCustomer($customer_id){
		$customerPicksheets = prepareExecuteQuery("SELECT pickerSheets.*, SUM(invoice_payments.amount) as paid FROM `pickerSheets` left join invoice_payments on invoice_payments.payment_method != 'CREDIT_NOTE' && pickerSheets.id = invoice_payments.invoice_id WHERE (pickerSheets.completed = 1 AND pickerSheets.customer_id=?) GROUP by pickerSheets.id ORDER BY pickerSheets.id ASC",'i',[$customer_id]);
		
		$totalOutstanding = 0.00;

		while($picksheet = $customerPicksheets->fetch_assoc()){
			
				
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
		global $mysqli;

		$outpalletQuery = "SELECT * FROM `palletsOut` WHERE pickersheet_id=?";
		$outpalletResult2 = prepareExecuteQuery($outpalletQuery,'i',[$pickersheet_id]);
                
		$outpalletCount = $outpalletResult2->num_rows;
		$totalPrice = 0;

		while($outpallet = $outpalletResult2->fetch_assoc()){
			$weightids = explode(',', $outpallet['weight_ids']);
 
			$productIDArray = array();
			
			$x = "SELECT * FROM `weights` WHERE id IN (".$outpallet['weight_ids'].")";
			$y = prepareExecuteQuery($x);
			$weightsByProductID = array();
			while($weight = $y->fetch_assoc())
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
				$y1 = prepareExecuteQuery($x1);
				$product = $y1->fetch_assoc();

				$count = count($weightsByProductID[$productID]);
								
				$productID = $product['id'];
				$sheetproduct = $pickersheet_id . "_" . $productID;
				if(!array_key_exists($sheetproduct, $weightsByProductID)){
					$howManyX = "SELECT * FROM `pickerItems` WHERE pickersheet_id=? AND product_id=$productID LIMIT 1";
					$howManyY = prepareExecuteQuery($howManyX,'i',[$pickersheet_id]);
					$pickerItemByProductID[$sheetproduct] = $howManyY->fetch_assoc();
				}
				
				$pickerItem = $pickerItemByProductID[$sheetproduct];
						
				$kg = 0;
						
				foreach($weightsByProductID[$productID] as $weightRow){
					
					if($weightRow['weight_tear'] == $weightRow['weight_gross']){
						$tw = (float)$weightRow['weight_gross'];
					}else{
						$tw = (float)$weightRow['weight_gross'] - $weightRow['weight_tear'];
					}			
					$kg = $kg + $tw;

					$kg = number_format($kg, 3, '.', '');
				}
						
				if($product['unit'] == 'PPC'){
					$totalPrice += number_format((float)$count * (float)$pickerItem['price'], 2, '.', '');
				}else{
					$totalPrice += number_format((float)$kg * (float)$pickerItem['price'], 2, '.', '');
				}
			}
		}
        
		return $totalPrice;
	}

	function getInvoiceCreditNoteTotal($invoice_id){
		global $mysqli;

		$db_result = prepareExecuteQuery("SELECT SUM(amount) as total_credit FROM `invoice_payments` WHERE invoice_id=? && payment_method='CREDIT_NOTE'",'i',[$invoice_id]); 
		$data = $db_result->fetch_assoc();

		return (float) $data['total_credit'];

	}

	function invoiceTotalCost($pickersheet_id){
		global $mysqli;

		$totalCost = 0;
		
		$outPalletQuery = "SELECT * FROM `palletsOut` WHERE pickersheet_id=?";
		$outPalletsResult = prepareExecuteQuery($outPalletQuery,'i',[$pickersheet_id]);
                
		$outPalletCount = $outPalletsResult->num_rows;

		while($outPallet = $outPalletsResult->fetch_assoc()){
			$weight_ids = $outPallet['weight_ids'];


			$weightCountResult = prepareExecuteQuery("SELECT count(id) as count FROM `weights` WHERE id in ($weight_ids)");
			$weightCountData = $weightCountResult->fetch_assoc();
			$this_weight_count = $weightCountData['count'];

			$weightResult = prepareExecuteQuery("SELECT * FROM `weights` WHERE id in ($weight_ids) GROUP BY product_id");
			$weightData = $weightResult->fetch_assoc();
			$product_id = $weightData['product_id'];
			
			
			$productResult = prepareExecuteQuery("SELECT * FROM product WHERE id = $product_id");
			$productData = $productResult->fetch_assoc();

			$totalCost += ($productData['cost'] * $this_weight_count);
		}

		return $totalCost;
	}

	function creditNoteTotal($invoice_payment_id){
		global $mysqli;

		$price = 0;
		$creditNoteResult = prepareExecuteQuery("SELECT * FROM `credit_note_items` WHERE payment_id =?",'i',[$invoice_payment_id]);

		while($creditNoteItem = $creditNoteResult->fetch_assoc()){
			if($creditNoteItem['product_id'] == 0 || weightTypeOfProduct($creditNoteItem['product_id']) == 'PPC'){ # bespoke credit note, not attached product
				$price += (float)$creditNoteItem['price'] * (float)$creditNoteItem['quantity'];	
			}else{
				$weight = weightFromProductIDArray([$creditNoteItem['product_id']]);
				$price += ((float)$creditNoteItem['price'] * (float)$weight);
			}
		}
		
		return ceilDec($price,2);
	}

	function weightCountOfProductOnPicksheet($pick_id, $productID){
		global $mysqli;

		$outpalletQuery = "SELECT * FROM `palletsOut` WHERE pickersheet_id=?";
        	$outpalletResult2 = prepareExecuteQuery($outpalletQuery,'i',[$pick_id]);
    
		$total_count = 0;

    		while($outpallet = mysqli_fetch_array($outpalletResult2)){
            		$weightids = explode(',', $outpallet['weight_ids']);

            		$x2 = "SELECT * FROM `weights` WHERE id IN (".implode(",",$weightids).") && status_id='1' && product_id=?";

            		$y2 = prepareExecuteQuery($x2,'i',[$productID]);
	
			$count = mysqli_num_rows($y2);
			$total_count += $count;
        }


		return $total_count;
	}

	function weightValueOfProductOnPicksheet($pick_id, $productID){
		global $mysqli;
		
		$outpalletQuery = "SELECT * FROM `palletsOut` WHERE pickersheet_id=?";
        $outpalletResult2 = prepareExecuteQuery($outpalletQuery,'i',[$pick_id]);
        
		$weight = 0;
        
		while($outpallet = mysqli_fetch_array($outpalletResult2)){
            $weightids = $outpallet['weight_ids'];
			
			$x = "SELECT * FROM `weights` WHERE id IN ($weightids) AND product_id = ?";
			$y = prepareExecuteQuery($x,'i',[$productID]);
            
			while($row = $y->fetch_assoc()){
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
		global $mysqli;
		$price = 0;
		$paymentsResult = prepareExecuteQuery("SELECT id FROM `invoice_payments` WHERE invoice_id=? AND payment_method = 'CREDIT_NOTE'",'i',[$invoice_id]);
		while ($paymentData = $paymentsResult->fetch_assoc())
		{
			$price = $price + (float)creditNoteTotal($paymentData['id']);
		}		
		return ceilDec($price,2);	
 	}

	function doesInvoiceHaveReturns($invoice_id){
		global $mysqli;

		$result = prepareExecuteQuery("SELECT count(id) as count FROM `intake` WHERE returned=1 && delivery_note_number=?",'i',[$invoice_id]);
		$data = $result->fetch_assoc();

		if($data['count'] == 0){
			return false;
		}

		return true;
	}

	function doesInvoiceHaveCreditNote($invoice_id){
		global $mysqli;

		$result = prepareExecuteQuery("SELECT count(id) as count FROM `invoice_payments` WHERE payment_method='CREDIT_NOTE' && invoice_id=?",'i',[$invoice_id]);
		$data = $result->fetch_assoc();

		if($data['count'] == 0){
			return false;
		}

		return true;
	}

	function getInvoiceCreditNotes($invoice_id){
		global $mysqli;

		$result = prepareExecuteQuery("SELECT * FROM `invoice_payments` WHERE payment_method='CREDIT_NOTE' && invoice_id=?",'i',[$invoice_id]);
		$array = array();
		while ($row = $result->fetch_assoc())
		{	
			$row['noteItems'] = array();
			$cnq = prepareExecuteQuery(
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
			
				while ($cnr = $cnq->fetch_assoc())
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
		global $mysqli;
		$name = $mysqli->real_escape_string($name);
		$tests = array(
			$name,
			str_replace(" ","",$name),
			str_replace(" & "," and ",$name),
			str_replace("&"," & ",$name)
		);
		$allSearchControl = "";
		if ($allSearch == false) $allSearchControl ="AND (`credit_terms` > -1 || `credit_enabled` = 1)";
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
				$y = prepareExecuteQuery($x);
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
		global $mysqli;
		global $userid;
		$body = $mysqli->real_escape_string($body);
		$x = "INSERT INTO `comment_logging` (`type`,`user_id`,`entity_id`,`body`) VALUES (?,?,?,?)";			
		$y = prepareExecuteQuery($x,'siis',[$type,$userid,$entity_id,$body]);
	}
	function debuglogging($body){
		global $conn;
		global $userid;
		$body = mysqli_real_escape_string($conn,$body);
		$arr = mysqli_real_escape_string($conn,json_encode(array("GET"=>$_GET,"POST"=>$_POST)));
		$REQUEST_URI = mysqli_real_escape_string($conn,$_SERVER['REQUEST_URI']);
		$session_id = mysqli_real_escape_string($conn,session_id());
		$x = "INSERT INTO `debug_logging` (`page`,`request`,`user_id`,`session_id`,`body`) VALUES ('".$REQUEST_URI."','".$arr."','$userid','".$session_id."','$body')";			
		mysqli_query($conn, $x);
	}
	function queryproxy(mysqli $conn, string $query)
	{
		debuglogging($query);
		$outcome = mysqli_query($conn,$query);
		if ($outcome == false)
		{
			debuglogging(mysqli_error($conn));
		}
		return $outcome;
	}
	CONST PAYMENT_METHODS = ['CHEQUE', 'BACS', 'CASH','CREDIT_NOTE'];	
?>