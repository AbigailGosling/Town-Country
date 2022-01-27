<?php
    require('../functions.php');
    $customerPicksheets = json_decode($_POST['picksheet'],true);
    $customer_id = $_POST['customer_id'];
    $due_days = $_POST['duedays'];
    $showAll = ($_POST['showAll'] == "Y");
    $picksheet = null;
    //Check if due days is a string if so strip out the numbers
    for ($i = 0; $i < count($customerPicksheets);$i++) {
        $picksheet = $customerPicksheets[$i];
        //if(!$showAll && ($picksheet['outstanding'] > -0.02 && $picksheet['outstanding'] < 0.02)) continue;
    ?>
    <tr class="<?php if($i%2 == 0){ echo 'odd'; }else{ echo 'even'; } ?>">  
        <td <?php if ($i != 0) { ?>style="border-top:1px solid lightgray"<?php } ?> data-order="<?php echo $picksheet['id']; ?>"><?php echo "IN: ".$picksheet['id']; ?>
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
        <td <?php if ($i != 0) { ?>style="border-top:1px solid lightgray"<?php } ?> data-sort="<?php echo $picksheet['sortableDueDateFormat']; ?>" width="100"><?php echo $picksheet['date']; ?></td>
        <td data-sort="<?php echo $due_days ?>" width="100"<?php
        echo "style='";
        if ($i != 0) 
        { 
            echo "border-top:1px solid lightgray;";
        }
        if (date('Ymd',strtotime(str_replace('/','-', $picksheet['date']) . "+ " . $due_days . "days")) < date('Ymd')) {
            echo "background-color:red;color:white;'>Overdue: ";
        }
        else {
            echo "'>";
        }
        ?><?php echo date('d/m/Y',strtotime(str_replace('/','-', $picksheet['date']) . "+ " . $due_days . "days"));?></td>
        <td <?php if ($i != 0) { ?>style="border-top:1px solid lightgray"<?php } ?> align="right" width="100" class="digit_value" value="<?php echo number_format($picksheet['price'],2,".",""); ?>"><?php if($picksheet['price'] != 0) { echo '£' . number_format($picksheet['price'],2,".",","); } ?></td>
        <td <?php if ($i != 0) { ?>style="border-top:1px solid lightgray"<?php } ?> align="right" width="100" class="digit_paid" value="<?php echo number_format($picksheet['paid'],2,".",""); ?>"><?php if($picksheet['paid'] != 0){ echo '£' . number_format($picksheet['paid'], 2, ".", ","); } ?></td>
        <td <?php if ($i != 0) { ?>style="border-top:1px solid lightgray; color: red"<?php } ?> align="right" value=""></td>
        <td <?php if ($i != 0) { ?>style="border-top:1px solid lightgray;<?php if($picksheet['outstanding'] < 0) { echo ' color:red;'; } ?>"<?php } if (count($picksheet['creditNotes']) == 0) {?>align="right" width="100" class="digit_outstanding" value="<?php echo number_format($picksheet['outstanding'], 2, ".", ""); ?>"><?php if(number_format($picksheet['outstanding'], 2, ".", ",") != 0){ echo '£' . number_format($picksheet['outstanding'], 2, ".", ","); } }?></td>
    </tr>
    <?php
        if($picksheet['hasCreditNote'])
        {
            for ($j = 0; $j < count($picksheet['creditNotes']);$j++)
            {
                $creditnote = $picksheet['creditNotes'][$j]
                ?>
    <tr>  
        <td data-order="<?php echo $creditnote['id']; ?>"><?php if ($j == count($picksheet['creditNotes']) -1) {echo "╚═";} else {echo "╠═";} echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;CN: ". $creditnote['id']; ?>
        </td>
        <td data-sort=""></td>
        <td data-sort=""></td>
        <td align="right" width="100" value=""></td>
        <td align="right" width="100" value=""></td>
        <td align="right" style="color:red;" class="digit_credit" value="<?php echo number_format(creditNoteTotal($creditnote['id']), 2, ".", ""); ?>"><?php if(creditNoteTotal($creditnote['id'])){ echo '£' . number_format(creditNoteTotal($creditnote['id']), 2, ".", ","); }?></td>
        <td align="right" width="100" class="digit_outstanding" value="<?php if ($j == count($picksheet['creditNotes']) -1) echo number_format($picksheet['outstanding'], 2, ".", ""); else echo 0;?>" <?php if($picksheet['outstanding'] < 0) { echo 'style="color:red;"'; } ?> ><?php if($j == count($picksheet['creditNotes']) -1 && number_format($picksheet['outstanding'], 2, ".", ",") != 0){ echo '£' . number_format($picksheet['outstanding'], 2, ".", ","); } ?></td>
    </tr>
                <?php
            }
            
        }
    }
    ?>