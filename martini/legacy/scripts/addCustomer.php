<?php

use App\Helpers\ProcessHelper;
use App\Models\ClientAddress;
use App\Models\ClientType;

	require(__DIR__.'/../functions.php');
	$colNames = array();
	$colValue = array();

	$colNames[] = "`businessname`";
	$colValue[] = request()->input('businessname');

	$colNames[] = "`tradingas`";
	$colValue[] = request()->input('tradingas');

	$colNames[] = "`nameofbuyer`";
	$colValue[] = request()->input('nameofbuyer');

	$colNames[] = "`contactnumber`";
	$colValue[] = request()->input('contactnumber');

	$colNames[] = "`customer_email`";
	$colValue[] = str_replace(array("\r", "\n"), '', request()->input('customer_email'));

	$colNames[] = "`companyregno`";
	$colValue[] = request()->input('companyregno');

	$colNames[] = "`accounts_address_1`";
	$colValue[] = request()->input('accounts_address_1');

	$colNames[] = "`accounts_address_2`";
	$colValue[] = request()->input('accounts_address_2');

	$colNames[] = "`accounts_address_3`";
	$colValue[] = request()->input('accounts_address_3');

	$colNames[] = "`accounts_address_4`";
	$colValue[] = request()->input('accounts_address_4');

	$colNames[] = "`accounts_contact`";
	$colValue[] = request()->input('accounts_contact');

	$colNames[] = "`tel_number`";
	$colValue[] = request()->input('tel_number');

	$colNames[] = "`internal_email`";
	$colValue[] = str_replace(array("\r", "\n"), '', request()->input('internal_email'));

	$colNames[] = "`credit_terms`";
	$colValue[] = request()->input('credit_terms');

	$colNames[] = "`pricedefault`";
	$colValue[] = request()->input('pricedefault');

	$colNames[] = "`credit_rating`";
	$colValue[] = request()->input('credit_rating');

    $colNames[] = "`insured_credit`";
	$colValue[] = request()->input('insured_credit');

	$colNames[] = "`flaguplimit`";
	$colValue[] = request()->input('flaguplimit');

	$colNames[] = "`current_outstanding`";
	$colValue[] = request()->input('current_outstanding');

	$colNames[] = "`accounts_email`";
	$colValue[] = str_replace(array("\r", "\n"), '', request()->input('accounts_email'));

	$colNames[] = "`accounts_comments`";
	$colValue[] = request()->input('accounts_comments');

	$colNames[] = "`default_salesman_id`";
	$colValue[] = request()->input('default_salesman_id');

    $colNames[] = "`can_reserve`";
	$colValue[] =  request()->has('can_reserve')?1:0;

    //$colNames[] = "`check_saledate`";
	//$colValue[] = request()->input('check_saledate',true);

	$colNames[] = "`due_warning`";
	$colValue[] = (request()->input('due_warning')!=null && request()->input('due_warning') != "")?request()->input('due_warning'):"0";

	$colNames[] = "`credit_grace`";
	$colValue[] = (request()->input('credit_grace')!=null && request()->input('credit_grace') != "")?request()->input('credit_grace'):"0";

	$colNames[] = "`markup_amount`";
	$colValue[] = request()->input('markup_amount',0);

    $colNames[] = "`site_id`";
	$colValue[] = (request()->input('site_id')!=null && request()->input('site_id') != "")?request()->input('site_id'):"1";

    $colNames[] = "`is_petfood_customer`";
	$colValue[] = (request()->input('is_petfood_customer')!=null && request()->input('is_petfood_customer') != "")?request()->input('is_petfood_customer'):"0";

    $colNames[] = "`credit_enabled`";
	$colValue[] = request()->input('credit_enabled_hidden',0);

    $colNames[] = "`override`";
	$colValue[] = request()->input('override_hidden',0);

    $colNames[] = "`markup_enabled`";
	$colValue[] = request()->input('markup_enabled_hidden',0);

    $colNames[] = "`delivery_day_checking`";
	$colValue[] = request()->input('delivery_day_checking_hidden',0);

    $colNames[] = "`delivery_day_override`";
	$colValue[] = request()->input('delivery_day_override_hidden',0);

    $colNames[] = "`check_saledate`";
	$colValue[] = request()->input('check_saledate_hidden',0);

    $colNames[] = "`override_cost_check`";
    $colValue[] = request()->input('override_cost_check_hidden',0);

    $colNames[] = "`cost_check_enabled`";
    $colValue[] = request()->input('cost_check_enabled_hidden',0);

    $colNames[] = "`default_finance_person_id`";
    $colValue[] = request()->input('default_finance_person_id',0);

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

	$colNames[] = '`delivery_days`';
	$colValue[] = $days;

	$colNames[] = '`sage_no`';
	$colValue[] = request()->input('sage_no');

	$x = "INSERT INTO `customers` (".implode(",",$colNames).",`businessnameDM`)
	VALUES
	(".implode(",",array_fill(0,count($colNames),"?")).",dm(?));";

	$customer_id = prepareExecuteQuery($x,str_repeat('s',count($colNames)).'s',array_merge($colValue, [request()->input('businessname')]),true);


	foreach (request()->input('address_id') as $index => $address_id)
	{
        if (request()->input('address_site_id')[$index] == "" || request()->input('address_site_id')[$index] == null) {
            continue; // Skip addresses without a site_id
        }
        $ca = new ClientAddress();
        $ca->client_id = $customer_id;
        $ca->address_id = $address_id;
        $ca->client_type = ClientType::CUSTOMER->value;
        $ca->address_1 = request()->input('address_1')[$index] ?? null;
        $ca->address_2 = request()->input('address_2')[$index] ?? null;
        $ca->address_3 = request()->input('address_3')[$index] ?? null;
        $ca->address_4 = request()->input('address_4')[$index] ?? null;
        $ca->postcode = request()->input('postcode')[$index] ?? null;
        $ca->address_number = request()->input('address_number')[$index] ?? null;
        $ca->site_id = request()->input('address_site_id')[$index];
        //$ca->restrictions = request()->input('restrictions')[$index] ?? null;
        $ca->allowed_vehicle_types = request()->input('address_allowed_vehicle_types')[$index] ?? '';
        $ca->require_tail_lift = request()->has('address_require_tail_lift'.$address_id);
        $ca->opening_time = request()->input('opening_time')[$index] ?? null;
        $ca->closing_time = request()->input('closing_time')[$index] ?? null;
        $ca->open_bank_holiday_mondays = request()->has('address_bhm'.$address_id);
        $ca->open_bank_holiday_fridays = request()->has('address_bhf'.$address_id);
        $ca->geocoding_tried = 0;
        $ca->collection = request()->has('address_collection'.$address_id);
        $ca->lat = null;
        $ca->lon = null;
        $ca->save();
        ProcessHelper::runInBackground('run:geocode_address '.$ca->id);
	}

?>

<script>
	window.location = '../manageCustomers.php';
</script>
