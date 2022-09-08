<?php
	require('../functions.php');
	
	$picker_id = $_POST['picker_id'];
	$customer_id = $_POST['customer_id'];
	$estimated_delivery_date = $_POST['estimated_delivery_date'];
	
	$orderReferenceNumber = $_POST['orderReferenceNumber'];
	$weightnote = $_POST['weightnote'];
	$picksheet_note = $_POST['picksheet_note'];

	//$user_from_id = $_SESSION['USER'];
	$user_from_id = $_POST['sales_person'];
	$addressid = $_POST['addressid'];
	
	if ($user_from_id == "" || $customer_id == 0 || $customer_id == "")
	{
?>
	<script type="text/javascript">
		alert("An Error Occurred! Could not complete sale!");
		window.location.href = "<?php echo $domain; ?>productpicker.php";
	</script>
<?php
		die();
	}

	//$x = "UPDATE `customers` SET override=0 WHERE id='$customer_id'";
	//$y = mysqli_query($conn, $x) or die(mysqli_error($conn));

	$addressline1 = mysqli_real_escape_string($conn, $_POST['addressline1']);
	$addressline2 = mysqli_real_escape_string($conn, $_POST['addressline2']);
	$addressline3 = mysqli_real_escape_string($conn, $_POST['addressline3']);
	$addressline4 = mysqli_real_escape_string($conn, $_POST['addressline4']);
	$addresspostcode = mysqli_real_escape_string($conn, $_POST['addresspostcode']);
	$deliverynumber = mysqli_real_escape_string($conn, $_POST['deliverynumber']);
		
	$addressQuery = "address{$addressid}_1='$addressline1', address{$addressid}_2='$addressline2', address{$addressid}_3='$addressline3', address{$addressid}_4='$addressline4', postcode_{$addressid}='$addresspostcode', address{$addressid}_number='$deliverynumber'";

	$addressUpdateQuery = mysqli_query($conn, "UPDATE `customers` SET $addressQuery WHERE id='$customer_id' LIMIT 1 ");


		
	$today = date('Y-m-d');

	
	$x = "INSERT INTO `pickerSheets` (picker_id,user_from_id,customer_id,estimated_delivery_date,orderReferenceNumber,date_completed,addressid,picksheet_note) VALUES ('$picker_id','$user_from_id','$customer_id','$estimated_delivery_date','$orderReferenceNumber','$today','$addressid','$picksheet_note')";
	$y = mysqli_query($conn, $x) or die(mysqli_error($conn));
	
	$_POST['id'] = $pickersheet_id = mysqli_insert_id($conn);
	
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
	require_once('../ajax/generatePDFsaleconfirm.php');
	
?>
<script>
	alert("Done!");
	window.location = '../menu.php';
</script>