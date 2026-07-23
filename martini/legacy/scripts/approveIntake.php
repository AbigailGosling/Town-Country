<?php

use App\Helpers\InternalCache;
use App\Helpers\ProcessHelper;
use App\Models\Intake;
use App\Models\Pallet;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
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
if ($intake->pallets?->count() == 0)
{?>
<script>
	window.location = '../intake.php?id=<?php echo $intake->id; ?>&error=1';
</script> <?php exit;
}
$transaction_id = "approve_intake_start_".$intake->id;
if ($transaction_id == null || $transaction_id == "")
{
    ?>
    <script>
        window.location = '../intake.php?id=<?php echo $intake->id; ?>&error=4';
    </script> <?php exit;
}
else if (InternalCache::has($transaction_id))
{
    ?>
    <script>
        window.location = '../intake.php?id=<?php echo $intake->id; ?>&error=5';
    </script> <?php exit;
}
else {
    InternalCache::put($transaction_id, getmypid(), 600);
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
if ($user->hasPermission("approve_intake") && $intake->approved == false && InternalCache::get($transaction_id) == getmypid())
{
    $intake->approving_start = Carbon::now();
    $intake->approved_by = Auth::id();
    $intake->save();
    $command_key = 'approve_intake_' . Str::uuid();
    InternalCache::put($command_key, $intake->id, 3600);
    InternalCache::put($transaction_id, getmypid(), 60);
    ProcessHelper::runInBackground('run:approve_intake '.$command_key);
    sleep(1);
}
?>
<script>
	window.location = '../intake.php?id=<?php echo $intake->id; ?>';
</script>
