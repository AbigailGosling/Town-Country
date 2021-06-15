<?php
	require('../functions.php');
	
	$picker_id = $_POST['picker_id'];
	$customer_id = $_POST['customer_id'];
	$estimated_delivery_date = $_POST['estimated_delivery_date'];
	
	$orderReferenceNumber = $_POST['orderReferenceNumber'];
	$weightnote = $_POST['weightnote'];
	$picksheet_note = $_POST['picksheet_note'];
 
	$deliverynumber = $_POST['deliverynumber'];
	$deliveryaddress = $_POST['deliveryaddress'];
	$addressid = $_POST['addressid'];
	
	$x = "UPDATE `customers` SET override=0 WHERE id='$customer_id'";
	$y = mysqli_query($conn, $x) or die(mysqli_error($conn));
	
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
		$target_weight = $_POST['target_weight_' . $product_id];
		
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