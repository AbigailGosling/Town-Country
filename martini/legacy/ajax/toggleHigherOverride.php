<?php

use App\Models\CommentLogging;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;

require(__DIR__.'/../functions.php');

$id = request()->input('id');
if ($id == null || $id == '') exit;

$x = "UPDATE `customers` SET `higher_override` = NOT `higher_override` WHERE `id` = ?";

$customer = Customer::find($id);
$cl = new CommentLogging();
$cl->type = "higher_override";
$cl->user_id = Auth::user()->id;
$cl->entity_id = $id;
$cl->body = ($customer->higher_override == "1")?"Enabled : by old system":"Disabled : by old system";
$cl->save();

$y = prepareExecuteQuery($x,'i',[$id]);


?>
