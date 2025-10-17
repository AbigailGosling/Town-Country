<?php
require(__DIR__.'/../functions.php');
use App\Models\Pallet;

$pallet_id = request()->input("pallet_id");
$set_to = request()->input("set_to");
$pallet = Pallet::find($pallet_id);
$pallet->qc_hold = $set_to;
$pallet->save();
?>
