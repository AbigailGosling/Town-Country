<?php
	require(__DIR__.'/../functions.php');
	
	$picker_id = request()->input('picker_id');
	$customer_id = request()->input('customer_id');
	$estimated_delivery_date = request()->input('estimated_delivery_date');
	
	$orderReferenceNumber = request('orderReferenceNumber');
	$weightnote = request()->input('weightnote');
	$picksheet_note = request()->input('picksheet_note');

	//$user_from_id = $_SESSION['USER'];
	$user_from_id = request()->input('sales_person');
	$addressid = request()->input('addressid');
	$transaction_id = request()->input('transaction_id');
	
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

	$addressline1 = $mysqli->real_escape_string( request()->input('addressline1'));
	$addressline2 = $mysqli->real_escape_string( request()->input('addressline2'));
	$addressline3 = $mysqli->real_escape_string( request()->input('addressline3'));
	$addressline4 = $mysqli->real_escape_string( request()->input('addressline4'));
	$addresspostcode = $mysqli->real_escape_string( request()->input('addresspostcode'));
	$deliverynumber = $mysqli->real_escape_string( request()->input('deliverynumber'));
		
	$addressQuery = "address{$addressid}_1='$addressline1', address{$addressid}_2='$addressline2', address{$addressid}_3='$addressline3', address{$addressid}_4='$addressline4', postcode_{$addressid}='$addresspostcode', address{$addressid}_number='$deliverynumber'";

	$addressUpdateQuery = prepareExecuteQuery("UPDATE `customers` SET $addressQuery WHERE id = ? LIMIT 1",'i',[$customer_id]);
		
	$today = date('Y-m-d');
	if ($transaction_id != null && $transaction_id != "")
	{
		$transactCheck = prepareExecuteQuery("SELECT * FROM `pickerSheets` WHERE transaction_id = ?",'s',[$transaction_id]);
		if ($transactCheck->num_rows > 0) {
			throw new \Exception("duplicate transaction");
			die();
		}
	}
	$x = "INSERT INTO `pickerSheets` (picker_id,user_from_id,customer_id,estimated_delivery_date,orderReferenceNumber,date_completed,addressid,picksheet_note,transaction_id) VALUES (?,?,?,?,?,?,?,?,?)";
	$y = prepareExecuteQuery($x,'iiissssss',[$picker_id,$user_from_id,$customer_id,$estimated_delivery_date,$orderReferenceNumber,$today,$addressid,$picksheet_note,$transaction_id],true);
	
	$pickersheet_id = $y;
	
	$index = 0;
	foreach (request('basketRow') as $key => $value) {

		$details = explode('-', $value);
		$product_id = $details[0];
		$quantity = $details[1];
		$cut_id = $details[2];
		
		$price_type = request()->input('price_type');
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
	require_once(__DIR__.'/../ajax/generatePDFsaleconfirm.php');
	renderPDF($pickersheet_id);
	
?>
<script>
	alert("Done!");
	window.location = '../menu.php';
</script>