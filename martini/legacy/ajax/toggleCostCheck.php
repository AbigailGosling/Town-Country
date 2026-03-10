<?php

use App\Models\CommentLogging;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;

require(__DIR__.'/../functions.php');


$id = request()->input('id');
if ($id == null || $id == '') exit;
$c = Customer::find($id);
$cl = new CommentLogging();
$cl->type = "cost_checking";
$cl->user_id = Auth::user()->id;
$cl->entity_id = $id;
$cl->body = ($c->cost_check_enabled == 0)?"Enabled":"Disabled";
$cl->save();


$c->cost_check_enabled = ($c->cost_check_enabled == 0) ? 1  : 0 ;
$c->save();

?>
