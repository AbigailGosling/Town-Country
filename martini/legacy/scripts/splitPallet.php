<?php

use App\Models\ContainerProduct;
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

$containerProduct = ContainerProduct::where("product_id",$existingProduct->id)->first()->replicate();
$containerProduct->product_id = $newProduct->id;
$containerProduct->save();

for ($i = 0;$i<$cases_to_split;$i++)
{
    /** @var Weight $weight */
    $weight = $existingProduct->weights[$i];
    $weight->product_id = $newProduct->id;
    $weight->save();
}
$existingProduct = Product::with("weights")->find($product_id);
$newProduct = Product::with("weights")->find($newProduct->id);
$reservationsSum = ReservationProduct::where("product_id",$existingProduct->id)->sum("target_count");
$allocated = 0;
if ($reservationsSum > $existingProduct->weights->count())
{
    foreach (ReservationProduct::where("product_id",$existingProduct->id)->orderBy("target_count","desc")->get() as $rp)
    {
        if ($rp->target_count > $existingProduct->weights->count() - $allocated)
        {
            if ($rp->target_count <= $newProduct->weights->count())
            {
                $rp->product_id = $newProduct->id;
            }
            else
            {
                $newRP = $rp->replicate();
                $newRP->product_id = $newProduct->id;
                $newRP->target_count = $newProduct->weights->count();
                $newRP->save();
                $rp->target_count = $rp->target_count-$newProduct->weights->count();
                if ($rp->target_count == 0)$rp->deleted = 1;
                $allocated = $allocated + $rp->target_count;
            }
            $rp->save();
        }
        else
        {
            $allocated = $allocated + $rp->target_count;
        }
    }
}
?>
<script>
	window.location = '../intake.php?id=<?php  echo $intake_id; ?>&palletupdated=<?php echo $newPallet->id; ?>';
</script>
