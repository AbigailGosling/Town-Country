<?php
	require('../functions.php');

    if($_POST['invoice_id'] != ''){
        $invoice_id = mysqli_real_escape_string($conn, $_POST['invoice_id']);

        mysqli_query($conn, "DELETE from `invoice_payments` WHERE id=$invoice_id LIMIT 1");
    }

    header('Location: ' . $_POST['return_url']);

?>