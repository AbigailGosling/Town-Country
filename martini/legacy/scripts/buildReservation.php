<?php

use App\Models\ContainerProduct;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Pallet;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\ReservationProduct;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

	require(__DIR__.'/../functions.php');
	$orderReferenceNumber = request()->input('orderReferenceNumber');
	$weightnote = request()->input('weightnote');
	$picksheet_note = request()->input('picksheet_note');
    $goods_out_note = request()->input('goods_out_note');
    $customer_id = request()->input('customer_id');
    $customer = Customer::find($customer_id);
	$addressid = request()->input('addressid');
    $eta = Carbon::createFromFormat('d/m/Y', request()->input('eta'));

    $transaction_id = request()->input('transaction_id');
    if ($transaction_id != null && $transaction_id != "")
	{
		$transactCheck = prepareExecuteQuery("SELECT * FROM `reservation` WHERE `transaction_id` = ?",'s',[$transaction_id]);
		if ($transactCheck->num_rows > 0) {
			throw new \Exception("duplicate transaction");
			abort(500);
			die();
		}
	}
	$today = date('Y-m-d');

	$baskets = [];
	$priceTypeSorted = [];
	$price_types = request()->input('price_type');
    $location = Location::where("name","Unit 11")->first();
	foreach (request()->input('basketRow') as $key => $value) {

		$details = explode('-', $value);
		$product_id = $details[0];
		$priceTypeSorted[$product_id] = $price_types[$key];
        $defProduct = Product::find($product_id);
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
    $prods = [];
    foreach ($baskets as $basket) {
        foreach ($basket as $item) {
			$details = explode('-', $item);
			$prods[] = $details[0];
        }
    }
    $numOfContainters = count(array_unique(ContainerProduct::whereIn("product_id",$prods)->get()->pluck("container_id")->toArray()));
    if ($numOfContainters>1)
    {
        ?>
<script>
	alert("Cannot Reserve Across Multiple Containers!!");
</script>
        <?php
        die();
        exit;
    }
	foreach ($baskets as $basket) {

        $reservation = Reservation::create([
            'user_id' => $customer->default_salesman_id,
            'address_id'=>$addressid,
            'picksheet_note'=>$picksheet_note,
            'goods_out_note'=>$goods_out_note,
            'order_reference_number'=>$orderReferenceNumber,
            'customer_id'=>$customer_id,
            'eta'=>$eta,
            'transaction_id'=>$transaction_id]);
        $transaction_id = null;
        loggedDataChange("reservation_note",$reservation->id,$picksheet_note);
		loggedDataChange("reservation_orderReferenceNumber",$reservation->id,$orderReferenceNumber);

		foreach ($basket as $item) {

			$details = explode('-', $item);
			$product_id = $details[0];
			$quantity = $details[1];
			$cut_id = $details[2];


			$target_weight = (int) request()->input('target_weight_' . $product_id);

			if(empty($target_weight)){ $target_weight = 0; }

			if(!is_int($target_weight)){ $target_weight = 0; }

			$price = request()->input('price_' . $product_id);
			$price_type = $priceTypeSorted[$product_id];
            ReservationProduct::create(
                [
                    'reservation_id'=>$reservation->id,
                    'product_id'=>$product_id,
                    'target_count'=>$quantity,
                    'price'=>$price,
                ]
            );
		}
        pclose(popen('start /B cmd /C "php '.$artisanLocation.'  run:send_reservation '.$reservation->id.' >NUL 2>NUL"', 'r'));
	}
	$x = "UPDATE `customers` SET `override` = 0, `delivery_day_override` = 0 WHERE id = ?";
	$y = prepareExecuteQuery($x,'i',[$customer_id]);

    $x = "UPDATE `users` SET `override_saledate_check` = 0 WHERE id = ?";
	$y = prepareExecuteQuery($x,'i',[$userid]);
?>
<script>
	alert("Done!");
	window.location = '../menu.php';
</script>
