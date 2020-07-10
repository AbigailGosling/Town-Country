<?php
	require('../functions.php');
	
	$id = mysqli_real_escape_string($conn, $_GET['id']);
	$pickersheet_id = mysqli_real_escape_string($conn, $_GET['id']);
	
	
	# START update customer price
	$x = "SELECT * FROM pickerSheets WHERE id='$pickersheet_id' LIMIT 1";
	$y = mysqli_query($conn, $x);
	$picksheet = mysqli_fetch_array($y);
	$customer_id = $picksheet['customer_id'];
	
	$val = getPicksheetValue($pickersheet_id);
	
	$x1 = "SELECT * FROM `customers` WHERE id='$customer_id'";
	$y1 = mysqli_query($conn, $x1);
	$customer = mysqli_fetch_array($y1);
	
	$current_outstanding = (float) $customer['current_outstanding'];
	$newVal = $current_outstanding + (float) $val;

	$x = "UPDATE `customers` SET current_outstanding='$newVal' WHERE id ='$customer_id' LIMIT 1";
	$y = mysqli_query($conn, $x);
	# END update customer price
	
	
	$date_completed = date('Y/m/d');
    
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
			// echo '<br/>';
			// echo $weight['weight_gross'];
			
		}
		
	}
	$x2 = "UPDATE `pickerItems` SET `status` = '1' WHERE pickersheet_id='$pickersheet_id'";
	$y2 = mysqli_query($conn, $x2);
    
    
	// $x2 = "SELECT * FROM `pickerItems` WHERE picksheet_id='$pickersheet_id'";
	// $y2 = mysqli_query($conn, $x2);
	
	// while($pickerItem = mysqli_fetch_array($y2)){
		// $product_id = $pickerItem['product_id'];
		
		// $x3 = "SELECT * FROM `weights` WHERE product_id = '$product_id'";
		// $y3 = mysqli_query($conn, $x3);
		
		
		
	// }
	
	
	
	// $idBang = explode(",", $ids);
	
	// foreach($idBang as $id) { 
		// $pallet_id = $id;
		
		 	
		// $x = "INSERT into `pickedSheet` (pickersheet_id,product_id) VALUES ('$pickersheet_id','$pallet_id')";
		// $y = mysqli_query($conn, $x) or die(mysqli_error($conn));
	// }
	
	
?>
<script>
	window.location = '../menu.php?msg=Picking Sheet Submitted!';
</script>
