<?php

use App\Models\Location;
use App\Models\Pallet;
use App\Models\Product;

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

	$today = date('Y-m-d');
	if ($transaction_id != null && $transaction_id != "")
	{
		$transactCheck = prepareExecuteQuery("SELECT * FROM `pickerSheets` WHERE transaction_id = ?",'s',[$transaction_id]);
		if ($transactCheck->num_rows > 0) {
			throw new \Exception("duplicate transaction");
			abort(500);
			die();
		}
	}
	$baskets = [];
	$priceTypeSorted = [];
	$price_types = request()->input('price_type');
	foreach (request('basketRow') as $key => $value) {

		$details = explode('-', $value);
		$product_id = $details[0];
		$priceTypeSorted[$product_id] = $price_types[$key];
		$location = Location::find(
			Pallet::find(
				Product::find(
					$product_id
					)->pallet_id
				)->storage_location
			);
		$found = false;
		foreach ($location->sale_rules as $locID => $valIsAlwaysTrue){
			if (array_key_exists($locID,$baskets)){
				$found = true;
				$baskets[$locID][] = $value;
				break;
			}
		}
		if ($found == false) {
			if (!array_key_exists($location->id,$baskets)) $baskets[$location->id] = array();
			$baskets[$location->id][] = $value;
		}
	}
	foreach ($baskets as $basket) {
		$x = "INSERT INTO `pickerSheets` (picker_id,user_from_id,customer_id,estimated_delivery_date,orderReferenceNumber,date_completed,addressid,picksheet_note,transaction_id) VALUES (?,?,?,?,?,?,?,?,?)";
		$y = prepareExecuteQuery($x,'iiissssss',[$picker_id,$user_from_id,$customer_id,$estimated_delivery_date,$orderReferenceNumber,$today,$addressid,$picksheet_note,$transaction_id],true);
		$transaction_id = null;
		$pickersheet_id = $y;

		if ((int)$pickersheet_id !== $pickersheet_id)
		{
			abort(500);
			die();
		}
		loggedDataChange("picksheet_note",$picksheetid,$picksheet_note);
		loggedDataChange("picksheet_orderReferenceNumber",$picksheetid,$orderReferenceNumber);

		foreach ($basket as $item) {

			$details = explode('-', $item);
			$product_id = $details[0];
			$quantity = $details[1];
			$cut_id = $details[2];


			$target_weight = (int) request('target_weight_' . $product_id);

			if(empty($target_weight)){ $target_weight = 0; }

			if(!is_int($target_weight)){ $target_weight = 0; }

			$price = request('price_' . $product_id);
			$price_type = $priceTypeSorted[$product_id];
			for($i=0;$i<$quantity;$i++){
				$x = "INSERT into `pickerItems` (pickersheet_id,product_id,price,price_type,comment,target_weight) VALUES (?,?,?,?,?,?)";
				$y = prepareExecuteQuery($x,'iissss',[$pickersheet_id,$product_id,$price,$price_type,$comment,$target_weight]);
			}
		}
        pclose(popen('start /B cmd /C "php D:\\wwwroot\\martini\\artisan  run:send_sale_confirmation '.$pickersheet_id.' >NUL 2>NUL"', 'r'));
		//shell_exec("php D:\\wwwroot\\martini\\artisan  run:send_sale_confirmation $pickersheet_id > NUL");
	}
	$x = "UPDATE `customers` SET `override` = 0, `delivery_day_override` = 0 WHERE id = ?";
	$y = prepareExecuteQuery($x,'i',[$customer_id]);


?>
<script>
	alert("Done!");
	window.location = '../menu.php';
</script>
