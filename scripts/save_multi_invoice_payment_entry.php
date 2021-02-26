<?php

	require('../functions.php');
    
    $customerID = $_POST['customer_id'];
    $paymentData = $_POST['payment_data'];
    $paymentData = explode(",", $paymentData);
    $metaData = $_POST['meta_data'];
    $paymentMethod = $_POST['payment_method'];
    
    
    if(empty($customerID) || count($paymentData) == 0 || !in_array($paymentMethod, PAYMENT_METHODS) || !$_SESSION['USER']){
        
        header('Location: /multi_invoice_payments.php?customer_id=' .$customerID);
        die();
    }

    $currentUser = $_SESSION['USER'];
    
    foreach($paymentData as $payment){

        $data = explode("|", $payment);
        $invoiceID = $data[0];
        $amount = $data[1];

        $x = "INSERT into invoice_payments (invoice_id,payment_method,amount,meta_data,payment_recorded_by) 
		VALUES ('$invoiceID','$paymentMethod','$amount','$metaData','$currentUser')";
		$y = mysqli_query($conn, $x) or die(mysqli_error($conn));
         
		$id = mysqli_insert_id($conn);

    }
    
    header('Location: /multi_invoice_payments.php?customer_id=' .$customerID);
    
?>

   