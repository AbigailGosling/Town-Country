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
        if($paymentMethod == 'CREDIT_NOTE'){
            $amount = 0;
        }
        $x = "INSERT into invoice_payments (invoice_id,payment_method,amount,meta_data,payment_recorded_by) 
		VALUES ('$invoiceID','$paymentMethod','$amount','$metaData','$currentUser')";
		$y = mysqli_query($conn, $x) or die(mysqli_error($conn));
         
		$id = mysqli_insert_id($conn);

        if($paymentMethod == 'CREDIT_NOTE'){
            //credit_note_items
            $i = 0;
            foreach($_POST['product_id'] as $product_id){

                $price = $_POST['price'][$i];
                $quantity = $_POST['quantity'][$i];

                $y = mysqli_query($conn, "INSERT into `credit_note_items` (payment_id,product_id,quantity,price) VALUES ('$id','$product_id','$quantity','$price')");

                $i++;
            }
        }

    }else{
        
        $x = "UPDATE `invoice_payments` SET amount='$amount', payment_method='$paymentMethod', meta_data='$metaData' WHERE id ='$paymentID'";
	    $y = mysqli_query($conn, $x);

        if($paymentMethod == 'CREDIT_NOTE'){
            $i = 0;
            foreach($_POST['product_id'] as $product_id){
                
                $price = $_POST['price'][$i];
                $quantity = $_POST['quantity'][$i];

                $y = mysqli_query($conn, "UPDATE `credit_note_items` SET quantity='$quantity', price='$price' WHERE payment_id='$paymentID' && product_id='$product_id'") or die(mysqli_error($conn));

                $i++;
            }
 
        }

    }

    header('Location: /single_invoice_payments.php?customer_id=' .$customerID . '&invoice_id=' . $invoiceID);
    
?>

   