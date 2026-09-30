<?php

use App\Models\CreditNoteItem;
use App\Models\InvoicePayment;

	require(__DIR__.'/../functions.php');

    $customerID = request()->input('customer_id');
    $paymentID = request()->input('payment_id');
    $invoiceID = request()->input('invoice_id');
    $amount = floatval(request()->input('amount'));
    $metaData = request()->input('meta_data');
    $paymentMethod = request()->input('payment_method');
    $input = request()->all();

    if(empty($customerID) || empty($invoiceID) || ($amount == '' && $paymentMethod != 'CREDIT_NOTE') || (!in_array($paymentMethod, PAYMENT_METHODS) && !in_array($paymentMethod, SUPPLIER_PAYMENT_METHODS)) || !$_SESSION['USER']){
        header('Location: ../single_invoice_payments.php?customer_id=' .$customerID . '&invoice_id=' . $invoiceID);
        die();
    }

    $currentUser = $_SESSION['USER'];
    $x = "DELETE FROM customer_outstanding_cache WHERE customer_id = ?";
    $y = prepareExecuteQuery($x,'i',[$customerID]);

    /** @var InvoicePayment $invoicePayment */
    $invoicePayment = new InvoicePayment();
    $newPayment = true;
    if (!empty($paymentID)){
        $newPayment = false;
        $invoicePayment = InvoicePayment::find($paymentID);
    }
    else {
        if($paymentMethod == 'CREDIT_NOTE'){
            $amount = 0;
        }
    }
    $invoicePayment->invoice_id = $invoiceID;
    $invoicePayment->payment_method = $paymentMethod;
    $invoicePayment->amount = $amount;
    $invoicePayment->meta_data = $metaData;
    $invoicePayment->payment_recorded_by = $currentUser;
    $invoicePayment->save();

    foreach($input['price'] as $i=>$price){
        /** @var CreditNoteItem $creditNoteItem */
        $creditNoteItem = new CreditNoteItem();
        if (!$newPayment){
            $creditNoteItem = CreditNoteItem::find($input['credit_id'][$i]);
        }
        $creditNoteItem->payment_id = $invoicePayment->id;
        $creditNoteItem->product_id = $input['product_id'][$i];
        $creditNoteItem->quantity = $input['quantity'][$i];
        $creditNoteItem->price = $input['price'][$i];
        $creditNoteItem->description = $input['description'][$i];
        $creditNoteItem->save();
    }
    if(request()->input('delete_ids') != null){

        $DELETE_IDS = request()->input('delete_ids');
        $DELETE_IDS = rtrim($DELETE_IDS, ',');
        $creditsToDelete = explode(',', $DELETE_IDS);
        $creditsToDelete = CreditNoteItem::whereIn('id', $creditsToDelete)->get();
        loggedDataChange("credit_note_items_deleted", $invoiceID, "Credit Note Items Deleted: ".$DELETE_IDS);
        foreach($creditsToDelete as $credit){
            $credit->deleted = 1;
            $credit->save();
        }

    }
    header('Location: ../single_invoice_payments.php?customer_id=' .$customerID . '&invoice_id=' . $invoiceID);

?>

