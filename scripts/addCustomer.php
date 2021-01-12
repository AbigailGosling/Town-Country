<?php
	require('../functions.php');
	
	$businessname = mysqli_real_escape_string($conn, $_POST['businessname']);
	
	$tradingas = mysqli_real_escape_string($conn, $_POST['tradingas']);
	
	
	
	$address1_1 = mysqli_real_escape_string($conn, $_POST['address1_1']);
	
	$address1_2 = mysqli_real_escape_string($conn, $_POST['address1_2']);
	
	$address1_3 = mysqli_real_escape_string($conn, $_POST['address1_3']);
	
	$address1_4 = mysqli_real_escape_string($conn, $_POST['address1_4']);
	
	$postcode_1 = mysqli_real_escape_string($conn, $_POST['postcode_1']);
	
	
	
	
	
	
	$address2_1 = mysqli_real_escape_string($conn, $_POST['address2_1']);
	
	
	$address2_2 = mysqli_real_escape_string($conn, $_POST['address2_2']);
	
	
	$address2_3 = mysqli_real_escape_string($conn, $_POST['address2_3']);
	
	
	$address2_4 = mysqli_real_escape_string($conn, $_POST['address2_4']);
	

	$postcode_2 = mysqli_real_escape_string($conn, $_POST['postcode_2']);
	
	


	$address3_1 = mysqli_real_escape_string($conn, $_POST['address3_1']);
	
	
	$address3_2 = mysqli_real_escape_string($conn, $_POST['address3_2']);
	
	
	$address3_3 = mysqli_real_escape_string($conn, $_POST['address3_3']);
	
	
	$address3_4 = mysqli_real_escape_string($conn, $_POST['address3_4']);
	

	$postcode_3 = mysqli_real_escape_string($conn, $_POST['postcode_3']);
	
	
	
		
	$nameofbuyer = mysqli_real_escape_string($conn, $_POST['nameofbuyer']);
	
		
	$contactnumber = mysqli_real_escape_string($conn, $_POST['contactnumber']);
	
	
	$customer_email = mysqli_real_escape_string($conn, $_POST['customer_email']);
	

	
	$salesman = mysqli_real_escape_string($conn, $_POST['salesman']);
	
	
	$companyregno = mysqli_real_escape_string($conn, $_POST['companyregno']);
	

	$accounts_address_1 = mysqli_real_escape_string($conn, $_POST['accounts_address_1']);
	
	
	$accounts_address_2 = mysqli_real_escape_string($conn, $_POST['accounts_address_2']);
	
	
	$accounts_address_3 = mysqli_real_escape_string($conn, $_POST['accounts_address_3']);
	

	$accounts_address_4 = mysqli_real_escape_string($conn, $_POST['accounts_address_4']);
	
	
	
	$accounts_contact = mysqli_real_escape_string($conn, $_POST['accounts_contact']);
	
	
	$tel_number = mysqli_real_escape_string($conn, $_POST['tel_number']);
	
		
	$internal_email = mysqli_real_escape_string($conn, $_POST['internal_email']);
	

	
	$credit_terms = mysqli_real_escape_string($conn, $_POST['credit_terms']);
	
	
	$pricedefault = mysqli_real_escape_string($conn, $_POST['pricedefault']);
	

	$credit_rating = mysqli_real_escape_string($conn, $_POST['credit_rating']);
	
	
	$flaguplimit = mysqli_real_escape_string($conn, $_POST['flaguplimit']);
	
				
	$current_outstanding = mysqli_real_escape_string($conn, $_POST['current_outstanding']);
	
		
	$address1_number = mysqli_real_escape_string($conn, $_POST['address1_number']);
	$address2_number = mysqli_real_escape_string($conn, $_POST['address2_number']);
	$address3_number = mysqli_real_escape_string($conn, $_POST['address3_number']);
	
	$currentUsers = implode(',', $_POST['users']);
	
	$x = "INSERT INTO `customers` (`businessname`, `tradingas`, `address1_1`, `address1_2`, `address1_3`, `address1_4`, `postcode_1`, `address2_1`, `address2_2`, `address2_3`, `address2_4`, `postcode_2`, `address3_1`, `address3_2`, `address3_3`, `address3_4`, `postcode_3`, `nameofbuyer`, `contactnumber`, `customer_email`, `salesman`, `companyregno`, `accounts_address_1`, `accounts_address_2`, `accounts_address_3`, `accounts_address_4`, `accounts_contact`, `tel_number`, `internal_email`, `credit_terms`, `pricedefault`, `credit_rating`, `flaguplimit`, `current_outstanding`,`address1_number`,`address2_number`,`address3_number`,`users`) 
	VALUES
	('$businessname','$tradingas','$address1_1','$address1_2','$address1_3','$address1_4','$postcode_1','$address2_1','$address2_2','$address2_3','$address2_4','$postcode_2','$address3_1','$address3_2','$address3_3','$address3_4','$postcode_3','$nameofbuyer','$contactnumber','$customer_email','$salesman','$companyregno','$accounts_address_1','$accounts_address_2','$accounts_address_3','$accounts_address_4','$accounts_contact','$tel_number','$internal_email','$credit_terms','$pricedefault','$credit_rating','$flaguplimit','$current_outstanding','$address1_number','$address2_number','$address3_number','$currentUsers');";
	
	$y = mysqli_query($conn, $x) or die(mysqli_error($conn));
	
?>

<script>
	window.location = '../manageCustomers.php';
</script>
