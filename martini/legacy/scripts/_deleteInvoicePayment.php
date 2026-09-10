<?php
	require(__DIR__.'/../functions.php');

    if(request()->input('invoice_id') != ''){
        $payment_id = request()->input('invoice_id');
        $pickSheet_id = prepareExecuteQuery("SELECT invoice_id FROM `invoice_payments` WHERE id=? LIMIT 1",'i',[$payment_id])->fetch_assoc()['invoice_id'];
        loggedDataChange("invoice_payment_deleted", $pickSheet_id, "Invoice Payment Deleted: ".$payment_id);
        prepareExecuteQuery("UPDATE `invoice_payments` SET `deleted`=1 WHERE id=? LIMIT 1",'i',[$payment_id]);
        prepareExecuteQuery("UPDATE `credit_note_items` SET `deleted`=1 WHERE payment_id=?",'i',[$payment_id]);
    }

    header('Location: ' . request()->input('return_url'));

?>
