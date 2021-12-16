<?php

	require('../functions.php');
    
    $customerID = $_POST['customer_id'];
    $paymentID = $_POST['payment_id'];
    $invoiceID = $_POST['invoice_id'];
    $amount = floatval($_POST['amount']);
    $metaData = $_POST['meta_data'];
    $paymentMethod = $_POST['payment_method'];
    
    
    if(empty($customerID) || empty($invoiceID) || ($amount == '' && $paymentMethod != 'CREDIT_NOTE') || ($amount < 0 && $paymentMethod != 'CREDIT_NOTE') || !in_array($paymentMethod, PAYMENT_METHODS) || !$_SESSION['USER']){
        
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
                $description = $_POST['description'][$i];

                $y = mysqli_query($conn, "INSERT into `credit_note_items` (payment_id,product_id,quantity,price,`description`) VALUES ('$id','$product_id','$quantity','$price','$description')");
                
                $i++;
            }
        }

    }else{
        $x = "DELETE FROM customer_outstanding_cache WHERE customer_id = $customerID";
	    $y = mysqli_query($conn, $x);

        $x = "UPDATE `invoice_payments` SET amount='$amount', payment_method='$paymentMethod', meta_data='$metaData' WHERE id ='$paymentID'";
	    $y = mysqli_query($conn, $x);

        if($paymentMethod == 'CREDIT_NOTE'){
            $i = 0;

            if($_POST['delete_ids'] != null){
                
                $DELETE_IDS = mysqli_real_escape_string($conn, $_POST['delete_ids']);
                $DELETE_IDS = rtrim($DELETE_IDS, ',');
                
                mysqli_query($conn, "DELETE FROM `credit_note_items` WHERE id IN ($DELETE_IDS)");
                
            }

            foreach($_POST['product_id'] as $product_id){
                $credit_id = $_POST['credit_id'][$i];
                $price = $_POST['price'][$i];
                $quantity = $_POST['quantity'][$i];
                $description = $_POST['description'][$i];
                
                $y = mysqli_query($conn, "UPDATE `credit_note_items` SET quantity='$quantity', price='$price', `description`='$description' WHERE id='$credit_id'") or die(mysqli_error($conn));
                 
                $i++;
            }
 
        }

    }

    header('Location: /single_invoice_payments.php?customer_id=' .$customerID . '&invoice_id=' . $invoiceID);
    
?>

   