<?php

use App\Models\Pallet;
use App\Models\Product;
use App\Models\ReservationProduct;
use App\Models\Weight;
use Illuminate\Support\Facades\Log;

require_once(__DIR__.'/../functions.php');
$intake_id = request()->input("intake_id");
$pallet_id = request()->input("pallet_id");
$product_id = request()->input("product_id");
$cases_to_split = request()->input("cases_to_split");

$existingPallet = Pallet::find($pallet_id);
$newPallet = $existingPallet->replicate();
$newPallet->save();

$existingProduct = Product::with("weights")->find($product_id);
$newProduct = $existingProduct->replicate();
$newProduct->pallet_id = $newPallet->id;
$newProduct->save();

for ($i = $existingProduct->weights->count()-$cases_to_split;$i<$existingProduct->weights->count();$i++)
{
    /** @var Weight $weight */
    $weight = $existingProduct->weights[$i];
    $weight->product_id = $newProduct->id;
    $weight->save();
}
$existingProduct = Product::with("weights")->find($product_id);
$newProduct = Product::with("weights")->find($newProduct->id);
$reservationsSum = ReservationProduct::where("product_id",$existingProduct->id)->sum("target_count");
while ($reservationsSum > $existingProduct->weights->count())
{
    $rp = ReservationProduct::where("product_id",$existingProduct->id)->orderBy("target_count","desc")->first();
    Log::debug("1",[$rp]);
    if ($rp->target_count > $newProduct->weights->count())
    {
        $newRP = $rp->replicate();
        $newRP->product_id = $newProduct->id;
        $newRP->target_count = $newProduct->weights->count();
        $newRP->save();
        $rp->target_count = $rp->target_count-$newProduct->weights->count();
        Log::debug("2a",[$rp,$newRP]);
    }
    else
    {
        $rp->product_id = $newProduct->id;
        Log::debug("2b",[$rp]);
    }
    $reservationsSum = $reservationsSum - $rp->target_count;
    $rp->save();
}
?>
<script>
	window.location = '../intake.php?id=<?php  echo $intake_id; ?>&palletupdated=<?php echo $newPallet->id; ?>';
</script>
