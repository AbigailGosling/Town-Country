<?php
   	require('../functions.php');

    $limit = 50;
    $toSkip = $_POST['toSkip'] ?? 0;

    $customer_id = $_POST['customer_id'];

    if($_POST['date_from'] != '' && $_POST['date_to'] != ''){

        $date_from = $_POST['date_from'];
        $date_to = $_POST['date_to'];
        
        $customerPicksheets = mysqli_query($conn, "SELECT pickerSheets.id, pickerSheets.customer_id, pickerSheets.date, pickerSheets.estimated_delivery_date, SUM(invoice_payments.amount) as paid FROM `pickerSheets` left join invoice_payments on invoice_payments.payment_method != 'CREDIT_NOTE' && pickerSheets.id = invoice_payments.invoice_id WHERE (pickerSheets.completed = 1 AND pickerSheets.customer_id=$customer_id AND pickerSheets.date BETWEEN '$date_from' AND '$date_to') GROUP by pickerSheets.id ORDER BY pickerSheets.id ASC LIMIT $toSkip, $limit");
    }else{
        $customerPicksheets = mysqli_query($conn, "SELECT pickerSheets.id, pickerSheets.customer_id, pickerSheets.date, pickerSheets.estimated_delivery_date, SUM(invoice_payments.amount) as paid FROM `pickerSheets` left join invoice_payments on invoice_payments.payment_method != 'CREDIT_NOTE' && pickerSheets.id = invoice_payments.invoice_id WHERE (pickerSheets.completed = 1 AND pickerSheets.customer_id=$customer_id) GROUP by pickerSheets.id ORDER BY pickerSheets.id ASC LIMIT $toSkip, $limit");
    }
    
    $totalPrice = 0.00;
    $totalPaid = 0.00;
    $totalCredited = 0.00;
    $totalOutstanding = 0.00;

    $i = 0;
    while($picksheet = mysqli_fetch_array($customerPicksheets)){
        $total_credit = totalValueCreditedOnInvoiceID($picksheet['id']);
        $this_price = (float) invoiceTotal($picksheet['id']);
        $totalPrice += $this_price;

        $date = str_replace('/', '-', $picksheet['date']);
        $date = date('d/m/Y', strtotime($date));

        $totalPaid += (float) $picksheet['paid'];
        $invoicePaid = false;
        $epsilon = 0.00001;

        if(($this_price - $picksheet['paid']) <= $epsilon){
            $invoicePaid = true;
            $currentOutstanding = (float) $this_price - $picksheet['paid'] - $total_credit;
        }else{
            $currentOutstanding = (float) $this_price - $picksheet['paid'] - $total_credit;
        }
        
        $totalOutstanding += $currentOutstanding;

    ?>
    <tr class="<?php  if($i%2 == 0){ echo 'odd'; }else{ echo 'even'; } ?>">
        <td data-order="<?php echo $picksheet['id']; ?>"><a href="/invoice.php?id=<?php echo $picksheet['id']; ?>"><?php echo $picksheet['id']; ?></a>
            <?php
                $hasReturns = doesInvoiceHaveReturns($picksheet['id']);
                $hasCreditNote = doesInvoiceHaveCreditNote($picksheet['id']);
                
                if(!$hasCreditNote){
                    if($hasReturns){
                        ?><div class="soa_cr_label">CR</div><?php
                    }
                }
            
            ?> 
        </td>
        <?php if(!$invoicePaid) { ?>
            <td><a href="/single_invoice_payments.php?customer_id=<?php echo $_GET['id']; ?>&invoice_id=<?php echo $picksheet['id']; ?>">Make / View payments</a></td>
        <?php }else{ ?>
            <td><a href="/single_invoice_payments.php?customer_id=<?php echo $_GET['id']; ?>&invoice_id=<?php echo $picksheet['id']; ?>">Invoice Paid</a></td>
        <?php }?>

        <?php
            $estimated_delivery_date = strtotime($picksheet['estimated_delivery_date']);
            $sortableDueDateFormat = date('d-m-Y',$estimated_delivery_date);
        ?>
        <td data-sort="<?php echo $sortableDueDateFormat; ?>">
            <?php
                echo $picksheet['estimated_delivery_date'];

                if (strtotime($picksheet['estimated_delivery_date']) < time()) {
                    ?><div class="overdue" style="display:inline-block;background:red;border-radius:20px;height:20px;width:20px;color:#fff;text-align:center;font-weight:bold;line-height:20px;">!</div><?php
                }
            ?>
            </td>

        
            <?php
            $sortableDateFormat = date('d-m-Y',$date);

            $totalCredited += totalValueCreditedOnInvoiceID($picksheet['id']);
        ?>
        <td data-sort="<?php echo $sortableDateFormat; ?>" width="100"><?php echo $date; ?></td>
        <td align="right" width="100" class="digit_value" value="<?php echo number_format($this_price,2,".",""); ?>"><?php if($this_price != 0) { echo '£' . number_format($this_price,2,".",","); } ?></td>
        <td align="right" width="100" class="digit_paid" value="<?php echo number_format($picksheet['paid'],2,".",""); ?>"><?php if($picksheet['paid'] != 0){ echo '£' . number_format($picksheet['paid'], 2, ".", ","); } ?></td>
        <td align="right" style="color:red;" class="digit_credit" value="<?php echo number_format(totalValueCreditedOnInvoiceID($picksheet['id']), 2, ".", ""); ?>"><?php if(totalValueCreditedOnInvoiceID($picksheet['id'])){ echo '£' . number_format(totalValueCreditedOnInvoiceID($picksheet['id']), 2, ".", ","); }?></td>
        <td align="right" width="100" class="digit_outstanding" value="<?php echo number_format($currentOutstanding, 2, ".", ""); ?>" <?php if($currentOutstanding < 0) { echo 'style="color:red;"'; } ?> ><?php if(number_format($currentOutstanding) != 0){ echo '£' . number_format($currentOutstanding, 2, ".", ","); } ?></td>
    </tr>
    <?php
        $i++;
    }
    ?>