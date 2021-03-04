<?php

	require('../functions.php');
    
    $customerID = $_POST['customer_id'];
    $paymentID = $_POST['payment_id'];
    $invoiceID = $_POST['invoice_id'];
    $amount = floatval($_POST['amount']);
    $metaData = $_POST['meta_data'];
    $paymentMethod = $_POST['payment_method'];
    
    
    if(empty($customerID) || empty($invoiceID) || $amount == '' || $amount < 0 || !in_array($paymentMethod, PAYMENT_METHODS) || !$_SESSION['USER']){
        
        header('Location: /single_invoice_payments.php?customer_id=' .$customerID . '&invoice_id=' . $invoiceID);
        die();
    }

    $currentUser = $_SESSION['USER'];
    
    if(empty($paymentID)){
	
        $x = "INSERT into invoice_payments (invoice_id,payment_method,amount,meta_data,payment_recorded_by) 
		VALUES ('$invoiceID','$paymentMethod','$amount','$metaData','$currentUser')";
		$y = mysqli_query($conn, $x) or die(mysqli_error($conn));
         
		$id = mysqli_insert_id($conn);

    }else{

        $x = "UPDATE `invoice_payments` SET amount='$amount', payment_method='$paymentMethod', meta_data='$metaData' WHERE id ='$paymentID'";
	    $y = mysqli_query($conn, $x);
    }

    header('Location: /single_invoice_payments.php?customer_id=' .$customerID . '&invoice_id=' . $invoiceID);
    
?>

   