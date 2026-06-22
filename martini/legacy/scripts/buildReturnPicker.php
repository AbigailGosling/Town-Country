<?php

use App\Helpers\ProcessHelper;
use App\Models\Location;
use App\Models\Pallet;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

	require(__DIR__.'/../functions.php');

	$picker_id = request()->input('picker_id');
	$supplier_id = request()->input('supplier_id');
	$estimated_delivery_date = request()->input('estimated_delivery_date');

	$orderReferenceNumber = request('orderReferenceNumber');
	$weightnote = request()->input('weightnote');
	$picksheet_note = request()->input('picksheet_note');

	//$user_from_id = $_SESSION['USER'];
	$user_from_id = request()->input('sales_person');
	$addressid = request()->input('addressid');
	$transaction_id = request()->input('transaction_id');

	if ($user_from_id == "" || $supplier_id == 0 || $supplier_id == "")
	{
?>
	<script type="text/javascript">
		alert("An Error Occurred! Could not complete return!");
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
        $defProduct = Product::find($product_id);
		$location = Location::find(
			Pallet::find(
				$defProduct->pallet_id
				)->storage_location
			);
		$found = false;
		foreach ($location->sale_rules as $locID => $valIsAlwaysTrue){
			if (array_key_exists($locID."-".$defProduct->cooling_id,$baskets)){
				$found = true;
				$baskets[$locID."-".$defProduct->cooling_id][] = $value;
				break;
			}
		}
		if ($found == false) {
			if (!array_key_exists($location->id."-".$defProduct->cooling_id,$baskets)) $baskets[$location->id."-".$defProduct->cooling_id] = array();
			$baskets[$location->id."-".$defProduct->cooling_id][] = $value;
		}
	}
	foreach ($baskets as $basket) {
		$x = "INSERT INTO `pickerSheets` (picker_id,user_from_id,customer_id,estimated_delivery_date,orderReferenceNumber,date_completed,addressid,picksheet_note,transaction_id,is_return_to_supplier) VALUES (?,?,?,?,?,?,?,?,?,?)";
		$y = prepareExecuteQuery($x,'iiisssissi',[$picker_id,$user_from_id,$supplier_id,$estimated_delivery_date,$orderReferenceNumber,$today,$addressid,$picksheet_note,$transaction_id,1],true);

        $transaction_id = null;
		$pickersheet_id = $y;

        if ((int)$pickersheet_id !== $pickersheet_id)
		{
			abort(500);
			die();
		}

        $x = "INSERT INTO `supplier_returns` (user_id,supplier_id,pick_id,reference_number,comments) VALUES (?,?,?,?,?)";
		$y = prepareExecuteQuery($x,'iiiss',[$user_from_id,$supplier_id,$pickersheet_id,$orderReferenceNumber,$picksheet_note],true);

		$return_id = $y;

        $xa = "INSERT INTO `supplier_return_attachment` (user_id,return_id,comments) VALUES (?,?,?)";
		$ya = prepareExecuteQuery($xa,'iis',[$user_from_id,$return_id,"created"],true);

		loggedDataChange("picksheet_note",$pickersheet_id,$picksheet_note);
		loggedDataChange("picksheet_orderReferenceNumber",$pickersheet_id,$orderReferenceNumber);
        loggedDataChange("picksheet_estimated_delivery_date",$pickersheet_id,$estimated_delivery_date);

		foreach ($basket as $item) {

			$details = explode('-', $item);
			$product_id = $details[0];
			$quantity = $details[1];
			$cut_id = $details[2];


			$target_weight = (int) request('target_weight_' . $product_id);

			if(empty($target_weight) || !is_int($target_weight) || $target_weight < 0){ $target_weight = 0; }

			$price = request('price_' . $product_id);
			$price_type = $priceTypeSorted[$product_id];
			for($i=0;$i<$quantity;$i++){
				$x = "INSERT into `pickerItems` (pickersheet_id,product_id,price,price_type,comment,target_weight) VALUES (?,?,?,?,?,?)";
				$y = prepareExecuteQuery($x,'iissss',[$pickersheet_id,$product_id,$price,$price_type,"",$target_weight]);
			}
            $x = "INSERT into `supplier_return_items` (supplier_return_id,product_id,cases) VALUES (?,?,?)";
            $y = prepareExecuteQuery($x,'iii',[$return_id,$product_id,$quantity]);
		}
        ProcessHelper::runInBackground('run:send_supplier_return '.$pickersheet_id);
	}

?>
<script>
	alert("Done!");
	window.location = '../menu.php';
</script>
