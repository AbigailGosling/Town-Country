<?php

use App\Models\PalletMovementTracking;

	require(__DIR__.'/../functions.php');

	$pallet = request()->input('pallet');
	$location = trim(request()->input('location'));


	PalletMovementTracking::moveStock($pallet, $location);



?>
