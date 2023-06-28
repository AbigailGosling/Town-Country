<?php

	require(__DIR__.'/../functions.php');
    
    $customerID = request()->input('customer_id');
    $paymentData = request()->input('payment_data');
    $paymentData = explode(",", $paymentData);
    $metaData = request()->input('meta_data');
    $paymentMethod = request()->input('payment_method');
    
    
    if(empty($customerID) || count($paymentData) == 0 || !in_array($paymentMethod, PAYMENT_METHODS) || !$_SESSION['USER']){
        
        header('Location: /multi_invoice_payments.php?customer_id=' .$customerID);
        die();
    }
    $x = "DELETE FROM customer_outstanding_cache WHERE customer_id = ?";
    $y = prepareExecuteQuery($x,'i',[$customerID]);
    $currentUser = $_SESSION['USER'];
    
    foreach($paymentData as $payment){

        $data = explode("|", $payment);
        $invoiceID = $data[0];
        $amount = $data[1];

        $x = "INSERT into invoice_payments (invoice_id,payment_method,amount,meta_data,payment_recorded_by) 
		VALUES (?,?,?,?,?)";
		$y = prepareExecuteQuery($x,'sssss',[$invoiceID,$paymentMethod,$amount,$metaData,$currentUser]);
         
		$id = mysqli_insert_id($mysqli);

    }
    
    header('Location: /multi_invoice_payments.php?customer_id=' .$customerID);
    
?>

   