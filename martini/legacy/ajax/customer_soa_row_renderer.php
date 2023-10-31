<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

    require(__DIR__.'/../functions.php');
    $customerPicksheets = json_decode(request()->input('picksheet'),true);
    $customer_id = request()->input('customer_id');
    $showAll = (request()->input('showAll') == "Y");
    $picksheet = null;
    for ($i = 0; $i < count($customerPicksheets);$i++) {
        $picksheet = $customerPicksheets[$i];
        if(!$showAll && ($picksheet['outstanding'] > -0.02 && $picksheet['outstanding'] < 0.02)) continue;
    ?>
    <tr class="<?php if($i%2 == 0){ echo 'odd'; }else{ echo 'even'; } ?>">  
        <td data-order="<?php echo $picksheet['id']; ?>"><a href="invoice.php?id=<?php echo $picksheet['id']; ?>"><?php echo $picksheet['id']; ?></a>
            <?php
                
                
                if(!$picksheet['hasCreditNote']){
                    if($picksheet['hasReturns']){
                        ?><div class="soa_cr_label">CR</div><?php
                    }
                }
            
            ?> 
        </td>
        <?php 
        $usermodel = User::find(Auth::id());
        if (!$usermodel->hasPermission("restrictedaccess")) { ?>
            <?php if(!$picksheet['invoicePaid']) { ?>
                <td><a href="single_invoice_payments.php?customer_id=<?php echo $customer_id; ?>&invoice_id=<?php echo $picksheet['id']; ?>">Make / View payments</a></td>
            <?php }else{ ?>
                <td><a href="single_invoice_payments.php?customer_id=<?php echo $customer_id; ?>&invoice_id=<?php echo $picksheet['id']; ?>">Invoice Paid</a></td>
            <?php }?>
        <?php }else{ ?>
            <td></td>
        <?php } ?>
        <td data-sort="<?php echo $picksheet['sortableDueDateFormat']; ?>">
            <?php
                echo $picksheet['estimated_delivery_date'];

                /*if (strtotime($picksheet['estimated_delivery_date']) < time()) { 
                    echo '<div class="overdue" style="display:inline-block;background:red;border-radius:20px;height:20px;width:20px;color:#fff;text-align:center;font-weight:bold;line-height:20px;">!</div>';
                    
                }*/
            ?>
            </td>

        
            <?php
            $sortableDateFormat = date('d-m-Y',$date);
        ?>
        <td data-sort="<?php echo $picksheet['sortableDueDateFormat']; ?>" width="100"><?php echo $picksheet['date']; ?></td>
        <td align="right" width="100" class="digit_value" value="<?php echo number_format($picksheet['price'],2,".",""); ?>"><?php if($picksheet['price'] != 0) { echo '£' . number_format($picksheet['price'],2,".",","); } ?></td>
        <td align="right" width="100" class="digit_paid" value="<?php echo number_format($picksheet['paid'],2,".",""); ?>"><?php if($picksheet['paid'] != 0){ echo '£' . number_format($picksheet['paid'], 2, ".", ","); } ?></td>
        <td align="right" style="color:red;" class="digit_credit" value="<?php echo number_format($picksheet['credit'], 2, ".", ""); ?>"><?php if($picksheet['credit']){ echo '£' . number_format($picksheet['credit'], 2, ".", ","); }?></td>
        <td align="right" width="100" class="digit_outstanding" value="<?php echo number_format($picksheet['outstanding'], 2, ".", ""); ?>" <?php if($picksheet['outstanding'] < 0) { echo 'style="color:red;"'; } ?> ><?php if(number_format($picksheet['outstanding'], 2, ".", ",") != 0){ echo '£' . number_format($picksheet['outstanding'], 2, ".", ","); } ?></td>
    </tr>
    <?php
    }
    ?>