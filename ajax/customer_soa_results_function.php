<?php
function get_customer_soa_results($customer_id,$adv)
{
    global $conn;
    $customerPicksheets = mysqli_query($conn, "SELECT pickerSheets.id, pickerSheets.customer_id, pickerSheets.date, pickerSheets.estimated_delivery_date, SUM(invoice_payments.amount) as paid FROM `pickerSheets` left join invoice_payments on invoice_payments.payment_method != 'CREDIT_NOTE' && pickerSheets.id = invoice_payments.invoice_id WHERE (pickerSheets.completed = 1 AND pickerSheets.customer_id=$customer_id) GROUP by pickerSheets.id ORDER BY pickerSheets.id DESC");

    $ret = [];
    while($picksheet = mysqli_fetch_assoc($customerPicksheets)){
        $picksheet['credit'] = (float) round(totalValueCreditedOnInvoiceID($picksheet['id']),2,PHP_ROUND_HALF_DOWN);
        $picksheet['price'] = (float) round(invoiceTotal($picksheet['id']),2,PHP_ROUND_HALF_DOWN);

        $picksheet['date'] = str_replace('/', '-', $picksheet['date']);
        $picksheet['date'] = date('d/m/Y', strtotime($picksheet['date']));

	    $picksheet['paid'] = (float) round($picksheet['paid'],2,PHP_ROUND_HALF_DOWN);
        $picksheet['invoicePaid'] = false;
        $epsilon = 0.00001;

        if(($picksheet['price'] - $picksheet['paid']) <= $epsilon){
            $picksheet['invoicePaid'] = true;
        }
	        
	    $picksheet['outstanding'] = (float) $picksheet['price'] - $picksheet['paid'] - $picksheet['credit'];
        if ($picksheet['outstanding'] == 0 && $adv != true) continue;
        $picksheet['credited'] = totalValueCreditedOnInvoiceID($picksheet['id']);
        $picksheet['hasReturns'] = doesInvoiceHaveReturns($picksheet['id']);
        $picksheet['hasCreditNote'] = doesInvoiceHaveCreditNote($picksheet['id']);

        $estimated_delivery_date = strtotime(str_replace('/', '-', $picksheet['estimated_delivery_date']));
        $picksheet['sortableDueDateFormat'] = date('d-m-Y',$estimated_delivery_date);
        $ret[] = $picksheet;

    }
    return $ret;
}
function check_customer_outstanding_cache($customer_id)
{
    global $conn;

    $cacheRow = mysqli_query($conn, "SELECT * FROM customer_outstanding_cache WHERE customer_id = $customer_id");
    $cacheRow = mysqli_fetch_assoc($cacheRow);
    if ($cacheRow == null) $cacheRow = array('customer_id' => $customer_id,'newRow' => true);
    else $cacheRow['newRow'] = false;

    $cacheRow['outdated'] = false;

    $check = mysqli_query($conn, "SELECT MAX(id) as max_id,GROUP_CONCAT(id) as list FROM pickerSheets WHERE customer_id = $customer_id");
    $check = mysqli_fetch_assoc($check);
    $invoiceList = $check['list'];
    $check = $check['max_id'];
    
    if (array_key_exists('pickersheet_id',$cacheRow) == false || $check != $cacheRow['pickersheet_id']) 
    {
        $cacheRow['pickersheet_id'] = $check;
        $cacheRow['outdated'] = true;
    }

    $check = mysqli_query($conn, "SELECT MAX(id) as id FROM invoice_payments WHERE invoice_id IN ($invoiceList)");
    $check = mysqli_fetch_assoc($check)['id'];
    
    if (array_key_exists('invoice_payment_id',$cacheRow) == false || $check != $cacheRow['invoice_payment_id']) 
    {
        $cacheRow['invoice_payment_id'] = $check;
        $cacheRow['outdated'] = true;
    }

    return $cacheRow;
}
function update_customer_outstanding_cache($cacheRow)
{
    global $conn;
    
    if ($cacheRow['newRow'] == true)
    {
        $sql = "INSERT INTO `tandc_live`.`customer_outstanding_cache` (`customer_id`, `pickersheet_id`, `invoice_payment_id`, `outstanding`) VALUES (".$cacheRow['customer_id'].",".$cacheRow['pickersheet_id'].",".$cacheRow['invoice_payment_id'].",'".(float)$cacheRow['outstanding']."')";
    }
    else
    {
        $sql = "UPDATE `tandc_live`.`customer_outstanding_cache` SET `pickersheet_id` = ".$cacheRow['pickersheet_id'].", `invoice_payment_id` = ".$cacheRow['invoice_payment_id'].", `outstanding` = '".(float)$cacheRow['outstanding']."' WHERE `customer_outstanding_cache`.`customer_id` = ".$cacheRow['customer_id'];
    }
    $x = mysqli_query($conn, $sql);
}
?>