<?php
    require('../functions.php');   
    die();
    $i = $_POST['count'];
    $picksheet = json_decode($_POST['picksheet']);
?>
<tr class="<?php  if($i%2 == 0){ echo 'odd'; }else{ echo 'even'; } ?>" <?php  if($currentOutstanding == 0){ echo 'style="display: none;"'; } ?>>
        <td data-order="<?php echo $picksheet['id']; ?>"><a href="/invoice.php?id=<?php echo $picksheet['id']; ?>"><?php echo $picksheet['id']; ?></a>
            <?php
                
                
                if(!$hasCreditNote){
                    if($hasReturns){
                        ?><div class="soa_cr_label">CR</div><?php
                    }
                }
            
            ?> 
        </td>
        <?php if(!$invoicePaid) { ?>
            <td><a href="/single_invoice_payments.php?customer_id=<?php echo $customer_id; ?>&invoice_id=<?php echo $picksheet['id']; ?>">Make / View payments</a></td>
        <?php }else{ ?>
            <td><a href="/single_invoice_payments.php?customer_id=<?php echo $customer_id; ?>&invoice_id=<?php echo $picksheet['id']; ?>">Invoice Paid</a></td>
        <?php }?>

        <?php
            $estimated_delivery_date = strtotime($picksheet['estimated_delivery_date']);
            $sortableDueDateFormat = date('d-m-Y',$estimated_delivery_date);
        ?>
        <td data-sort="<?php echo $sortableDueDateFormat; ?>">
            <?php
                echo $picksheet['estimated_delivery_date'];

                if (strtotime($picksheet['estimated_delivery_date']) < time()) { 
                    echo '<div class="overdue" style="display:inline-block;background:red;border-radius:20px;height:20px;width:20px;color:#fff;text-align:center;font-weight:bold;line-height:20px;">!</div>';
                    
                }
            ?>
            </td>

        
            <?php
            $sortableDateFormat = date('d-m-Y',$date);
        ?>
        <td data-sort="<?php echo $sortableDateFormat; ?>" width="100"><?php echo $date; ?></td>
        <td align="right" width="100" class="digit_value" value="<?php echo number_format($this_price,2,".",""); ?>"><?php if($this_price != 0) { echo '£' . number_format($this_price,2,".",","); } ?></td>
        <td align="right" width="100" class="digit_paid" value="<?php echo number_format($picksheet['paid'],2,".",""); ?>"><?php if($picksheet['paid'] != 0){ echo '£' . number_format($picksheet['paid'], 2, ".", ","); } ?></td>
        <td align="right" style="color:red;" class="digit_credit" value="<?php echo number_format(totalValueCreditedOnInvoiceID($picksheet['id']), 2, ".", ""); ?>"><?php if(totalValueCreditedOnInvoiceID($picksheet['id'])){ echo '£' . number_format(totalValueCreditedOnInvoiceID($picksheet['id']), 2, ".", ","); }?></td>
        <td align="right" width="100" class="digit_outstanding" value="<?php echo number_format($currentOutstanding, 2, ".", ""); ?>" <?php if($currentOutstanding < 0) { echo 'style="color:red;"'; } ?> ><?php if(number_format($currentOutstanding, 2, ".", ",") != 0){ echo '£' . number_format($currentOutstanding, 2, ".", ","); } ?></td>
    </tr>