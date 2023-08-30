<?php
	require(__DIR__.'/../functions.php');
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

	$colNames[] = '`markup_type`=?';
	$colValue[] = $mysqli->real_escape_string( request()->input('markup_type'));

	$colNames[] = '`markup_amount`=?';
	$colValue[] = $mysqli->real_escape_string( request()->input('markup_amount'));

	for ($u=1;$u<10;$u++)
	{
		$colNames[] = "`address".$u."_1` = ?";
		$colValue[] = $mysqli->real_escape_string( request('address'.$u.'_1'));
		$colNames[] = "`address".$u."_2` = ?";
		$colValue[] = $mysqli->real_escape_string( request('address'.$u.'_2'));
		$colNames[] = "`address".$u."_3` = ?";
		$colValue[] = $mysqli->real_escape_string( request('address'.$u.'_3'));
		$colNames[] = "`address".$u."_4` = ?";
		$colValue[] = $mysqli->real_escape_string( request('address'.$u.'_4'));
		$colNames[] = "`postcode_".$u."` = ?";
		$colValue[] = $mysqli->real_escape_string( request('postcode_'.$u));
		$colNames[] = "`address".$u."_number` = ?";
		$colValue[] = $mysqli->real_escape_string( request('address'.$u.'_number'));
	}
	$colValue[] = request()->input('id');
	$x = "UPDATE `customers` SET ".implode(",",$colNames)." WHERE id=? LIMIT 1";
	$y = loggedQuery($x,str_repeat("s",count($colValue)),$colValue);
?>
<script>
	window.location = '../manageCustomers.php?id=<?php echo $id; ?>';
</script>