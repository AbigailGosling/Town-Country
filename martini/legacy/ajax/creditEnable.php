<?php

use App\Models\Customer;

	require(__DIR__.'/../functions.php');


	$id = request()->input('id');

	if($id != ''){

		$c = Customer::find($id);
        $c->credit_enabled = ($c->credit_enabled == 0) ? 1  : 0 ;
        $c->save();

	}

?>
