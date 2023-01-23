<?php
	require(__DIR__.'/../functions.php');
	
	$picker_id = request('picker_id');
	$customer_id = request('customer_id');
	$estimated_delivery_date = request('estimated_delivery_date');
	
	$orderReferenceNumber = request('orderReferenceNumber');
	$weightnote = request('weightnote');
	$picksheet_note = request('picksheet_note');

	//$user_from_id = $_SESSION['USER'];
	$user_from_id = request('sales_person');
	$addressid = request('addressid');
	
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
	//$y = prepareExecuteQuery($x) or die(mysqli_error($conn));

	$addressline1 = $mysqli->real_escape_string( request('addressline1'));
	$addressline2 = $mysqli->real_escape_string( request('addressline2'));
	$addressline3 = $mysqli->real_escape_string( request('addressline3'));
	$addressline4 = $mysqli->real_escape_string( request('addressline4'));
	$addresspostcode = $mysqli->real_escape_string( request('addresspostcode'));
	$deliverynumber = $mysqli->real_escape_string( request('deliverynumber'));
		
	$addressQuery = "address{$addressid}_1='$addressline1', address{$addressid}_2='$addressline2', address{$addressid}_3='$addressline3', address{$addressid}_4='$addressline4', postcode_{$addressid}='$addresspostcode', address{$addressid}_number='$deliverynumber'";

	$addressUpdateQuery = prepareExecuteQuery("UPDATE `customers` SET $addressQuery WHERE id = ? LIMIT 1",'i',[$customer_id]);


		
	$today = date('Y-m-d');

	
	$x = "INSERT INTO `pickerSheets` (picker_id,user_from_id,customer_id,estimated_delivery_date,orderReferenceNumber,date_completed,addressid,picksheet_note) VALUES (?,?,?,?,?,?,?,?)";
	$y = prepareExecuteQuery($x,'iiisssss',[$picker_id,$user_from_id,$customer_id,$estimated_delivery_date,$orderReferenceNumber,$today,$addressid,$picksheet_note]);
	
	request('id') = $pickersheet_id = mysqli_insert_id($conn);
	
	$index = 0;
	foreach (request('basketRow') as $key => $value) {

		$details = explode('-', $value);
		$product_id = $details[0];
		$quantity = $details[1];
		$cut_id = $details[2];
		
		$price_type = request('price_type');
		$target_weight = (int) request('target_weight_' . $product_id);
		
		if(empty($target_weight)){ $target_weight = 0; }
		
		if(!is_int($target_weight)){ $target_weight = 0; }
	
		
		$price_type = $price_type[$index];
		
		$price = request('price_' . $product_id);
		
		for($i=0;$i<$quantity;$i++){
			$x = "INSERT into `pickerItems` (pickersheet_id,product_id,price,price_type,comment,target_weight) VALUES (?,?,?,?,?,?)";
			$y = prepareExecuteQuery($x,'iissss',[$pickersheet_id,$product_id,$price,$price_type,$comment,$target_weight]);
		}
	 
		$index++;
	}
	$x = "UPDATE `customers` SET override = 0 WHERE id = ?";
	$y = prepareExecuteQuery($x,'i',[$customer_id]);
	require_once('../ajax/generatePDFsaleconfirm.php');
	
?>
<script>
	alert("Done!");
	window.location = '../menu.php';
</script>