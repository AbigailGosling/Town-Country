<?php
	require('../functions.php');
	
	
	$id = mysqli_real_escape_string($conn, $_POST['id']);
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
	
	$payment_received = mysqli_real_escape_string($conn, $_POST['payment_received']);
	
	$current_outstanding = (float) $current_outstanding - (float) $payment_received;
	

	$credit_grace = mysqli_real_escape_string($conn, $_POST['credit_grace']);
	$due_warning = mysqli_real_escape_string($conn, $_POST['due_warning']);

	$accounts_email = str_replace(array("\r", "\n"), '', mysqli_real_escape_string($conn, $_POST['accounts_email']));
	$accounts_comments = mysqli_real_escape_string($conn, $_POST['accounts_comments']);
	
	$default_salesman_id = mysqli_real_escape_string($conn, $_POST['default_salesman_id']);

	$disabled = (isset($_POST['disabled']) && $_POST['disabled'] == "1")?"1":"0";
	$colNames = array();
	for ($u=1;$u<10;$u++)
	{
		$colNames[] = "`address".$u."_1` = '".mysqli_real_escape_string($conn, $_POST['address'.$u.'_1'])."'";

		$colNames[] = "`address".$u."_2` = '".mysqli_real_escape_string($conn, $_POST['address'.$u.'_2'])."'";
		
		$colNames[] = "`address".$u."_3` = '".mysqli_real_escape_string($conn, $_POST['address'.$u.'_3'])."'";

		$colNames[] = "`address".$u."_4` = '".mysqli_real_escape_string($conn, $_POST['address'.$u.'_4'])."'";

		$colNames[] = "`postcode_".$u."` = '".mysqli_real_escape_string($conn, $_POST['postcode_'.$u])."'";

		$colNames[] = "`address".$u."_number` = '".mysqli_real_escape_string($conn, $_POST['address'.$u.'_number'])."'";
	}
	$x = "UPDATE `customers` SET 
		businessname='$businessname', 
		tradingas='$tradingas', 
		nameofbuyer='$nameofbuyer', 
		contactnumber='$contactnumber', 
		customer_email='$customer_email', 
		companyregno='$companyregno', 
		accounts_address_1='$accounts_address_1', 
		accounts_address_2='$accounts_address_2', 
		accounts_address_3='$accounts_address_3', 
		accounts_address_4='$accounts_address_4', 
		accounts_contact='$accounts_contact',
		tel_number='$tel_number', 
		internal_email='$internal_email',
		credit_terms='$credit_terms', 
		pricedefault='$pricedefault', 
		credit_rating='$credit_rating', 
		flaguplimit='$flaguplimit', 
		current_outstanding='$current_outstanding',
		accounts_email='$accounts_email', 
		accounts_comments='$accounts_comments', 
		default_salesman_id='$default_salesman_id',
		`disabled`=$disabled,
		`due_warning`=$due_warning,
		`credit_grace`=$credit_grace,
		".implode(",",$colNames)."
		 WHERE id='$id' LIMIT 1";
	$y = mysqli_query($conn, $x) or die(mysqli_error($conn));
?>
<script>
	window.location = '../manageCustomers.php?id=<?php echo $id; ?>';
</script>