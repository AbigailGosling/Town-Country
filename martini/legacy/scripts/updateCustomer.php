<?php
	require(__DIR__.'/../functions.php');
	$colValue = array();
	$colValue[] = $mysqli->real_escape_string( request('businessname'));
	$colValue[] = $mysqli->real_escape_string( request('tradingas'));
	$colValue[] = $mysqli->real_escape_string( request('nameofbuyer'));
	$colValue[] = $mysqli->real_escape_string( request('contactnumber'));
	$colValue[] = str_replace(array("\r", "\n"), '', $mysqli->real_escape_string( request('customer_email')));
	$colValue[] = $mysqli->real_escape_string( request('companyregno'));
	$colValue[] = $mysqli->real_escape_string( request('accounts_address_1'));
	$colValue[] = $mysqli->real_escape_string( request('accounts_address_2'));
	$colValue[] = $mysqli->real_escape_string( request('accounts_address_3'));
	$colValue[] = $mysqli->real_escape_string( request('accounts_address_4'));
	$colValue[] = $mysqli->real_escape_string( request('accounts_contact'));
	$colValue[] = $mysqli->real_escape_string( request('tel_number'));
	$colValue[] = str_replace(array("\r", "\n"), '', $mysqli->real_escape_string( request('internal_email')));
	$colValue[] = $mysqli->real_escape_string( request('credit_terms'));
	$colValue[] = $mysqli->real_escape_string( request('pricedefault'));
	$colValue[] = $mysqli->real_escape_string( request('credit_rating'));
	$colValue[] = $mysqli->real_escape_string( request('flaguplimit'));
	$current_outstanding = $mysqli->real_escape_string( request('current_outstanding'));	
	$payment_received = $mysqli->real_escape_string( request('payment_received'));	
	$colValue[] = (float) $current_outstanding - (float) $payment_received;
	$colValue[] = str_replace(array("\r", "\n"), '', $mysqli->real_escape_string( request('accounts_email')));
	$colValue[] = $mysqli->real_escape_string( request('accounts_comments'));
	$colValue[] = $mysqli->real_escape_string( request('default_salesman_id'));
	$colValue[] = $mysqli->real_escape_string( request('credit_grace'));
	$colValue[] = $mysqli->real_escape_string( request('due_warning'));
	$colValue[] = (isset(request('disabled']) && $_POST['disabled') == "1")?"1":"0";
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
	$colValue[] = $mysqli->real_escape_string( request('id'));
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
		".implode(",",$colNames)."
		 WHERE id=? LIMIT 1";
	$y = prepareExecuteQuery($x,str_repeat("s",count($colValue)),$colValue);
?>
<script>
	window.location = '../manageCustomers.php?id=<?php echo $id; ?>';
</script>