<?php
	require('../functions.php');
	
	$picker_id = $_POST['picker_id'];
	$customer_id = $_POST['customer_id'];
	$estimated_delivery_date = $_POST['estimated_delivery_date'];
	
	$orderReferenceNumber = $_POST['orderReferenceNumber'];
	$weightnote = $_POST['weightnote'];
	$picksheet_note = $_POST['picksheet_note'];
 
	$addressid = $_POST['addressid'];
	
	$x = "UPDATE `customers` SET override=0 WHERE id='$customer_id'";
	$y = mysqli_query($conn, $x) or die(mysqli_error($conn));

	$addressline1 = mysqli_real_escape_string($conn, $_POST['addressline1']);
	$addressline2 = mysqli_real_escape_string($conn, $_POST['addressline2']);
	$addressline3 = mysqli_real_escape_string($conn, $_POST['addressline3']);
	$addressline4 = mysqli_real_escape_string($conn, $_POST['addressline4']);
	$addresspostcode = mysqli_real_escape_string($conn, $_POST['addresspostcode']);
	$deliverynumber = mysqli_real_escape_string($conn, $_POST['deliverynumber']);
		
	switch ($addressid) {
		case 1:
			$addressQuery = "address1_1='$addressline1', address1_2='$addressline2', address1_3='$addressline3', address1_4='$addressline4', postcode_1='$addresspostcode', address1_number='$deliverynumber'";
			break;
		case 2:
			$addressQuery = "address2_1='$addressline1', address2_2='$addressline2', address2_3='$addressline3', address2_4='$addressline4', postcode_2='$addresspostcode', address2_number='$deliverynumber'";
			break;
		case 3:
			$addressQuery = "address3_1='$addressline1', address3_2='$addressline2', address3_3='$addressline3', address3_4='$addressline4', postcode_3='$addresspostcode', address3_number='$deliverynumber'";
			break;
	}

	$addressUpdateQuery = mysqli_query($conn, "UPDATE `customers` SET $addressQuery WHERE id='$customer_id' LIMIT 1 ");


		
	$today = date('Y-m-d');
	
	
	//$user_from_id = $_SESSION['USER'];
	$user_from_id = $_POST['sales_person'];
	
	$x = "INSERT INTO `pickerSheets` (picker_id,user_from_id,customer_id,estimated_delivery_date,orderReferenceNumber,date_completed,addressid,picksheet_note) VALUES ('$picker_id','$user_from_id','$customer_id','$estimated_delivery_date','$orderReferenceNumber','$today','$addressid','$picksheet_note')";
	$y = mysqli_query($conn, $x) or die(mysqli_error($conn));
	
	$pickersheet_id = mysqli_insert_id($conn);
	
	$index = 0;
	foreach ($_POST['basketRow'] as $key => $value) {

		$details = explode('-', $value);
		$product_id = $details[0];
		$quantity = $details[1];
		$cut_id = $details[2];
		
		$price_type = $_POST['price_type'];
		$target_weight = (int) $_POST['target_weight_' . $product_id];
		
		if(empty($target_weight)){ $target_weight = 0; }
		
		if(!is_int($target_weight)){ $target_weight = 0; }
	
		
		$price_type = $price_type[$index];
		
		$price = $_POST['price_' . $product_id];
		
		for($i=0;$i<$quantity;$i++){
			$x = "INSERT into `pickerItems` (pickersheet_id,product_id,price,price_type,comment,target_weight) VALUES ('$pickersheet_id','$product_id','$price','$price_type','$comment','$target_weight')";
			$y = mysqli_query($conn, $x);
		}
	 
		$index++;
	}
	
?>
<script type="text/javascript">
	window.location.href = "<?php echo $domain; ?>/menu.php?msg=Pick Form Sent!&pickerSheets=<?php echo $pickersheet_id; ?>";
</script>