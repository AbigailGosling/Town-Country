<?php
	require('../functions.php');
	$id = mysqli_real_escape_string($conn, $_GET['id']);
	$pickersheet_id = mysqli_real_escape_string($conn, $_GET['id']);
	$type = mysqli_real_escape_string($conn, $_POST['sheet_type']);

	/* START - Get all product IDs on the picksheet */
	$product_ids = array();
	$result_product = mysqli_query($conn, "SELECT product_id FROM `pickerItems` WHERE pickersheet_id='$pickersheet_id' GROUP BY product_id");
	while($product = mysqli_fetch_array($result_product)){
		array_push($product_ids, $product['product_id']);
	} 
	$product_ids = implode(',', $product_ids);
	/* END - Get all product IDs on the picksheet */


	$result_fresh = mysqli_query($conn, "SELECT id FROM `product` WHERE id IN ($product_ids) && cooling_id='1' LIMIT 1");
	$count_fresh = mysqli_num_rows($result_fresh);

	$result_frozen= mysqli_query($conn, "SELECT id FROM `product` WHERE id IN ($product_ids) && cooling_id IN ('2','3') LIMIT 1");
	$count_frozen = mysqli_num_rows($result_frozen);
	

	// if you just submitted the fresh products OR theres no fresh products at all
	if($type == 'fresh' || $count_fresh == 0){
		// mark fresh as completed
		$updateStatusQuery = "UPDATE `pickerSheets` SET completed_fresh='1' WHERE id='$pickersheet_id'";
		mysqli_query($conn, $updateStatusQuery);
	}

	// if you just submitted the frozen products OR theres no frozen products at all
	if($type == 'frozen' || $count_frozen == 0){
		// mark frozen as completed
		$updateStatusQuery = "UPDATE `pickerSheets` SET completed_frozen='1' WHERE id='$pickersheet_id'";
		mysqli_query($conn, $updateStatusQuery);
	}
	$picksheetResult = mysqli_query($conn,  "SELECT * FROM pickerSheets WHERE id='$pickersheet_id' LIMIT 1");
	$picksheet = mysqli_fetch_array($picksheetResult);
	$customer_id = $picksheet['customer_id'];
	mysqli_query($conn, "DELETE FROM customer_outstanding_cache WHERE customer_id = ".$customer_id);
	if($picksheet['completed_frozen'] == 1 && $picksheet['completed_fresh'] == 1){
		
		$updateStatusQuery = "UPDATE `pickerSheets` SET completed='1' WHERE id='$pickersheet_id'";
		mysqli_query($conn, $updateStatusQuery);

		$val = getPicksheetValue($pickersheet_id);
		
		$x1 = "SELECT * FROM `customers` WHERE id='$customer_id'";
		$y1 = mysqli_query($conn, $x1);
		$customer = mysqli_fetch_array($y1);
		
		$current_outstanding = (float) $customer['current_outstanding'];
		$newVal = $current_outstanding + (float) $val;

		$x = "UPDATE `customers` SET current_outstanding='$newVal' WHERE id ='$customer_id' LIMIT 1";
		$y = mysqli_query($conn, $x);
		# END update customer price
		
		date_default_timezone_set("Europe/London");

		$date_completed = date('Y/m/d H:i:s');
		
		$session_USERID = $_SESSION['USER'];

		$x = "UPDATE `pickerSheets` SET completed = '1', completedby_userid='$session_USERID', date_completed = '$date_completed' WHERE id = '$id'";
		$y = mysqli_query($conn, $x);
			
		
		
		$x = "SELECT * FROM `palletsOut` WHERE pickersheet_id='$id'";
		$y = mysqli_query($conn, $x);
		
		while($row = mysqli_fetch_array($y)){
			// echo 'weight_ids = ' . $row['weight_ids'];
		
			$weightIDArray = explode(',', $row['weight_ids']);
			
			foreach($weightIDArray as $weightID){
				$x1 = "SELECT * FROM `weights` WHERE id='$weightID'";
				$y1 = mysqli_query($conn, $x1);
				
						
				$x2 = "UPDATE `weights` SET status = '1' WHERE id='$weightID'";
				$y2 = mysqli_query($conn, $x2);
				
				$weight = mysqli_fetch_array($y1);
			}
			
		}
		$x2 = "UPDATE `pickerItems` SET `status` = '1' WHERE pickersheet_id='$pickersheet_id'";
		$y2 = mysqli_query($conn, $x2);
	}
?>
<script>
	alert("Picking Sheet Submitted!");
	window.location = '../menu.php';
</script>
