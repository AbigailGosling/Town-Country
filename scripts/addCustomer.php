<?php
	require('../functions.php');
	
	$businessname = mysqli_real_escape_string($conn, $_POST['businessname']);
	
	$tradingas = mysqli_real_escape_string($conn, $_POST['tradingas']);
	
	$nameofbuyer = mysqli_real_escape_string($conn, $_POST['nameofbuyer']);
	
		
	$contactnumber = mysqli_real_escape_string($conn, $_POST['contactnumber']);
	
	
	$customer_email = str_replace(array("\r", "\n"), '', mysqli_real_escape_string($conn, $_POST['customer_email']));

	
	$companyregno = mysqli_real_escape_string($conn, $_POST['companyregno']);
	

	$accounts_address_1 = mysqli_real_escape_string($conn, $_POST['accounts_address_1']);
	
	
	$accounts_address_2 = mysqli_real_escape_string($conn, $_POST['accounts_address_2']);
	
	
	$accounts_address_3 = mysqli_real_escape_string($conn, $_POST['accounts_address_3']);
	

	$accounts_address_4 = mysqli_real_escape_string($conn, $_POST['accounts_address_4']);
	
	
	
	$accounts_contact = mysqli_real_escape_string($conn, $_POST['accounts_contact']);
	
	
	$tel_number = mysqli_real_escape_string($conn, $_POST['tel_number']);
	
		
	$internal_email = str_replace(array("\r", "\n"), '', mysqli_real_escape_string($conn, $_POST['internal_email']));
	

	
	$credit_terms = mysqli_real_escape_string($conn, $_POST['credit_terms']);
	
	
	$pricedefault = mysqli_real_escape_string($conn, $_POST['pricedefault']);
	

	$credit_rating = mysqli_real_escape_string($conn, $_POST['credit_rating']);
	
	
	$flaguplimit = mysqli_real_escape_string($conn, $_POST['flaguplimit']);
	
				
	$current_outstanding = mysqli_real_escape_string($conn, $_POST['current_outstanding']);
	$colNames = array();
	$colValue = array();
	for ($u=1;$u<10;$u++)
	{
		$colNames[] = '`address'.$u.'_1`';
		$colValue[] = "'".mysqli_real_escape_string($conn, $_POST['address'.$u.'_1'])."'";

		$colNames[] = '`address'.$u.'_2`';
		$colValue[] = "'".mysqli_real_escape_string($conn, $_POST['address'.$u.'_2'])."'";

		$colNames[] = '`address'.$u.'_3`';
		$colValue[] = "'".mysqli_real_escape_string($conn, $_POST['address'.$u.'_3'])."'";

		$colNames[] = '`address'.$u.'_4`';
		$colValue[] = "'".mysqli_real_escape_string($conn, $_POST['address'.$u.'_4'])."'";

		$colNames[] = '`postcode_'.$u.'`';
		$colValue[] = "'".mysqli_real_escape_string($conn, $_POST['postcode_'.$u])."'";

		$colNames[] = '`address'.$u.'_number`';
		$colValue[] = "'".mysqli_real_escape_string($conn, $_POST['address'.$u.'_number'])."'";
	}


	$accounts_email = str_replace(array("\r", "\n"), '', mysqli_real_escape_string($conn, $_POST['accounts_email']));
	$accounts_comments = mysqli_real_escape_string($conn, $_POST['accounts_comments']);
	
	$default_salesman_id = mysqli_real_escape_string($conn, $_POST['default_salesman_id']);
	
 	
	$x = "INSERT INTO `customers` (`businessname`, `tradingas`, `nameofbuyer`, `contactnumber`, `customer_email`, `companyregno`, `accounts_address_1`, `accounts_address_2`, `accounts_address_3`, `accounts_address_4`, `accounts_contact`, `tel_number`, `internal_email`, `credit_terms`, `pricedefault`, `credit_rating`, `flaguplimit`, `current_outstanding`,`accounts_email`,`accounts_comments`,`default_salesman_id`, ".implode(",",$colNames).") 
	VALUES
	('$businessname','$tradingas','$nameofbuyer','$contactnumber','$customer_email','$companyregno','$accounts_address_1','$accounts_address_2','$accounts_address_3','$accounts_address_4','$accounts_contact','$tel_number','$internal_email','$credit_terms','$pricedefault','$credit_rating','$flaguplimit','$current_outstanding','$accounts_email','$accounts_comments','$default_salesman_id', ".implode(",",$colNames).");";
	
	$y = mysqli_query($conn, $x) or die(mysqli_error($conn));
	
?>

<script>
	window.location = '../manageCustomers.php';
</script>
