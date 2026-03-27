<?php

use App\Models\ClientAddress;
use App\Models\ClientType;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

	require(__DIR__.'/../functions.php');
 $c = Customer::find(request()->input('id'));
	$colNames = array();
	$colValue = array();

	$colNames[] = '`businessname`=?';
	$colValue[] = request()->input('businessname');

	$colNames[] = '`tradingas`=?';
	$colValue[] = request()->input('tradingas');

	$colNames[] = '`nameofbuyer`=?';
	$colValue[] = request()->input('nameofbuyer');

	$colNames[] = '`contactnumber`=?';
	$colValue[] = request()->input('contactnumber');

	$colNames[] = '`customer_email`=?';
	$colValue[] = str_replace(array("\r", "\n"), '', request()->input('customer_email'));

	$colNames[] = '`companyregno`=?';
	$colValue[] = request()->input('companyregno');

	$colNames[] = '`accounts_address_1`=?';
	$colValue[] = request()->input('accounts_address_1');

	$colNames[] = '`accounts_address_2`=?';
	$colValue[] = request()->input('accounts_address_2');

	$colNames[] = '`accounts_address_3`=?';
	$colValue[] = request()->input('accounts_address_3');

	$colNames[] = '`accounts_address_4`=?';
	$colValue[] = request()->input('accounts_address_4');

	$colNames[] = '`accounts_contact`=?';
	$colValue[] = request()->input('accounts_contact');

	$colNames[] = '`tel_number`=?';
	$colValue[] = request()->input('tel_number');

	$colNames[] = '`internal_email`=?';
	$colValue[] = str_replace(array("\r", "\n"), '', request()->input('internal_email'));

	$colNames[] = '`credit_terms`=?';
	$colValue[] = request()->input('credit_terms');

	$colNames[] = '`pricedefault`=?';
	$colValue[] = request()->input('pricedefault');

	$colNames[] = '`credit_rating`=?';
	$colValue[] = request()->input('credit_rating');

	$colNames[] = '`flaguplimit`=?';
	$colValue[] = request()->input('flaguplimit');

    $colNames[] = '`can_reserve`=?';
	$colValue[] = request()->has('can_reserve')?1:0;

	$current_outstanding = request()->input('current_outstanding');
	$payment_received = request()->input('payment_received');
	$colNames[] = '`current_outstanding`=?';
	$colValue[] = (float) $current_outstanding - (float) $payment_received;

	$colNames[] = '`accounts_email`=?';
	$colValue[] = str_replace(array("\r", "\n"), '', request()->input('accounts_email'));

	$colNames[] = '`accounts_comments`=?';
	$colValue[] = request()->input('accounts_comments');

	$colNames[] = '`default_salesman_id`=?';
	$colValue[] = request()->input('default_salesman_id');

	$colNames[] = '`credit_grace`=?';
	$colValue[] = request()->input('credit_grace');

	$colNames[] = '`due_warning`=?';
	$colValue[] = request()->input('due_warning');

	$colNames[] = '`disabled`=?';
	$colValue[] = (request()->input('disabled') !== null && request()->input('disabled') == "1")?"1":"0";

	$colNames[] = "`markup_amount`=?";
	$colValue[] = request()->input('markup_amount');

    $colNames[] = "`site_id`=?";
	$colValue[] = (request()->input('site_id')!=null && request()->input('site_id') != "")?request()->input('site_id'):"1";

    //$colNames[] = "`check_saledate`=?";
	//$colValue[] = request()->input('check_saledate');

    $colNames[] = "`is_petfood_customer`=?";
	$colValue[] = (request()->input('is_petfood_customer')!=null && request()->input('is_petfood_customer') != "")?request()->input('is_petfood_customer'):"0";

    $colNames[] = "`override`=?";
	$colValue[] = request()->input('override_hidden',$c->override);

    $colNames[] = "`markup_enabled`=?";
	$colValue[] = (request()->input('markup_enabled_hidden',$c->markup_enabled))?1:0;

    $colNames[] = "`delivery_day_checking`=?";
    $colValue[] = (request()->input('delivery_day_checking_hidden',$c->delivery_day_checking))?1:0;

    $colNames[] = "`delivery_day_override`=?";
	$colValue[] = request()->input('delivery_day_override_hidden',$c->delivery_day_override);

    $colNames[] = "`override_cost_check`=?";
    $colValue[] = request()->input('override_cost_check_hidden',$c->override_cost_check);

    $colNames[] = "`default_finance_person_id`=?";
    $colValue[] = request()->input('default_finance_person_id',$c->default_finance_person_id);

	define('DEL_SUNDAY',     1);
	define('DEL_SATURDAY',   2);
	define('DEL_FRIDAY',     4);
	define('DEL_THURSDAY',   8);
	define('DEL_WEDNESDAY', 16);
	define('DEL_TUESDAY',   32);
	define('DEL_MONDAY',    64);
	$days = 0;
	if (request()->has('del_monday') 	&& request()->input('del_monday') == 1) 	$days += DEL_MONDAY;
	if (request()->has('del_tuesday') 	&& request()->input('del_tuesday') == 1) 	$days += DEL_TUESDAY;
	if (request()->has('del_wednesday') && request()->input('del_wednesday') == 1) 	$days += DEL_WEDNESDAY;
	if (request()->has('del_thursday') 	&& request()->input('del_thursday') == 1) 	$days += DEL_THURSDAY;
	if (request()->has('del_friday') 	&& request()->input('del_friday') == 1) 	$days += DEL_FRIDAY;
	if (request()->has('del_saturday') 	&& request()->input('del_saturday') == 1) 	$days += DEL_SATURDAY;
	if (request()->has('del_sunday') 	&& request()->input('del_sunday') == 1) 	$days += DEL_SUNDAY;

	$colNames[] = '`delivery_days` = ?';
	$colValue[] = $days;

	$colNames[] = '`sage_no` = ?';
	$colValue[] = request()->input('sage_no');

    if (User::find(Auth::id())->hasPermission("control_credit_enabled")) {
        $colNames[] = "`cost_check_enabled`=?";
        $colValue[] = request()->input('cost_check_enabled_hidden',$c->cost_check_enabled);

        $colNames[] = "`check_saledate`=?";
        $colValue[] = request()->input('check_saledate_hidden',$c->check_saledate);

        $colNames[] = "`credit_enabled`=?";
        $colValue[] = (request()->input('credit_enabled_hidden',$c->credit_enabled))?1:0;
    }

	$colValue[] = request()->input('id');
	$x = "UPDATE `customers` SET ".implode(",",$colNames)." WHERE id=? LIMIT 1";
	$y = prepareExecuteQuery($x,str_repeat("s",count($colValue)),$colValue);

    foreach (request()->input('address_id') as $index => $address_id)
	{
        $ca = ClientAddress::where('client_id', request()->input('id'))->where('address_id', $address_id)->where('client_type', ClientType::CUSTOMER->value)->first();
        if (!$ca) {
            $ca = new ClientAddress();
            $ca->client_id = request()->input('id');
            $ca->address_id = $address_id;
            $ca->client_type = ClientType::CUSTOMER->value;
        }
        $ca->address_1 = request()->input('address_1')[$index] ?? null;
        $ca->address_2 = request()->input('address_2')[$index] ?? null;
        $ca->address_3 = request()->input('address_3')[$index] ?? null;
        $ca->address_4 = request()->input('address_4')[$index] ?? null;
        $ca->postcode = request()->input('postcode')[$index] ?? null;
        $ca->address_number = request()->input('address_number')[$index] ?? null;
        $ca->site_id = request()->input('address_site_id')[$index] ?? null;
        $ca->restrictions = request()->input('restrictions')[$index] ?? null;
        $ca->save();
	}
?>
<script>
	window.location = '../manageCustomers.php?id=<?php echo $id; ?>';
</script>
