<?php

use App\Models\Customer;
use App\Models\InboundContainer;
use App\Models\Intake;
use App\Models\Pallet;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\ReservationProduct;
use App\Models\Site;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

require(__DIR__.'/../functions.php');

$user = User::find(Auth::id());
$intake = Intake::with("pallets")->with("products")->find(request()->input('intake_id'));
if ($intake->approved == true)
{?>
    <script>
        window.location = '../intake.php?id=<?php echo $intake->id; ?>';
    </script> <?php exit;
}
if ($intake->approving_start != null || $intake->approved != false)
{?>
    <script>
        window.location = '../intake.php?id=<?php echo $intake->id; ?>&error=0';
    </script> <?php exit;
}
$intake->approving_start = Carbon::now();
$intake->approved_by = Auth::id();
$intake->save();
if ($intake->pallets->count() == 0)
{?>
<script>
	window.location = '../intake.php?id=<?php echo $intake->id; ?>&error=1';
</script> <?php exit;
}
/**
* @var Pallet $pallet
*/
foreach ($intake->pallets as $pallet)
{
    if ($pallet->products->count() == 0)
    {
        $pallet->delete();
    }

    /**
    * @var Product $product
    */
    foreach ($pallet->products as $product)
    {
        if ($product->ubbb != 2 && ($product->range_from =="" || $product->range_from ==null || $product->range_to =="" || $product->range_to ==null))
        {
        ?>
            <script>
                window.location = '../intake.php?id=<?php echo $intake->id; ?>&error=3';
            </script><?php exit;
        }
    }
}
if ($user->hasPermission("approve_intake") && $intake->approved == false)
{
    if (isset($intake->container_id)) {
        $container = InboundContainer::find($intake->container_id);
        $products = $container->getProducts();
        /**
         * @var ContainerProduct $containerProduct
        */
        foreach ($products as $containerProduct)
        {
            $prod = Product::find($containerProduct->product_id);
            if ($prod == null) continue;
            $pallet=Pallet::find($prod->pallet_id);
            $pallet->user_id = Auth::id();
            $pallet->save();
        }
        $products = $products->pluck("product_id")->toArray();
        $reservationProducts = ReservationProduct::whereIn("product_id",$products)->where("deleted",0)->groupBy("reservation_id")->pluck("reservation_id")->toArray();
        $reservations = Reservation::whereIn("id",$reservationProducts)->where("deleted",0)->get();
        $today = date('Y-m-d');
        foreach ($reservations as $reservation)
        {
            $customer = Customer::find($customer_id);
            $site = Site::find($customer->site_id);
            $siteCutOffHoursAndMinutes = explode(":",$site->cutoff);
            $sitesCutOffToday = Carbon::now()->hour($siteCutOffHoursAndMinutes[0])->minute($siteCutOffHoursAndMinutes[1])->second(0)->micro(0);
            if ($reservation->eta->timestamp>$sitesCutOffToday->timestamp){
                $delDate =  $reservation->eta;
            }
            else {
                $delDate = $sitesCutOffToday->copy();
                $delDate->addDay();
            }
            $weekdayLookup = [1			,64			,32			,16			,8			,4			,2			];
            $weekdayInt = $weekdayLookup[$delDate->dayOfWeek];

            while ($customer->delivery_day_checking == 1 && ($weekdayInt & $customer->delivery_days) == 0) {
                $delDate->addDay();
                $weekdayInt = $weekdayLookup[$delDate->dayOfWeek];
            }
            $x = "INSERT INTO `pickerSheets` (picker_id,user_from_id,customer_id,estimated_delivery_date,orderReferenceNumber,date_completed,addressid,picksheet_note,transaction_id) VALUES (?,?,?,?,?,NOW(),?,?,?)";
            $y = prepareExecuteQuery($x,'iiisssss',[$picker_id,$reservation->user_id,$reservation->customer_id,$delDate->format("d/m/Y"),$reservation->order_reference_number,$reservation->address_id,$reservation->picksheet_note,null],true);
            $pickersheet_id = $y;

            if ((int)$pickersheet_id !== $pickersheet_id)
            {
                abort(500);
                die();
            }
            loggedDataChange("picksheet_note",$pickersheet_id,$picksheet_note);
            loggedDataChange("picksheet_orderReferenceNumber",$pickersheet_id,$orderReferenceNumber);

            foreach (ReservationProduct::where([["reservation_id",$reservation->id],["deleted",0]])->get() as $resProduct)
            {
                $product_id = $resProduct->product_id;
                $quantity = $resProduct->target_count;
                $target_weight = 0;


                $price = $resProduct->price;
                $price_type = null;
                for($i=0;$i<$quantity;$i++){
                    $x = "INSERT into `pickerItems` (pickersheet_id,product_id,price,price_type,comment,target_weight) VALUES (?,?,?,?,?,?)";
                    $y = prepareExecuteQuery($x,'iissss',[$pickersheet_id,$product_id,$price,$price_type,$comment,$target_weight]);
                }
            }
            $reservation->processed = true;
            $reservation->save();
            pclose(popen('start /B cmd /C "php '.$artisanLocation.'  run:send_sale_confirmation '.$pickersheet_id.' >NUL 2>NUL"', 'r'));
        }

    }
    $x = "UPDATE `intake` SET `approved` = 1, `approved_by` = ?, `approved_date` = CURRENT_TIMESTAMP() WHERE id = ?";
    $y = prepareExecuteQuery($x,'ii',[$user->id,$intake->id]);
}
?>
<script>
	window.location = '../intake.php?id=<?php echo $intake->id; ?>';
</script>
