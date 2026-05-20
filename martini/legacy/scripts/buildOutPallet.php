<?php

use App\Helpers\FuncHelper;
use App\Helpers\InternalCache;
use App\Models\OutgoingPallet;
use App\Models\OutgoingPalletPickWeight;
use App\Models\PickerSheet;
use App\Models\PickWeightOut;
use Carbon\Carbon;

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
    if (InternalCache::has($transactionCacheKey)) {
    ?>
        <script>
            window.location = '../viewPickSheet.php?id=<?php echo $pickersheet_id; ?>&type=<?php echo request()->input('type'); ?>';
        </script>
    <?php
        exit();
    }
    InternalCache::put($transactionCacheKey, true, now()->addHours(12));
    $weight_ids = request()->input('weightids');
    $weight_ids = rtrim($weight_ids,',');
    $weight_id_array = explode(",",$weight_ids);
    $dolavs = [];
    if (request()->has("dolavs") && is_array(request()->input("dolavs")) && count(request()->input("dolavs")) > 0)
    {
        $dolavs = request()->input("dolavs");
        $weight_id_array = array_merge($weight_id_array,$dolavs);
    }
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
    $oppw = OutgoingPalletPickWeight::where('outgoing_pallet_id', $op->id)->get()->first();
    if ($oppw == null) {
        $tmp = new PickWeightOut();
        $tmp->pickersheet_id = $pickersheet_id;
        $tmp->weight_ids = '';
        $tmp->picker_ids = '';
        $tmp->save();
        $oppw = new OutgoingPalletPickWeight();
        $oppw->outgoing_pallet_id = $op->id;
        $oppw->pickWeightOut_id = $tmp->id;
        $oppw->save();
    }
    $pw = PickWeightOut::find($oppw->pickWeightOut_id);
 	$x = "SELECT * FROM pickWeightOut WHERE `id` = ? ORDER BY `id` DESC LIMIT 1";
    $y = prepareExecuteQuery($x,'s',[$pw->id]);
    $exists = mysqli_num_rows($y)>0;
    $outPallet = mysqli_fetch_array($y);
    $outPalletID = $outPallet['id'];
    $grossTareArray = array_filter(explode(',', $outPallet['weight_ids']));

    if (request()->has('grossids') && is_array(request()->input('grossids')) && count(request()->input('grossids'))>0)
        $grossTareArray = array_merge($grossTareArray,processGrossDolav(request()->input('grossids'),'gross_'));
    if (is_array($dolavs) && count($dolavs)>0)
        $grossTareArray = array_merge($grossTareArray,processGrossDolav($dolavs,'dolav_'));

    //startTransaction();
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
        $grossTareArray = array_filter(array_unique($grossTareArray));
        $weightString = implode(',', $grossTareArray);
        $dupCheck = "SELECT * FROM `pickWeightOut` WHERE weight_ids = ? AND picker_ids = ? AND pickersheet_id = ?";
        $dupCheckResult = prepareExecuteQuery($dupCheck,'ssi',[$weightString,$pickers,$pickersheet_id]);
        if(mysqli_num_rows($dupCheckResult) == 0){ # only update if the exact same data doesn't already exist for this pallet, to avoid race conditions

            $x = "UPDATE `pickWeightOut` SET weight_ids = ?, picker_ids = ? WHERE id = ?";
            $y = prepareExecuteQuery($x,'ssi',[$weightString,$pickers,$outPalletID]);
        }
    }
    //commitTransaction();
    ?>
<script>
	window.location = '../viewPickSheet.php?id=<?php echo $pickersheet_id; ?>&type=<?php echo request()->input('type'); ?>';
</script>
<?php

function processGrossDolav(array $workingSet,string $key):array
{
    $grossTareArray = [];
    foreach($workingSet as $weightID){

        if(is_numeric($weightID) && request($key . $weightID) != 0){

            # START GET WEIGHT ROW
            $x1 = "SELECT * FROM `weights` WHERE `id` = ?";
            $y1 = prepareExecuteQuery($x1,'i',[$weightID]);
            $weight = mysqli_fetch_array($y1);
            $tare = $weight['weight_gross'];
            # END GET WEIGHT ROW


            $product_id = $weight['product_id'];

            $weightOne = FuncHelper::ceilDec(min($tare,request($key . $weightID)),3);
            $weightTwo = FuncHelper::ceilDec(max(0,(float) $tare - (float) $weightOne),3);

            # START UPDATE CURRENT WEIGHT INFO
            $x2 = "UPDATE `weights` SET weight_gross = ?, weight_tear = ?, grosstare='0', status_id='1' WHERE id = ?";
            $y2 = prepareExecuteQuery($x2,'ssi',[$weightOne,$weightOne,$weightID]);
            # END UPDATE CURRENT WEIGHT INFO

            array_push($grossTareArray, $weightID);
            $grossTareArray = array_filter(array_unique($grossTareArray));

            # START CREATE NEW WEIGHT FOR REMAINING GROSSTARE WEIGHT
            $x3 = "INSERT into `weights` (product_id, weight_gross, weight_tear,status_id,grosstare) VALUES (?,?,?,'0',0)";
            $y3 = prepareExecuteQuery($x3,'sss',[$product_id,$weightTwo,$weightTwo]);
            # END CREATE NEW WEIGHT FOR REMAINING GROSSTARE WEIGHT

        }
    }
    return $grossTareArray;
}

?>
