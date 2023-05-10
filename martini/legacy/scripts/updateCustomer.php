<?php
	require(__DIR__.'/../functions.php');
	$colValue = array();
	$colValue[] = $mysqli->real_escape_string( request()->input('businessname'));
	$colValue[] = $mysqli->real_escape_string( request()->input('tradingas'));
	$colValue[] = $mysqli->real_escape_string( request()->input('nameofbuyer'));
	$colValue[] = $mysqli->real_escape_string( request()->input('contactnumber'));
	$colValue[] = str_replace(array("\r", "\n"), '', $mysqli->real_escape_string( request()->input('customer_email')));
	$colValue[] = $mysqli->real_escape_string( request()->input('companyregno'));
	$colValue[] = $mysqli->real_escape_string( request()->input('accounts_address_1'));
	$colValue[] = $mysqli->real_escape_string( request()->input('accounts_address_2'));
	$colValue[] = $mysqli->real_escape_string( request()->input('accounts_address_3'));
	$colValue[] = $mysqli->real_escape_string( request()->input('accounts_address_4'));
	$colValue[] = $mysqli->real_escape_string( request()->input('accounts_contact'));
	$colValue[] = $mysqli->real_escape_string( request()->input('tel_number'));
	$colValue[] = str_replace(array("\r", "\n"), '', $mysqli->real_escape_string( request()->input('internal_email')));
	$colValue[] = $mysqli->real_escape_string( request()->input('credit_terms'));
	$colValue[] = $mysqli->real_escape_string( request()->input('pricedefault'));
	$colValue[] = $mysqli->real_escape_string( request()->input('credit_rating'));
	$colValue[] = $mysqli->real_escape_string( request()->input('flaguplimit'));
	$current_outstanding = $mysqli->real_escape_string( request()->input('current_outstanding'));	
	$payment_received = $mysqli->real_escape_string( request()->input('payment_received'));	
	$colValue[] = (float) $current_outstanding - (float) $payment_received;
	$colValue[] = str_replace(array("\r", "\n"), '', $mysqli->real_escape_string( request()->input('accounts_email')));
	$colValue[] = $mysqli->real_escape_string( request()->input('accounts_comments'));
	$colValue[] = $mysqli->real_escape_string( request()->input('default_salesman_id'));
	$colValue[] = $mysqli->real_escape_string( request()->input('credit_grace'));
	$colValue[] = $mysqli->real_escape_string( request()->input('due_warning'));
	$colValue[] = (request()->input('disabled') !== null && request()->input('disabled') == "1")?"1":"0";
	$colValue[] = $mysqli->real_escape_string( request()->input('markup_type'));
	$colValue[] = $mysqli->real_escape_string( request()->input('markup_amount'));
	$colNames = array();
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
	$colValue[] = $mysqli->real_escape_string( request()->input('id'));
	$x = "UPDATE `customers` SET 
		businessname=?, 
		tradingas=?, 
		nameofbuyer=?, 
		contactnumber=?, 
		customer_email=?, 
		companyregno=?, 
		accounts_address_1=?, 
		accounts_address_2=?, 
		accounts_address_3=?, 
		accounts_address_4=?, 
		accounts_contact=?,
		tel_number=?, 
		internal_email=?,
		credit_terms=?, 
		pricedefault=?, 
		credit_rating=?, 
		flaguplimit=?, 
		current_outstanding=?,
		accounts_email=?, 
		accounts_comments=?, 
		default_salesman_id=?,
		`disabled`=?,
		`due_warning`=?,
		`credit_grace`=?,
		`markup_type`=?,
		`markup_amount`=?,
		".implode(",",$colNames)."
		 WHERE id=? LIMIT 1";
	$y = prepareExecuteQuery($x,str_repeat("s",count($colValue)),$colValue);
?>
<script>
	window.location = '../manageCustomers.php?id=<?php echo $id; ?>';
</script>