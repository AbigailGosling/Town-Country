<?php
	require('../functions.php');
	
	
	$id = mysqli_real_escape_string($conn, $_POST['id']);
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
	
	$payment_received = mysqli_real_escape_string($conn, $_POST['payment_received']);
	
	$current_outstanding = (float) $current_outstanding - (float) $payment_received;
	

	$accounts_email = mysqli_real_escape_string($conn, $_POST['accounts_email']);
	$accounts_comments = mysqli_real_escape_string($conn, $_POST['accounts_comments']);
	
	$default_salesman_id = mysqli_real_escape_string($conn, $_POST['default_salesman_id']);
	
	$x = "UPDATE `customers` SET businessname='$businessname', tradingas='$tradingas', address1_1='$address1_1', address1_2='$address1_2', address1_3='$address1_3'
	, address1_4='$address1_4', postcode_1='$postcode_1', address2_1='$address2_1', address2_2='$address2_2', address2_3='$address2_3', address2_4='$address2_4'
	, postcode_2='$postcode_2', address3_1='$address3_1', address3_2='$address3_2', address3_3='$address3_3', address3_4='$address3_4', postcode_3='$postcode_3'
	, nameofbuyer='$nameofbuyer', contactnumber='$contactnumber', customer_email='$customer_email', companyregno='$companyregno', accounts_address_1='$accounts_address_1'
	, accounts_address_2='$accounts_address_2', accounts_address_3='$accounts_address_3', accounts_address_4='$accounts_address_4', accounts_contact='$accounts_contact'
	, tel_number='$tel_number', internal_email='$internal_email', credit_terms='$credit_terms', pricedefault='$pricedefault', credit_rating='$credit_rating', flaguplimit='$flaguplimit'
	, current_outstanding='$current_outstanding',address1_number='$address1_number',address2_number='$address2_number',address3_number='$address3_number'
	, accounts_email='$accounts_email', accounts_comments='$accounts_comments', default_salesman_id='$default_salesman_id' WHERE id='$id' LIMIT 1";
	
	$y = mysqli_query($conn, $x) or die(mysqli_error($conn));
?>
<script>
	window.location = '../manageCustomers.php?id=<?php echo $id; ?>';
</script>