<?php
	require(__DIR__.'/../functions.php');

    if(request()->input('invoice_id') != ''){
        $payment_id = request()->input('invoice_id');

        prepareExecuteQuery("DELETE from `invoice_payments` WHERE id=? LIMIT 1",'i',[$payment_id]);
        prepareExecuteQuery("DELETE from `credit_note_items` WHERE payment_id=?",'i',[$payment_id]);
    }

    header('Location: ' . request()->input('return_url'));

?>