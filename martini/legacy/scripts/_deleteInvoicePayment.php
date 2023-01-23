<?php
	require(__DIR__.'/../functions.php');

    if(request('invoice_id') != ''){
        $payment_id = $mysqli->real_escape_string( request('invoice_id'));

        prepareExecuteQuery("DELETE from `invoice_payments` WHERE id=? LIMIT 1",'i',[$payment_id]);
        prepareExecuteQuery("DELETE from `credit_note_items` WHERE payment_id=?",'i',[$payment_id]);
    }

    header('Location: ' . request('return_url'));

?>