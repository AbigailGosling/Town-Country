<?php

use App\Models\CommentLogging;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;

	require(__DIR__.'/../functions.php');
	
	
	$id = request()->input('id');
	
	if($id != ''){
	
		$customer = Customer::find($id);
		$cl = new CommentLogging();
		$cl->type = "credit_override";
		$cl->user_id = Auth::id();
		$cl->entity_id = $id;
		$cl->body = ($customer->override == "1")?"Enabled : by old system":"Disabled : by old system";
		$cl->save();
		
		$x = "UPDATE `customers` SET `allowPrint` = IF(`override` = 1,0,1),`override` = IF(`override` = 1,0,1) WHERE id=? LIMIT 1";
		$y = prepareExecuteQuery($x,'i',[$id]);
		
	}
	
?>