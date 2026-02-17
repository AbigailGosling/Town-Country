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

// Reload both products with their weights after moving
$existingProduct = Product::with("weights")->find($product_id);
$newProduct = Product::with("weights")->find($newProduct->id);
$reservationsSum = ReservationProduct::where([["product_id",$existingProduct->id],["deleted",0]])->sum("target_count");
$allocatedExisting = 0;
$allocatedNew = 0;  // Track new pallet allocation separately

if ($reservationsSum > $existingProduct->weights->count())
{
    foreach (ReservationProduct::where([["product_id",$existingProduct->id],["deleted",0]])->orderBy("target_count","desc")->get() as $rp)
    {
        if ($rp->target_count > $existingProduct->weights->count() - $allocatedExisting)
        {
            $availableOnNew = $newProduct->weights->count() - $allocatedNew;

            if ($rp->target_count <= $availableOnNew)
            {
                // Move entire reservation to new product
                $rp->product_id = $newProduct->id;
                $allocatedNew += $rp->target_count;
            }
            else if ($availableOnNew > 0)
            {
                // Split the reservation between both products
                $newRP = $rp->replicate();
                $newRP->product_id = $newProduct->id;
                $newRP->target_count = $availableOnNew;
                $newRP->save();

                $rp->target_count = $rp->target_count - $availableOnNew;
                if ($rp->target_count == 0) $rp->deleted = 1;

                $allocatedNew += $availableOnNew;
            }
            $rp->save();
        }
        else
        {
            $allocatedExisting += $rp->target_count;
        }
    }
}
?>
<script>
	window.location = '../intake.php?id=<?php  echo $intake_id; ?>&palletupdated=<?php echo $newPallet->id; ?>';
</script>
