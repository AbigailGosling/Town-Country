<?php

use App\Models\CommentLogging;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;

require(__DIR__.'/../functions.php');

	
$id = request()->input('id');

$x = "UPDATE `customers` SET `delivery_day_override` = NOT `delivery_day_override` WHERE `id` = ?";

$customer = Customer::find($id);
$cl = new CommentLogging();
$cl->type = "delivery_override";
$cl->user_id = Auth::id();
$cl->entity_id = $id;
$cl->body = ($customer->delivery_day_override == "1")?"Enabled : by old system":"Disabled : by old system";
$cl->save();

$y = prepareExecuteQuery($x,'i',[$id]);


?>