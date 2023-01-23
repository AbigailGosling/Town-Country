<?php
	require(__DIR__.'/../functions.php');
	$colNames = array();
	$colValue = array();

	$colNames[] = "`businessname`";
	$colValue[] = $mysqli->real_escape_string( request('businessname'));
	
	$colNames[] = "`tradingas`";
	$colValue[] = $mysqli->real_escape_string( request('tradingas'));
	
	$colNames[] = "`nameofbuyer`";
	$colValue[] = $mysqli->real_escape_string( request('nameofbuyer'));
	
	$colNames[] = "`contactnumber`";	
	$colValue[] = $mysqli->real_escape_string( request('contactnumber'));
	
	$colNames[] = "`customer_email`";	
	$colValue[] = str_replace(array("\r", "\n"), '', $mysqli->real_escape_string( request('customer_email')));

	$colNames[] = "`companyregno`";	
	$colValue[] = $mysqli->real_escape_string( request('companyregno'));
	
	$colNames[] = "`accounts_address_1`";	
	$colValue[] = $mysqli->real_escape_string( request('accounts_address_1'));
	
	$colNames[] = "`accounts_address_2`";
	$colValue[] = $mysqli->real_escape_string( request('accounts_address_2'));
	
	$colNames[] = "`accounts_address_3`";
	$colValue[] = $mysqli->real_escape_string( request('accounts_address_3'));
	
	$colNames[] = "`accounts_address_4`";
	$colValue[] = $mysqli->real_escape_string( request('accounts_address_4'));
	
	$colNames[] = "`accounts_contact`";
	$colValue[] = $mysqli->real_escape_string( request('accounts_contact'));
	
	$colNames[] = "`tel_number`";
	$colValue[] = $mysqli->real_escape_string( request('tel_number'));
	
	$colNames[] = "`internal_email`";
	$colValue[] = str_replace(array("\r", "\n"), '', $mysqli->real_escape_string( request('internal_email')));
	
	$colNames[] = "`credit_terms`";
	$colValue[] = $mysqli->real_escape_string( request('credit_terms'));
	
	$colNames[] = "`pricedefault`";
	$colValue[] = $mysqli->real_escape_string( request('pricedefault'));
	
	$colNames[] = "`credit_rating`";
	$colValue[] = $mysqli->real_escape_string( request('credit_rating'));
	
	$colNames[] = "`flaguplimit`";
	$colValue[] = $mysqli->real_escape_string( request('flaguplimit'));
		
	$colNames[] = "`current_outstanding`";
	$colValue[] = $mysqli->real_escape_string( request('current_outstanding'));

	$colNames[] = "`accounts_email`";
	$colValue[] = str_replace(array("\r", "\n"), '', $mysqli->real_escape_string( request('accounts_email')));
	
	$colNames[] = "`accounts_comments`";
	$colValue[] = $mysqli->real_escape_string( request('accounts_comments'));
	
	$colNames[] = "`default_salesman_id`";
	$colValue[] = $mysqli->real_escape_string( request('default_salesman_id'));
	
	$colNames[] = "`due_warning`";
	$colValue[] = $mysqli->real_escape_string( request('due_warning'));

	$colNames[] = "`credit_grace`";
	$colValue[] = $mysqli->real_escape_string( request('credit_grace'));

	for ($u=1;$u<10;$u++)
	{
		$colNames[] = '`address'.$u.'_1`';
		$colValue[] = "'".$mysqli->real_escape_string( request('address'.$u.'_1'))."'";

		$colNames[] = '`address'.$u.'_2`';
		$colValue[] = "'".$mysqli->real_escape_string( request('address'.$u.'_2'))."'";

		$colNames[] = '`address'.$u.'_3`';
		$colValue[] = "'".$mysqli->real_escape_string( request('address'.$u.'_3'))."'";

		$colNames[] = '`address'.$u.'_4`';
		$colValue[] = "'".$mysqli->real_escape_string( request('address'.$u.'_4'))."'";

		$colNames[] = '`postcode_'.$u.'`';
		$colValue[] = "'".$mysqli->real_escape_string( request('postcode_'.$u))."'";

		$colNames[] = '`address'.$u.'_number`';
		$colValue[] = "'".$mysqli->real_escape_string( request('address'.$u.'_number'))."'";
	}

	$x = "INSERT INTO `customers` (".implode(",",$colNames).") 
	VALUES
	(".implode(",",array_fill(0,count($colNames),"?")).");";
	
	$y = prepareExecuteQuery($x,str_repeat('s',count($colNames)),$colValue);
	
?>

<script>
	window.location = '../manageCustomers.php';
</script>
