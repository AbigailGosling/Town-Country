<?php
    require('../functions.php');
    $customerPicksheets = json_decode($_POST['picksheet'],true);
    $customer_id = $_POST['customer_id'];
    $due_days = $_POST['duedays'];
    $showAll = ($_POST['showAll'] == 1);
    $picksheet = null;
    for ($i = 0; $i < count($customerPicksheets);$i++) {
        $picksheet = $customerPicksheets[$i];
        if(!$showAll && $picksheet['outstanding'] == 0) continue;
    ?>
    <tr class="<?php if($i%2 == 0){ echo 'odd'; }else{ echo 'even'; } ?>">  
        <td data-order="<?php echo $picksheet['id']; ?>"><?php echo $picksheet['id']; ?>
            <?php
                
                
                if(!$picksheet['hasCreditNote']){
                    if($picksheet['hasReturns']){
                        ?><div class="soa_cr_label">CR</div><?php
                    }
                }
            
            ?> 
        </td>
            <?php
            $sortableDateFormat = date('d-m-Y',$date);
            //Calculate the Due Date
            ?>
        <td data-sort="<?php echo $picksheet['sortableDueDateFormat']; ?>" width="100"><?php echo $picksheet['date']; ?></td>
        <td data-sort="<?php echo $due_days ?>" width="100"<?php
        if (date('Ymd',strtotime(str_replace('/','-', $picksheet['date']) . "+ " . $due_days . "days")) < date('Ymd')) {
            echo " style='background-color:red;color:white;'>Overdue: ";
        }
        else {
            echo ">";
        }
        ?><?php echo date('d/m/Y',strtotime(str_replace('/','-', $picksheet['date']) . "+ " . $due_days . "days"));?></td>
        <td align="right" width="100" class="digit_value" value="<?php echo number_format($picksheet['price'],2,".",""); ?>"><?php if($picksheet['price'] != 0) { echo '£' . number_format($picksheet['price'],2,".",","); } ?></td>
        <td align="right" width="100" class="digit_paid" value="<?php echo number_format($picksheet['paid'],2,".",""); ?>"><?php if($picksheet['paid'] != 0){ echo '£' . number_format($picksheet['paid'], 2, ".", ","); } ?></td>
        <td align="right" style="color:red;" class="digit_credit" value="<?php echo number_format(totalValueCreditedOnInvoiceID($picksheet['id']), 2, ".", ""); ?>"><?php if(totalValueCreditedOnInvoiceID($picksheet['id'])){ echo '£' . number_format(totalValueCreditedOnInvoiceID($picksheet['id']), 2, ".", ","); }?></td>
        <td align="right" width="100" class="digit_outstanding" value="<?php echo number_format($picksheet['outstanding'], 2, ".", ""); ?>" <?php if($picksheet['outstanding'] < 0) { echo 'style="color:red;"'; } ?> ><?php if(number_format($picksheet['outstanding'], 2, ".", ",") != 0){ echo '£' . number_format($picksheet['outstanding'], 2, ".", ","); } ?></td>
    </tr>
    <?php
    }
    ?>