<?php

    use App\Models\InternalPalletMovement;
    use App\Models\Location;
    use App\Models\Pallet;
    use Illuminate\Support\Facades\Auth;

	require(__DIR__.'/../functions.php');
	$pallet = request()->input('pallet');
	$location = trim(request()->input('location'));
    $palletRecord = Pallet::find($pallet);
    if (!$palletRecord) {
        exit;
    }
    $fromLocation = Location::find($palletRecord->storage_location);
    $toLocation = Location::find($location);
    if (!$toLocation || $toLocation->id == $palletRecord->storage_location) {
        exit;
    }
	$palletRecord->storage_location = $location;
    $palletRecord->save();
	if ($palletRecord && $palletRecord->storage_location != null && $palletRecord->storage_location !="") {
	    $internalMovement = new InternalPalletMovement();
	    $internalMovement->pallet_id = $palletRecord->id;
	    $internalMovement->from_location_id = $fromLocation->id;
	    $internalMovement->to_location_id = $toLocation->id;
        $internalMovement->site_to_site = $fromLocation->site_id !== $toLocation->site_id;
	    $internalMovement->movement_initiated_by = Auth::id();
	    $internalMovement->save();
	}
?>
