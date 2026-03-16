<?php

use App\Models\OutgoingPallet;
use App\Models\OutgoingPalletPickWeight;
use App\Models\OutgoingPalletType;
use App\Models\PickerSheet;
use App\Models\PickWeightOut;
use Carbon\Carbon;

	require(__DIR__.'/../functions.php');
	$id = request()->input('id');
	$pickersheet_id = request()->input('id');
	$type = request()->input('sheet_type');

	/* START - Get all product IDs on the picksheet */
	$product_ids = array();
	$result_product = prepareExecuteQuery("SELECT `product_id` FROM `pickerItems` WHERE pickersheet_id=? GROUP BY `product_id`",'i',[$pickersheet_id]);
	while($product = mysqli_fetch_array($result_product)){
		array_push($product_ids, $product['product_id']);
	}
	$product_ids = implode(',', $product_ids);
	/* END - Get all product IDs on the picksheet */


	$result_fresh = prepareExecuteQuery("SELECT id FROM `product` WHERE id IN ($product_ids) && cooling_id='1' LIMIT 1");
	$count_fresh = mysqli_num_rows($result_fresh);

	$result_frozen= prepareExecuteQuery("SELECT id FROM `product` WHERE id IN ($product_ids) && cooling_id IN ('2','3') LIMIT 1");
	$count_frozen = mysqli_num_rows($result_frozen);


	// if you just submitted the fresh products OR theres no fresh products at all
	if($type == 'fresh' || $count_fresh == 0){
		// mark fresh as completed
		$updateStatusQuery = "UPDATE `pickerSheets` SET completed_fresh='1' WHERE id=?";
		prepareExecuteQuery($updateStatusQuery,'i',[$pickersheet_id]);
	}

	// if you just submitted the frozen products OR theres no frozen products at all
	if($type == 'frozen' || $count_frozen == 0){
		// mark frozen as completed
		$updateStatusQuery = "UPDATE `pickerSheets` SET completed_frozen='1' WHERE id=?";
		prepareExecuteQuery($updateStatusQuery,'i',[$pickersheet_id]);
	}
	$picksheetResult = prepareExecuteQuery("SELECT * FROM pickerSheets WHERE id=? LIMIT 1",'i',[$pickersheet_id]);
	$picksheet = mysqli_fetch_array($picksheetResult);
	$customer_id = $picksheet['customer_id'];
	prepareExecuteQuery("DELETE FROM customer_outstanding_cache WHERE customer_id = ?",'i',[$customer_id]);
	if($picksheet['completed_frozen'] == 1 && $picksheet['completed_fresh'] == 1){

		$updateStatusQuery = "UPDATE `pickerSheets` SET completed='1' WHERE id = ?";
		prepareExecuteQuery($updateStatusQuery,'i',[$pickersheet_id]);

		$val = getPicksheetValue($pickersheet_id);

		$x1 = "SELECT * FROM `customers` WHERE id=?";
		$y1 = prepareExecuteQuery($x1,'i',[$customer_id]);
		$customer = mysqli_fetch_array($y1);

		$current_outstanding = (float) $customer['current_outstanding'];
		$newVal = $current_outstanding + (float) $val;

		$x = "UPDATE `customers` SET current_outstanding = ? WHERE id = ? LIMIT 1";
		$y = prepareExecuteQuery($x,'si',[$newVal,$customer_id]);
		# END update customer price

		date_default_timezone_set("Europe/London");

		$date_completed = date('Y/m/d H:i:s');

		$session_USERID = $_SESSION['USER'];

		$x = "UPDATE `pickerSheets` SET completed = '1', completedby_userid=?, date_completed = ? WHERE id = ?";
		$y = prepareExecuteQuery($x,'ssi',[$session_USERID,$date_completed,$id]);



		$x = "SELECT * FROM `pickWeightOut` WHERE pickersheet_id=?";
		$y = prepareExecuteQuery($x,'i',[$id]);

		while($row = mysqli_fetch_array($y)){
			// echo 'weight_ids = ' . $row['weight_ids'];

			$weightIDArray = explode(',', $row['weight_ids']);

			foreach($weightIDArray as $weightID){

				$x2 = "UPDATE `weights` SET status = '1' WHERE id=?";
				//$y2 = prepareExecuteQuery($x2,'i',[$weightID]);

				//$weight = mysqli_fetch_array($y1);
			}

		}
		$x2 = "UPDATE `pickerItems` SET `status` = '1' WHERE pickersheet_id=?";
		$y2 = prepareExecuteQuery($x2,'i',[$pickersheet_id]);

	}
    pclose(popen('start /B cmd /C "php '.$artisanLocation.' run:checkshortpick '.$pickersheet_id.' >NUL 2>NUL"', 'r'));
    pclose(popen('start /B cmd /C "php '.$artisanLocation.' run:credit_precheck '.$customer_id.' >NUL 2>NUL"', 'r'));
?>
<script>
	alert("Picking Sheet Submitted!");
	window.location = '../menu.php';
</script>
