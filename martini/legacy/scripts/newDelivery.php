<?php

use App\Models\InboundContainer;
use App\Models\Intake;
use App\Models\Location;
use App\Models\Pallet;

	require(__DIR__.'/../functions.php');


	$supplier_id = request()->input('supplier_id');
	$purchase_id = request()->input('purchase_id');
	$security_id = request()->input('security_id');
	$date_received = request()->input('date_received');
	$vehicle_reg = request()->input('vehicle_reg');
	$vehicle_temperature = request()->input('vehicle_temperature');
	$product_temperature = '';
	$delivery_note_number = request()->input('delivery_note_number');
	$staff_id = request()->input('staff_id');
	$site_id = request()->input('site_id');
	$date_received = str_replace('/', '-', $date_received);
	$date_received = date('Y-m-d H:i:s', strtotime($date_received));
	$intake_id = addIntakeDupe($supplier_id, $date_received, $vehicle_reg, $vehicle_temperature,$product_temperature, $delivery_note_number, $staff_id, $security_id, $purchase_id, $site_id);

    if (request()->has("container")){
        $container = InboundContainer::find(request()->input("container"));
        $intakeModel = Intake::find($intake_id);
        $intakeModel->container_id = $container->id;
        $intakeModel->save();

        foreach($container->getProducts() as $containerProduct){
            $pallet = new Pallet();
            $pallet->intake_id = $intake_id;
            $pallet->storage_location = Location::where("site_id",$site_id)->get()[0]->id;
            $pallet->save();

            $product= $containerProduct->getProduct();
            $product->pallet_id = $pallet->id;
            $product->save();

        }
    }
?>
<script>
	window.location = 'intake.php?id=<?php echo $intake_id; ?>';
</script>
