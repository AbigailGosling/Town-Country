<?php
	require(__DIR__.'/../functions.php');

	$delivery_note_number = request()->input('delivery_note_number');
    $supplier_id = loggedQuery("SELECT `customer_id` FROM `pickersheets` WHERE id = ?","i", [$delivery_note_number]);
    if ($supplier_id->num_rows > 0) {
        $supplier_id = $supplier_id->fetch_assoc()['customer_id'];
    } else {
        ?>
message:Original Invoice not found
        <?php
        exit;
    }
    $purchase_id = request()->input('purchase_id');
	$security_id = request()->input('security_id');
	$date_received = request()->input('date_received');
	$vehicle_reg = request()->input('vehicle_reg');
	$vehicle_temperature = request()->input('vehicle_temperature');
	$product_temperature = request()->input('product_temperature');
	$staff_id = request()->input('staff_id');

	$date_received = str_replace('/', '-', $date_received);
	$date_received = date('Y-m-d 00:00:00', strtotime($date_received));

	echo addReturnIntake($supplier_id, $date_received, $vehicle_reg, $vehicle_temperature,$product_temperature, $delivery_note_number, $staff_id, $security_id, $purchase_id);
?>

