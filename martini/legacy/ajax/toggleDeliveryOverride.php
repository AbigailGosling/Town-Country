<?php

use App\Models\CommentLogging;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;

require(__DIR__.'/../functions.php');

	
$id = request()->input('id');
define('DEL_SUNDAY',     1);
define('DEL_SATURDAY',   2);
define('DEL_FRIDAY',     4);
define('DEL_THURSDAY',   8);
define('DEL_WEDNESDAY', 16);
define('DEL_TUESDAY',   32);
define('DEL_MONDAY',    64);
$days = 0;
if (request()->has('mo') && request()->input('mo') == 1) $days += DEL_MONDAY;
if (request()->has('tu') && request()->input('tu') == 1) $days += DEL_TUESDAY;
if (request()->has('we') && request()->input('we') == 1) $days += DEL_WEDNESDAY;
if (request()->has('th') && request()->input('th') == 1) $days += DEL_THURSDAY;
if (request()->has('fr') && request()->input('fr') == 1) $days += DEL_FRIDAY;
if (request()->has('sa') && request()->input('sa') == 1) $days += DEL_SATURDAY;
if (request()->has('su') && request()->input('su') == 1) $days += DEL_SUNDAY;

$x = "UPDATE `customers` SET `delivery_day_override` = NOT `delivery_day_override`, `delivery_days` = ? WHERE `id` = ?";

$customer = Customer::find($id);
$cl = new CommentLogging();
$cl->type = "delivery_override";
$cl->user_id = Auth::id();
$cl->entity_id = $id;
$cl->body = ($customer->delivery_day_override == "1")?"Enabled : by old system":"Disabled : by old system";
$cl->save();

$y = prepareExecuteQuery($x,'i',[$id]);


?>