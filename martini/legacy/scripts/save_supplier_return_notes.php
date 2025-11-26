<?php

use App\Models\SupplierReturn;

	require(__DIR__.'/../functions.php');
    $supplier_return_id = request()->input('supplier_return_id');
    $reference_number = request()->input('reference_number');
    $comments = request()->input('comments');

    $supplierReturn = SupplierReturn::find($supplier_return_id);
    $supplierReturn->reference_number = $reference_number;
    $supplierReturn->comments = $comments;
    $supplierReturn->save();

    header('Location: ../single_invoice_payments.php?return=y&customer_id=' .$customerID . '&invoice_id=' . $invoiceID);

?>

