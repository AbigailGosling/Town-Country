<?php
   	require('../functions.php');    
    
    $customer_id = $_POST['customer_id'];

    $customerPicksheets = mysqli_query($conn, "SELECT pickerSheets.id, pickerSheets.customer_id, pickerSheets.date, pickerSheets.estimated_delivery_date, SUM(invoice_payments.amount) as paid FROM `pickerSheets` left join invoice_payments on invoice_payments.payment_method != 'CREDIT_NOTE' && pickerSheets.id = invoice_payments.invoice_id WHERE (pickerSheets.completed = 1 AND pickerSheets.customer_id=$customer_id) GROUP by pickerSheets.id");

    $count = mysqli_num_rows($customerPicksheets);
    $ret = [];
    while($picksheet = mysqli_fetch_array($customerPicksheets)){
        $picksheet['credit'] = (float) round(totalValueCreditedOnInvoiceID($picksheet['id']),2,PHP_ROUND_HALF_DOWN);
        $picksheet['price'] = (float) round(invoiceTotal($picksheet['id']),2,PHP_ROUND_HALF_DOWN);

        $picksheet['date'] = str_replace('/', '-', $picksheet['date']);
        $picksheet['date'] = date('d/m/Y', strtotime($date));

	    $picksheet['paid'] = (float) round($picksheet['paid'],2,PHP_ROUND_HALF_DOWN);
        $picksheet['invoicePaid'] = false;
        $epsilon = 0.00001;

        if(($this_price - $picksheet['paid']) <= $epsilon){
            $picksheet['invoicePaid'] = true;
        }
	        
	    $picksheet['outstanding'] = (float) $this_price - $picksheet['paid'] - $total_credit;
        $picksheet['credited'] = totalValueCreditedOnInvoiceID($picksheet['id']);
        $picksheet['hasReturns'] = doesInvoiceHaveReturns($picksheet['id']);
        $picksheet['hasCreditNote'] = doesInvoiceHaveCreditNote($picksheet['id']);
        $ret[] = $picksheet;
    }
    echo json_encode($ret);
?>
