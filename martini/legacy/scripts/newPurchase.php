<?php
	require(__DIR__.'/../functions.php');
	Log::debug(request()->all());
	
	$supplier_id = request()->input('supplier_id');
	$purchased_by = request()->input('purchased_by');
	$date_purchased = request()->input('date_purchased');
	$date_due = request()->input('date_due');
	$transportation = request()->input('transportation');
	$haulier = request()->input('haulier');
	$direct_drop = request()->input('direct_drop',0);
	$temperature_id = request()->input('temperature_id');
	$site_id = request()->input('site_id');
	
	$date_purchased = str_replace('/', '-', $date_purchased);
	$date_purchased = date('Y-m-d 00:00:00', strtotime($date_purchased));
	
	$date_due = str_replace('/', '-', $date_due);
	$date_due = date('Y-m-d H:00:00', strtotime($date_due));
	
	$comments = request()->input('comments');
	$booking_ref_number = request()->input('booking_ref_number');
	
	$speciesString = '';
	$cutString = '';
	$unitsString = '';
	$priceString = '';
	
	foreach(request()->input('species') as $species){
		$speciesString .= $species.'|';
	}
	
	foreach(request()->input('cuts') as $cuts){
		$cutString .= $cuts.'|';
	}
	
	foreach(request()->input('units') as $units){
		$unitsString .= $units.'|';
	}
 
	foreach(request()->input('prices') as $price){
		$priceString .= $price.'|';
	}
	
	
	
	$upload_dir='/../documents/';
	$file_name="";
	if(request()->hasFile('dfile'))
	{
		$file_name=time().".".request()->file('dfile')->extension();
		$tmp_name=request()->file('dfile')->path();
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

	
	$purchase_id = createPurchase($supplier_id,$transportation, $speciesString,$cutString,$priceString,$unitsString,$date_purchased,$purchased_by,$date_due,$comments,$file_name,$booking_ref_number,$haulier, $direct_drop, $temperature_id,$site_id);
	
	
	// $delivery_note_number = '';
	// $product_temperature = '';
	// $vehicle_temperature = '';
	// $vehicle_reg = '';
	// $date_received = '';
	
	
	
	// $intake_id = addIntakeFromPurchase($supplier_id, $purchase_id, $date_received, $vehicle_reg, $vehicle_temperature, $product_temperature, $delivery_note_number, $purchased_id);

	
?>
<script>
	// window.location = '../intake.php?id=<?php echo $intake_id; ?>';
	window.location = '../createPurchase.php?id=<?php echo $purchase_id; ?>';

</script>
