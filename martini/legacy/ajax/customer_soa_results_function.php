<?php
function get_customer_soa_results($customer_id,$adv)
{
    $customerPicksheets = prepareExecuteQuery("SELECT pickerSheets.id, pickerSheets.customer_id, pickerSheets.date, pickerSheets.estimated_delivery_date, SUM(invoice_payments.amount) as paid FROM `pickerSheets` left join invoice_payments on invoice_payments.payment_method != 'CREDIT_NOTE' && pickerSheets.id = invoice_payments.invoice_id WHERE (pickerSheets.completed = 1 AND pickerSheets.customer_id=?) GROUP by pickerSheets.id ORDER BY pickerSheets.id DESC",'i',[$customer_id]);
    $ret = [];
    while($picksheet = mysqli_fetch_assoc($customerPicksheets)){

        $picksheet['credit'] = (double) round(totalValueCreditedOnInvoiceID($picksheet['id']),2,PHP_ROUND_HALF_DOWN);
        $picksheet['price'] = (double) round(invoiceTotal($picksheet['id']),2,PHP_ROUND_HALF_DOWN);

        $picksheet['date'] = str_replace('/', '-', $picksheet['estimated_delivery_date']);
        $picksheet['datetime'] = strtotime($picksheet['date']);
        $picksheet['date'] = date('d/m/Y', $picksheet['datetime']);

	    $picksheet['paid'] = (double) round($picksheet['paid'],2,PHP_ROUND_HALF_DOWN);
        $picksheet['invoicePaid'] = false;
        $epsilon = 0.00001;

        if(($picksheet['price'] - $picksheet['paid']) <= $epsilon){
            $picksheet['invoicePaid'] = true;
        }
	    $picksheet['outstanding'] = round((double) $picksheet['price'] - $picksheet['paid'] - $picksheet['credit'],2,PHP_ROUND_HALF_DOWN);
        if ($adv == true && ($picksheet['outstanding'] > -0.02 && $picksheet['outstanding'] < 0.02)) continue;
        $picksheet['credited'] = totalValueCreditedOnInvoiceID($picksheet['id']);
        $picksheet['hasReturns'] = doesInvoiceHaveReturns($picksheet['id']);
        $picksheet['creditNotes'] = getInvoiceCreditNotes($picksheet['id']);
        $picksheet['hasCreditNote'] = doesInvoiceHaveCreditNote($picksheet['id']);

        $estimated_delivery_date = strtotime(str_replace('/', '-', $picksheet['estimated_delivery_date']));
        $picksheet['sortableDueDateFormat'] = date('d-m-Y',$estimated_delivery_date);
        $ret[] = $picksheet;

    }
    if ($adv)usort($ret,'date_compare');
    return $ret;
}
function date_compare($element1, $element2) {
    $datetime1 = $element1['datetime'];
    $datetime2 = $element2['datetime'];
    return $datetime2 - $datetime1;
} 
function check_customer_outstanding_cache($customer_id,$forceReload = false)
{
    $cacheRow = prepareExecuteQuery("SELECT SQL_NO_CACHE *,NOW() FROM customer_outstanding_cache WHERE customer_id = ?",'i',[$customer_id]);
    $cacheRow = mysqli_fetch_assoc($cacheRow);
    if ($cacheRow == null || $forceReload == true) 
    {
        $cacheRow = array('customer_id' => $customer_id,'newRow' => true);
        $oldest = -1;
        $lastpayment = -1;
        $cacheRow['outdated'] = true;
    }
    else 
    {
        $cacheRow['newRow'] = false;
        $oldest = $cacheRow['oldest_unpaid_id'];
        $lastpayment = $cacheRow['invoice_payment_id'];
        
        $cacheRow['outdated'] = false;
    }
    
    
    $invoiceList = array();
    $check = prepareExecuteQuery("SELECT id FROM pickerSheets WHERE customer_id = ? ORDER BY `pickerSheets`.`id` DESC",'i',[$customer_id]);
    $row = mysqli_fetch_assoc($check);
    if ($row == null)
    {
        $cacheRow['outstanding_old'] = $cacheRow['outstanding'] = $cacheRow['oldest_unpaid_id'] = $cacheRow['pickersheet_id'] = $cacheRow['invoice_payment_id'] = 0;
        $cacheRow['oldest_unpaid_date'] = strtotime('0');
        return $cacheRow;
    }
    $invoiceList[] = $row['id'];
    $highest = $row['id'];
    while($row = mysqli_fetch_assoc($check))
    {
        $invoiceList[] = $row['id'];
    }
    $check = $highest;
    $invoiceList = implode(",",$invoiceList);
    if (array_key_exists('pickersheet_id',$cacheRow) == false || $check != $cacheRow['pickersheet_id']) 
    {
        $cacheRow['pickersheet_id'] = $check;
        $cacheRow['pickersheet_id_outdated'] = $cacheRow['outdated'] = true;
    }
    
    
    $check = prepareExecuteQuery("SELECT MAX(id) as id FROM invoice_payments WHERE invoice_id IN (".$invoiceList.")");
    $check = mysqli_fetch_assoc($check);
    $check = $check['id'];
    if (array_key_exists('invoice_payment_id',$cacheRow) == false || $check != $cacheRow['invoice_payment_id']) 
    {
        $cacheRow['invoice_payment_id'] = $check;
        $cacheRow['invoice_payment_id_outdated'] = $cacheRow['outdated'] = true;
    }
    
    $cacheRow['debug'] = "SELECT pickerSheets.id, pickerSheets.date, invoice_payments.id as payment_id FROM pickerSheets, pickerSheets.estimated_delivery_date LEFT JOIN invoice_payments ON pickerSheets.id = invoice_payments.invoice_id WHERE pickerSheets.customer_id = ".$customer_id." AND (pickerSheets.id >= ".$oldest." OR invoice_payments.id > ".$lastpayment.") ORDER BY `pickerSheets`.`id` ASC";
    $checkQ = prepareExecuteQuery( "SELECT pickerSheets.id, pickerSheets.date, invoice_payments.id as payment_id, pickerSheets.estimated_delivery_date FROM pickerSheets LEFT JOIN invoice_payments ON pickerSheets.id = invoice_payments.invoice_id WHERE pickerSheets.customer_id = ? AND (pickerSheets.id >= ? OR invoice_payments.id > ?) ORDER BY `pickerSheets`.`id` ASC",'iii',[$customer_id,$oldest,$lastpayment]);
    $cacheRow['pending'] = null;
    $lastRow = null;
    while($row = mysqli_fetch_assoc($checkQ))
    {  
        $row['outstanding'] = (double)(getOutstandingPicksheetTotal($row['id']) - totalValueCreditedOnInvoiceID($row['id']));
        if ($row['outstanding'] > 0)
        {
            $cacheRow['pending'] = $row;           
            break;
        }
        $lastRow = $row;
    }   
    if ($cacheRow['pending'] != null)
    {
        $row = $cacheRow['pending'];
        $myDateTime = DateTime::createFromFormat('d/m/Y', $row['estimated_delivery_date']);
        $newDateString = $myDateTime->format('Y-m-d');
        if ($row['outstanding'] > 0) $cacheRow['oldest_unpaid_date'] = strtotime($newDateString);
        else $cacheRow['oldest_unpaid_date'] = strtotime("now");
        
        if (array_key_exists('oldest_unpaid_id',$cacheRow) == false || $row['id'] != $cacheRow['oldest_unpaid_id']) 
        {      
            $cacheRow['oldest_unpaid_id'] = $row['id'];
            $cacheRow['outdated'] = true;
        }
    }
    else 
    {
        $cacheRow['oldest_unpaid_date'] = strtotime("now");
        if ($lastRow != null && $cacheRow['oldest_unpaid_id'] != $lastRow['id'])
        {
            $cacheRow['oldest_unpaid_id'] = $lastRow['id'];
            $cacheRow['outdated'] = true;
        }
    }
    if ($cacheRow['outdated'] == true)
    {
        $cacheRow['outstanding_old'] = $cacheRow['outstanding'];
        $outstanding = 0;      
        $cacheRow['picksheets'] = $qq = get_customer_soa_results($customer_id,false);
        foreach ($qq as $pick)
        {
            $outstanding = $outstanding + $pick['outstanding'];
        }
        $cacheRow['outstanding'] = $outstanding;
        update_customer_outstanding_cache($customer_id,$cacheRow);
    }
    return $cacheRow;
}
function update_customer_outstanding_cache($customer_id,$cacheRow)
{
    global $mysqli;
    $cacheRow2 = prepareExecuteQuery("SELECT customer_id FROM customer_outstanding_cache WHERE customer_id = ?",'i',[$customer_id]);
    $cacheRow2 = mysqli_fetch_assoc($cacheRow2);
    if ($cacheRow2 == null)
    {
        $sql = "INSERT INTO customer_outstanding_cache (`customer_id`, `pickersheet_id`, `invoice_payment_id`, `oldest_unpaid_id`, `outstanding`) VALUES (".$cacheRow['customer_id'].",".$cacheRow['pickersheet_id'].",'".$cacheRow['invoice_payment_id']."','".$cacheRow['oldest_unpaid_id']."','".(double)$cacheRow['outstanding']."')";
    }
    else
    {
        $sql = "UPDATE customer_outstanding_cache SET `pickersheet_id` = ".$cacheRow['pickersheet_id'].", `invoice_payment_id` = ".$cacheRow['invoice_payment_id'].", `outstanding` = '".(double)$cacheRow['outstanding']."', `oldest_unpaid_id` = '".$cacheRow['oldest_unpaid_id']."' WHERE `customer_outstanding_cache`.`customer_id` = ".$cacheRow['customer_id'];
    }
    $x = prepareExecuteQuery($sql);
}
function precredit_check($customer_id)
{
    $customerQ = prepareExecuteQuery("SELECT * FROM `customers` WHERE `id` = ?",'i',[$customer_id]);
    $custR = mysqli_fetch_assoc($customerQ);
    $returningObj['showWarning'] = false;
    $returningObj['saleAllowed'] = true;
    $returningObj['creditCheckRender'] = true;
    if ($custR['override'] == 1 || $custR['credit_enabled'] == 0)
    {
        $returningObj['infoMessage'] = "<td align='center' style='height:100%;padding-top:15px;padding-bottom:15px;'>Credit Check Disabled</td>";
        $returningObj['creditCheckRender'] = false;
        return $returningObj;
    }
    else if ($custR['credit_terms'] < 0)
    {
        $returningObj['saleAllowed'] = false;
        $returningObj['creditCheckRender'] = false;
        $returningObj['message'] = "Customer has been suspended, contact administrator";
    }
    else
    {
        $gracePeriod =  strtotime("-".$custR['credit_grace']." days");
        $beyondDate = strtotime("-".$custR['credit_terms']." days");
        $closeToOverdue = strtotime("-".$custR['due_warning']." days");
        $returningObj['details'] = $details = check_customer_outstanding_cache($customer_id);
        if ($details['outdated'] == true) update_customer_outstanding_cache($customer_id,$details);
        $returningObj['oldest'] = $oldest = $details['oldest_unpaid_date'];
        $outstanding = $details['outstanding'];
        $returningObj['beyondDate'] = $beyondDate;
        $returningObj['gracePeriod'] = $gracePeriod;
        $returningObj['closeToOverdue'] = $closeToOverdue;

        $returningObj['creditRating'] = $custR['credit_rating'];

        $returningObj['hideOnStmt'] = false;

        $calDayRem1 = $oldest -$beyondDate;
        $calDayRem = intdiv($calDayRem1,86400);
        if ($calDayRem < 0)
        {
            $calDayRem = $calDayRem * -1;
            $returningObj['infoMessage'] = "<td align='center' style='height:100%;padding-top:12px;padding-bottom:12px;' bgcolor='lightgrey'>Overdue day(s): $calDayRem</td>";
        }
        else
        {
            $returningObj['infoMessage'] = "<td align='center' style='height:100%;padding-top:12px;padding-bottom:12px;' bgcolor='lightgrey'>Remaining day(s): $calDayRem</td>";
        }
        $calCredRem = $custR['credit_rating'] - $outstanding;
        if ($calCredRem < 0)
        {
            $calCredRem = $calCredRem * -1;
            $returningObj['infoMessage'] .= "<td align='right'>Over Credit: £$calCredRem</td>";
        }
        else
        {
            $returningObj['infoMessage'] .= "<td align='right'>Remaining Credit: £$calCredRem</td>";
        }

        if ($oldest != "" && $oldest < $gracePeriod)
        {
            $returningObj['saleAllowed'] = false;
            $returningObj['message'] = "Customer has invoice(s) long overdue, contact administration";
            $returningObj['messageLong'] = "Invoice overdue: ".$returningObj['details']['pending']['id'];
        } 
        else if ($outstanding > $custR['credit_rating']) 
        {
            $returningObj['saleAllowed'] = false;
            $returningObj['message'] = "Customer is over credit limit, contact administration";
            $returningObj['messageLong'] = "Customer is over credit limit, contact administration";
            $returningObj['overcredit'] = true;
        }
        else if ($oldest != "" && $oldest < $beyondDate) 
        {
            $returningObj['showHigherWarning'] = true;
            $returningObj['showWarning'] = true;
            $returningObj['message'] = "Customer has invoice(s) overdue, contact administration";
            $returningObj['messageLong'] = "Invoice overdue: ".$returningObj['details']['pending']['id'];
        }
        else if ($oldest != "" && $oldest < $closeToOverdue)
        {
            $returningObj['hideOnStmt'] = true;
            $returningObj['showWarning'] = true;
            $returningObj['message'] = "Customer has invoice due soon (Delivery note may not be printable if over rating when picked)";
            $returningObj['messageLong'] = "Customer has invoice due soon (Delivery note may not be printable if over rating when picked)";
        }
        
        else if ($outstanding > $custR['flaguplimit']) 
        {
            $returningObj['showWarning'] = true;
            $returningObj['message'] = "Close to Credit Limit (Delivery note may not be printable if over rating when picked)";
            $returningObj['messageLong'] = "Close to Credit Limit (Delivery note may not be printable if over rating when picked)";
        }

    }
    return $returningObj;
}
?>