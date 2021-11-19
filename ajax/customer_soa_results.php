<?php
   	require('../functions.php');    
    
    $customer_id = $_POST['customer_id'];

    $customerPicksheets = mysqli_query($conn, "SELECT pickerSheets.id, pickerSheets.customer_id, pickerSheets.date, pickerSheets.estimated_delivery_date, SUM(invoice_payments.amount) as paid FROM `pickerSheets` left join invoice_payments on invoice_payments.payment_method != 'CREDIT_NOTE' && pickerSheets.id = invoice_payments.invoice_id WHERE (pickerSheets.completed = 1 AND pickerSheets.customer_id=$customer_id) GROUP by pickerSheets.id");

    $ret = [];
    while($picksheet = mysqli_fetch_assoc($customerPicksheets)){
        $picksheet['credit'] = (float) round(totalValueCreditedOnInvoiceID($picksheet['id']),2,PHP_ROUND_HALF_DOWN);
        $picksheet['price'] = (float) round(invoiceTotal($picksheet['id']),2,PHP_ROUND_HALF_DOWN);

        $picksheet['date'] = str_replace('/', '-', $picksheet['date']);
        $picksheet['date'] = date('d/m/Y', strtotime($picksheet['date']));

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

        $estimated_delivery_date = strtotime(str_replace('/', '-', $picksheet['estimated_delivery_date']));
        $picksheet['sortableDueDateFormat'] = date('d-m-Y',$estimated_delivery_date);
        $ret[] = $picksheet;

    }
    echo json_encode($ret);
?>
