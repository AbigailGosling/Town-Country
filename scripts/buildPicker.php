<?php
	require('../functions.php');

	
	$picker_id = $_POST['picker_id'];
	$customer_id = $_POST['customer_id'];
	$estimated_delivery_date = $_POST['estimated_delivery_date'];
	$comments = $_POST['comments'];
	
	$orderReferenceNumber = $_POST['orderReferenceNumber'];
	$weightnote = $_POST['weightnote'];
	
	
	$deliverynumber = $_POST['deliverynumber'];
	$deliveryaddress = $_POST['deliveryaddress'];
	$addressid = $_POST['addressid'];
	
	$x = "UPDATE `customers` SET override=0 WHERE id='$customer_id'";
	$y = mysqli_query($conn, $x) or die(mysqli_error($conn));
	
	$today = date('Y-m-d');
	
	
	$user_from_id = $_SESSION['USER'];
	
	$x = "INSERT INTO `pickerSheets` (picker_id,user_from_id,customer_id,estimated_delivery_date,comments,orderReferenceNumber,date_completed,addressid) VALUES ('$picker_id','$user_from_id','$customer_id','$estimated_delivery_date','$comments','$orderReferenceNumber','$today','$addressid')";
	$y = mysqli_query($conn, $x) or die(mysqli_error($conn));
	
	$pickersheet_id = mysqli_insert_id($conn);
	
	$index = 0;
	foreach ($_POST['basketRow'] as $key => $value) {
	
		$price_type = $_POST['price_type'];
		
		$details = explode('-', $value);
		
		$product_id = $details[0];
		$quantity = $details[1];
		$cut_id = $details[2];
		
		
		# WEIGHT NOTE START
		$weightnote = $_POST['weightnote_' . $product_id];
 		$x = "UPDATE `product` SET weightnote = '$weightnote' WHERE id='$product_id' LIMIT 1";
		$y = mysqli_query($conn, $x);
		# WEIGHT NOTE END
		
		$price_type = $price_type[$index];
		
		$price = $_POST['price_' . $product_id];
		
		for($i=0;$i<$quantity;$i++){
			$x = "INSERT into `pickerItems` (pickersheet_id,product_id,price,price_type,comment) VALUES ('$pickersheet_id','$product_id','$price','$price_type','$comment')";
			$y = mysqli_query($conn, $x);
		}
	 
		$index++;
	}
	
?>
<script type="text/javascript">
	window.location.href = "<?php echo $domain; ?>/menu.php?msg=Pick Form Sent!&pickerSheets=<?php echo $pickersheet_id; ?>";
</script>