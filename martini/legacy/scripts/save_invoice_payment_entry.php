<?php

	require(__DIR__.'/../functions.php');
    $customerID = request()->input('customer_id');
    $paymentID = request()->input('payment_id');
    $invoiceID = request()->input('invoice_id');
    $amount = floatval(request()->input('amount'));
    $metaData = request()->input('meta_data');
    $paymentMethod = request()->input('payment_method');


    if(empty($customerID) || empty($invoiceID) || ($amount == '' && $paymentMethod != 'CREDIT_NOTE') || (!in_array($paymentMethod, PAYMENT_METHODS) && !in_array($paymentMethod, SUPPLIER_PAYMENT_METHODS)) || !$_SESSION['USER']){

        header('Location: ../single_invoice_payments.php?customer_id=' .$customerID . '&invoice_id=' . $invoiceID);
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
		$id = prepareExecuteQuery($x,'issss',[$invoiceID,$paymentMethod,$amount,$metaData,$currentUser],true);

        if($paymentMethod == 'CREDIT_NOTE'){
            //credit_note_items
            $i = 0;
            foreach(request()->input('product_id') as $product_id){

                $price = request()->input('price')[$i];
                $quantity = request()->input('quantity')[$i];
                $description = request()->input('description')[$i];

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

            if(request()->input('delete_ids') != null){

                $DELETE_IDS = request()->input('delete_ids');
                $DELETE_IDS = rtrim($DELETE_IDS, ',');

                prepareExecuteQuery("DELETE FROM `credit_note_items` WHERE id IN ($DELETE_IDS)");

            }

            foreach(request()->input('product_id') as $product_id){
                $credit_id = request()->input('credit_id')[$i];
                $price = request()->input('price')[$i];
                $quantity = request()->input('quantity')[$i];
                $description = request()->input('description')[$i];

                $y = prepareExecuteQuery("UPDATE `credit_note_items` SET quantity=?, price=?, `description`=? WHERE id=?",
            'sssi',[$quantity,$price,$description,$credit_id]);

                $i++;
            }

        }

    }

    header('Location: ../single_invoice_payments.php?customer_id=' .$customerID . '&invoice_id=' . $invoiceID);

?>

