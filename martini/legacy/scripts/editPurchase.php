<?php
	require(__DIR__.'/../functions.php');
	
	$id = $mysqli->real_escape_string( request('id'));
	$supplier_id = $mysqli->real_escape_string( request('supplier_id'));
	$purchased_by = $mysqli->real_escape_string( request('purchased_by'));
	$date_purchased = $mysqli->real_escape_string( request('date_purchased'));
	$date_due = $mysqli->real_escape_string( request('date_due'));
	$transportation = $mysqli->real_escape_string( request('transportation'));
	$haulier = $mysqli->real_escape_string( request('haulier'));
	$direct_drop = $mysqli->real_escape_string( request('direct_drop'));
	$temperature_id = $mysqli->real_escape_string( request('temperature_id'));
	
	$date_purchased = str_replace('/', '-', $date_purchased);
	$date_purchased = date('Y-m-d 00:00:00', strtotime($date_purchased));
	
	$date_due = str_replace('/', '-', $date_due);
	$date_due = date('Y-m-d H:00:00', strtotime($date_due));
	
	$comments = $mysqli->real_escape_string( request('comments'));
	$booking_ref_number = $mysqli->real_escape_string( request('booking_ref_number'));
	
	$speciesString = '';
	$cutString = '';
	$unitsString = '';
	$priceString = '';
	
	foreach(request('species') as $species){
		$speciesString .= $species.'|';
	}
	
	foreach(request('cuts') as $cuts){
		$cutString .= $cuts.'|';
	}
	
	foreach(request('units') as $units){
		$unitsString .= $units.'|';
	}

	foreach(request('prices') as $price){
		$priceString .= $price.'|';
	}
	
	
	$upload_dir='../documents/';

	if($_FILES['dfile']['name']!=""){
        $file_name=$_FILES['dfile']['name'];
        $explode = explode(".",$file_name);
        $file_name=time().".".$explode[count($explode)-1];
        $tmp_name=$_FILES['dfile']['tmp_name'];
        copy($tmp_name,$upload_dir.$file_name);
	}
	  
	$speciesString = substr($speciesString,0,strlen($speciesString)-1);
	$cutString = substr($cutString,0,strlen($cutString)-1);
	$unitsString = substr($unitsString,0,strlen($unitsString)-1); 
	$priceString = substr($priceString,0,strlen($priceString)-1); 
    
    
    $speciesString = rtrim($speciesString, '|');
    $cutString = rtrim($cutString, '|');
    $unitsString = rtrim($unitsString, '|');
    $priceString = rtrim($priceString, '|');


	updatePurchase($id,$transportation,$supplier_id,$speciesString,$cutString,$unitsString,$priceString,$date_purchased,$purchased_by,$date_due,$comments,$file_name,$booking_ref_number, $haulier, $direct_drop, $temperature_id);
	
	
	// $delivery_note_number = '';
	// $product_temperature = '';
	// $vehicle_temperature = '';
	// $vehicle_reg = '';
	// $date_received = '';
	
	
	// $intake_id = addIntakeFromPurchase($supplier_id, $purchase_id, $date_received, $vehicle_reg, $vehicle_temperature, $product_temperature, $delivery_note_number, $purchased_id);

	
?>
<script>
	window.location = '../createPurchase.php?id=<?php echo $id; ?>';
</script>
