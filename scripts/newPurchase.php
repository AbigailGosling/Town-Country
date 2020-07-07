<?php
	require('../functions.php');
	
	
	$supplier_id = mysqli_real_escape_string($conn, $_POST['supplier_id']);
	$purchased_by = mysqli_real_escape_string($conn, $_POST['purchased_by']);
	$date_purchased = mysqli_real_escape_string($conn, $_POST['date_purchased']);
	$date_due = mysqli_real_escape_string($conn, $_POST['date_due']);
	$transportation = mysqli_real_escape_string($conn, $_POST['transportation']);
	$haulier = mysqli_real_escape_string($conn, $_POST['haulier']);
	$direct_drop = mysqli_real_escape_string($conn, $_POST['direct_drop']);
	
	$date_purchased = str_replace('/', '-', $date_purchased);
	$date_purchased = date('Y-m-d 00:00:00', strtotime($date_purchased));
	
	$date_due = str_replace('/', '-', $date_due);
	$date_due = date('Y-m-d H:00:00', strtotime($date_due));
	
	$comments = mysqli_real_escape_string($conn, $_POST['comments']);
	$booking_ref_number = mysqli_real_escape_string($conn, $_POST['booking_ref_number']);
	
	$speciesString = '';
	$cutString = '';
	$unitsString = '';
	$priceString = '';
	
	foreach($_POST['species'] as $species){
		$speciesString .= $species.'|';
	}
	
	foreach($_POST['cuts'] as $cuts){
		$cutString .= $cuts.'|';
	}
	
	foreach($_POST['units'] as $units){
		$unitsString .= $units.'|';
	}
 
	foreach($_POST['prices'] as $price){
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

	
	$purchase_id = createPurchase($supplier_id,$transportation, $speciesString,$cutString,$priceString,$unitsString,$date_purchased,$purchased_by,$date_due,$comments,$file_name,$booking_ref_number,$haulier, $direct_drop);
	
	
	// $delivery_note_number = '';
	// $product_temperature = '';
	// $vehicle_temperature = '';
	// $vehicle_reg = '';
	// $date_received = '';
	
	
	
	// $intake_id = addIntakeFromPurchase($supplier_id, $purchase_id, $date_received, $vehicle_reg, $vehicle_temperature, $product_temperature, $delivery_note_number, $purchased_id);

	
?>
<script>
	// window.location = '../intake.php?id=<?php echo $intake_id; ?>';
	window.location = '../purchaseList.php';
</script>
