<?php
	require('../functions.php');

    if($_POST['invoice_id'] != ''){
        $payment_id = mysqli_real_escape_string($conn, $_POST['invoice_id']);

        mysqli_query($conn, "DELETE from `invoice_payments` WHERE id=$payment_id LIMIT 1");
        mysqli_query($conn, "DELETE from `credit_note_items` WHERE payment_id=$payment_id");
    }

    header('Location: ' . $_POST['return_url']);

?>