<?php

use App\Models\InboundContainer;
use App\Models\Intake;
use App\Models\Location;
use App\Models\Pallet;
use App\Models\Weight;
use Illuminate\Support\Facades\Cache;

	require(__DIR__.'/../functions.php');


	$supplier_id = request()->input('supplier_id');
	$purchase_id = request()->input('purchase_id');
	$security_id = request()->input('security_id');
	$date_received = request()->input('date_received');
	$vehicle_reg = request()->input('vehicle_reg');
	$vehicle_temperature = request()->input('vehicle_temperature');
	$product_temperature = '';
	$delivery_note_number = request()->input('delivery_note_number');
    $internal_number = request()->input('internal_number');
	$staff_id = request()->input('staff_id');
	$site_id = request()->input('site_id');
    $transactionId = request()->input('transaction_id');

    if (empty($transactionId)) {
    ?>
    <script>
        alert("An error occurred while processing this delivery. Please check Intakes and try again.");
        window.location = '../newDelivery.php?purchaseid=<?php echo urlencode($purchase_id); ?><?php if (request()->has("container")) { echo "&container=" . urlencode(request()->input("container")); } ?>';
    </script>
    <?php
        exit();
    }

    $transactionCacheKey = 'new_delivery_transaction:' . $transactionId;
    if (Cache::has($transactionCacheKey)) {
    ?>
    <script>
        alert("This delivery is already being processed. Please check Intakes.");
        window.location = '../intakeList.php';
    </script>
    <?php
        exit();
    }
    Cache::put($transactionCacheKey, true, now()->addHours(12));
	$date_received = str_replace('/', '-', $date_received);
	$date_received = date('Y-m-d H:i:s', strtotime($date_received));
	$intake_id = addIntakeDupe($supplier_id, $date_received, $vehicle_reg, $vehicle_temperature,$product_temperature, $delivery_note_number, $staff_id, $security_id, $purchase_id, $site_id,$internal_number);

    if (request()->has("container")){
        $container = InboundContainer::find(request()->input("container"));
        $intakeModel = Intake::find($intake_id);
        $intakeModel->container_id = $container->id;
        $intakeModel->save();

        foreach($container->getProducts() as $containerProduct){
            $pallet = new Pallet();
            $pallet->intake_id = $intake_id;
            $pallet->storage_location = Location::where("site_id",$site_id)->get()[0]->id;
            $pallet->qc_hold = false;
            $pallet->save();

            $product= $containerProduct->getProduct();
            $akg = $product->akg;
            for ($i=0;$i<$product->quantity;$i++)
            {
                $weight = new Weight();
                $weight->product_id = $product->id;
                $weight->status_id = 0;
                $weight->save();
                if ($product->unit == "G/T") break;
            }
            $product->old_akg = $akg;
            $product->akg = null;
            $product->pallet_id = $pallet->id;
            $product->cooling_id = max($container->temperature_id,1);
            $product->save();

        }
        $container->arrived = true;
        $container->save();
    }
?>
<script>
	window.location = 'intake.php?id=<?php echo $intake_id; ?>';
</script>
