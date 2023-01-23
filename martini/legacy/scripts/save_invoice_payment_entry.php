<?php

	require(__DIR__.'/../functions.php');
    
    $customerID = request('customer_id');
    $paymentID = request('payment_id');
    $invoiceID = request('invoice_id');
    $amount = floatval(request('amount'));
    $metaData = request('meta_data');
    $paymentMethod = request('payment_method');
    
    
    if(empty($customerID) || empty($invoiceID) || ($amount == '' && $paymentMethod != 'CREDIT_NOTE') || !in_array($paymentMethod, PAYMENT_METHODS) || !$_SESSION['USER']){
        
        header('Location: single_invoice_payments.php?customer_id=' .$customerID . '&invoice_id=' . $invoiceID);
        die();
    }

    $currentUser = $_SESSION['USER'];
    
    if(empty($paymentID)){
        $x = "DELETE FROM customer_outstanding_cache WHERE customer_id = ?";
	    $y = prepareExecuteQuery($x,'i',[$customerID]);
        if($paymentMethod == 'CREDIT_NOTE'){
            $amount = 0;
        }
        $x = "INSERT into invoice_payments (invoice_id,payment_method,amount,meta_data,payment_recorded_by) 
		VALUES (?,?,?,?,?)";
		$y = prepareExecuteQuery($x,'issss',[$invoiceID,$paymentMethod,$amount,$metaData,$currentUser]);
         
		$id = mysqli_insert_id($conn);

        if($paymentMethod == 'CREDIT_NOTE'){
            //credit_note_items
            $i = 0;
            foreach(request('product_id') as $product_id){

                $price = request('price'][$i);
                $quantity = request('quantity'][$i);
                $description = request('description'][$i);

                $y = prepareExecuteQuery("INSERT into `credit_note_items` (payment_id,product_id,quantity,price,`description`) VALUES (?,?,?,?,?)"
            ,'issss',[$id,$product_id,$quantity,$price,$description]);
                
                $i++;
            }
        }

    }else{
        $x = "DELETE FROM customer_outstanding_cache WHERE customer_id = ?";
	    $y = prepareExecuteQuery($x,'i',[$customerID]);

        $x = "UPDATE `invoice_payments` SET amount=?, payment_method=?, meta_data=? WHERE id =?";
	    $y = prepareExecuteQuery($x,'sssi',[$amount,$paymentMethod,$metaData,$paymentID]);

        if($paymentMethod == 'CREDIT_NOTE'){
            $i = 0;

            if(request('delete_ids') != null){
                
                $DELETE_IDS = $mysqli->real_escape_string( request('delete_ids'));
                $DELETE_IDS = rtrim($DELETE_IDS, ',');
                
                prepareExecuteQuery("DELETE FROM `credit_note_items` WHERE id IN ($DELETE_IDS)");
                
            }

            foreach(request('product_id') as $product_id){
                $credit_id = request('credit_id'][$i);
                $price = request('price'][$i);
                $quantity = request('quantity'][$i);
                $description = request('description'][$i);
                
                $y = prepareExecuteQuery("UPDATE `credit_note_items` SET quantity=?, price=?, `description`=? WHERE id=?",
            'sssi',[$quantity,$price,$description,$credit_id]);
                 
                $i++;
            }
 
        }

    }

    header('Location: single_invoice_payments.php?customer_id=' .$customerID . '&invoice_id=' . $invoiceID);
    
?>

   