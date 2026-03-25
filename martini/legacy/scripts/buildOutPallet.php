<?php

use App\Models\OutgoingPallet;
use App\Models\OutgoingPalletPickWeight;
use App\Models\PickerSheet;
use App\Models\PickWeightOut;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

	require(__DIR__.'/../functions.php');

	$pickersheet_id = request()->input('id');
    $outgoingPalletID = request()->input('outgoingPalletID',-1);
    $transactionId = request()->input('transaction_id');

    if (empty($transactionId)) {
    ?>
        <script>
            window.location = '../viewPickSheet.php?id=<?php echo $pickersheet_id; ?>&type=<?php echo request()->input('type'); ?>';
        </script>
    <?php
        exit();
    }

    $transactionCacheKey = 'build_out_pallet_transaction:' . $transactionId;
    if (Cache::has($transactionCacheKey)) {
    ?>
        <script>
            window.location = '../viewPickSheet.php?id=<?php echo $pickersheet_id; ?>&type=<?php echo request()->input('type'); ?>';
        </script>
    <?php
        exit();
    }
    Cache::put($transactionCacheKey, true, now()->addHours(12));
    $weight_ids = request()->input('weightids');
    $weight_ids = rtrim($weight_ids,',');
    $weight_id_array = explode(",",$weight_ids);

    $checkSoldResult = prepareExecuteQuery("SELECT *  FROM `weights` WHERE `id` IN (".implode(",",array_fill(0,count($weight_id_array),"?")).") && `status_id` = 1",
        str_repeat("i",count($weight_id_array)),$weight_id_array);
    $weightsAlreadySold = mysqli_num_rows($checkSoldResult);

    if($weightsAlreadySold > 0){
    ?>
        <script>
            window.location = '../viewPickSheet.php?id=<?php echo $pickersheet_id; ?>&type=<?php echo request()->input('type'); ?>';
        </script>
    <?php
        exit();
    }
    $pickSheet = PickerSheet::find($pickersheet_id);
    if ($outgoingPalletID == -1 || $outgoingPalletID == null || $outgoingPalletID == '#') {
        $op = OutgoingPallet::create([
            'outgoing_pallet_type_id' => 1,
            'customer_id' => $pickSheet->customer_id,
            'address_id' => $pickSheet->addressid,
            'estimated_delivery_date' => Carbon::createFromFormat('d/m/Y', $pickSheet->estimated_delivery_date)->format('Y-m-d'),
            'dispatched' => false,
        ]);
    }
    else $op = OutgoingPallet::find($outgoingPalletID);
    $oppw = OutgoingPalletPickWeight::where('outgoing_pallet_id', $op->id)->get()->first() ?? OutgoingPalletPickWeight::create([
        'outgoing_pallet_id' => $op->id,
        'pickWeightOut_id' => PickWeightOut::create([
            'pickersheet_id' => $pickersheet_id,
            'weight_ids' => '',
            'picker_ids' => '',
        ])->id,
    ]);
    $pw = PickWeightOut::find($oppw->pickWeightOut_id);
 	$x = "SELECT * FROM pickWeightOut WHERE `id` = ? ORDER BY `id` DESC LIMIT 1";
    $y = prepareExecuteQuery($x,'s',[$pw->id]);
    $exists = mysqli_num_rows($y);
    $outPallet = mysqli_fetch_array($y);
    $outPalletID = $outPallet['id'];
    $grossTareArray = array_filter(explode(',', $outPallet['weight_ids']));

    foreach(request()->input('grossids') as $weightID){

        if(is_numeric($weightID) && request('gross_' . $weightID) != 0){

            $grosstareEmpty = false;

            # START GET WEIGHT ROW
            $x1 = "SELECT * FROM `weights` WHERE `id` = ?";
            $y1 = prepareExecuteQuery($x1,'i',[$weightID]);
            $weight = mysqli_fetch_array($y1);
            $tare = $weight['weight_gross'];
            # END GET WEIGHT ROW


            $product_id = $weight['product_id'];

            $weightOne = request('gross_' . $weightID);
            $weightTwo = (float) $tare - (float) $weightOne;

            # START UPDATE CURRENT WEIGHT INFO
            $x2 = "UPDATE `weights` SET weight_gross = ?, weight_tear = ?, grosstare='0', status_id='1' WHERE id = ?";
            $y2 = prepareExecuteQuery($x2,'ssi',[$weightOne,$weightOne,$weightID]);
            # END UPDATE CURRENT WEIGHT INFO

            array_push($grossTareArray, $weightID);

            # START CREATE NEW WEIGHT FOR REMAINING GROSSTARE WEIGHT
            $x3 = "INSERT into `weights` (product_id, weight_gross, weight_tear,status_id,grosstare) VALUES (?,?,?,'0',0)";
            $y3 = prepareExecuteQuery($x3,'sss',[$product_id,$weightTwo,$weightTwo]);
            # END CREATE NEW WEIGHT FOR REMAINING GROSSTARE WEIGHT

        }
    }

    # START NORMAL WEIGHT
    $weightids = explode(',', request()->input('weightids')) ?? [];
    foreach($weightids as $weightID){
        if($weightID != ''){

            $x = "UPDATE `weights` SET status_id='1' WHERE id = ? LIMIT 1";
            $y = prepareExecuteQuery($x,'i',[$weightID]);

            array_push($grossTareArray, $weightID);  # add to that existing weights array
        }
    }

if(!empty($grossTareArray)){
    $pickers = array_filter(explode(",",$outPallet['picker_ids']));
    $pickers[] = $userid;
    $pickers = array_unique($pickers);
    $pickers = implode(",",$pickers);
    $weightString = implode(',', $grossTareArray);
    $x = "UPDATE `pickWeightOut` SET weight_ids = ?, picker_ids = ? WHERE id = ?";
    $y = prepareExecuteQuery($x,'ssi',[$weightString,$pickers,$outPalletID]);
}
# END NORMAL WEIGHT




    ?>
<script>
	window.location = '../viewPickSheet.php?id=<?php echo $pickersheet_id; ?>&type=<?php echo request()->input('type'); ?>';
</script>
