<?php

use App\Models\CommentLogging;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;

	require(__DIR__.'/../functions.php');


	$id = request()->input('id');

	if($id != ''){

		$customer = Customer::find($id);
		$cl = new CommentLogging();
		$cl->type = "override_cost_check";
		$cl->user_id = Auth::user()->id;
		$cl->entity_id = $id;
		$cl->body = ($customer->override_cost_check == 0)?"Enabled : by old system":"Disabled : by old system";
		$cl->save();

		$x = "UPDATE `customers` SET `override_cost_check` = IF(`override_cost_check` = 1,0,1) WHERE id=? LIMIT 1";
		$y = loggedQuery($x,'i',[$id]);

	}

?>
