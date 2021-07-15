<?php
   	require('../functions.php');

    $toSkip = $_POST['toSkip'];
    $limit = 50;

    if($_POST['user_id'] != '' || $_POST['customer_id'] != '' || $_POST['species_id'] != '' || $_POST['intake_id'] != '' || $_POST['pallet_id'] != ''){
        $INTAKE_ID = mysqli_real_escape_string($conn, $_POST['intake_id']);
        $PALLET_ID = mysqli_real_escape_string($conn, $_POST['pallet_id']);
        $USER_ID = mysqli_real_escape_string($conn, $_POST['user_id']);
        $CUSTOMER_ID = mysqli_real_escape_string($conn, $_POST['customer_id']);
        $SPECIES_ID = mysqli_real_escape_string($conn, $_POST['species_id']);
        $CUT_ID = mysqli_real_escape_string($conn, $_POST['cut_id']);

        if($_POST['date_start'] != ''){
            $date_start = mysqli_real_escape_string($conn, $_POST['date_start']);
            $date_start = str_replace('/', '-', $date_start);
            $date_start = date('Y-m-d', strtotime($date_start));
            
            if($_POST['date_end'] == ''){
                $date_end = date('d/m/Y');
            }else{
                $date_end = mysqli_real_escape_string($conn, $_POST['date_end']);
            }

            $date_end = str_replace('/', '-', $date_end);
            $date_end = date('Y-m-d', strtotime($date_end));

         
            $dateQueryPiece = " && `pickerSheets.date` >= '$date_start' && `pickerSheets.date` <= '$date_end'";
        }

        if($CUSTOMER_ID != 0){
            $customerQueryPiece = " && pickerSheets.customer_id ='$CUSTOMER_ID'";
        }else{
            $customerQueryPiece = "";
        }

        if($USER_ID != 0){
            $userQueryPiece = " && pickerSheets.user_from_id ='$USER_ID'";
        }else{
            $userQueryPiece = "";
        }
        
        if($INTAKE_ID != ''){
            $picksheet_ids = array();

            $intakePicksheetSearchQuery = "SELECT pickerSheets.id FROM `pickerSheets`
                        JOIN `pickerItems` ON pickerItems.pickersheet_id = pickerSheets.id
                        JOIN `product` ON product.id = pickerItems.product_id
                        JOIN `pallet` ON pallet.id = product.pallet_id
                        JOIN `intake` ON intake.id = pallet.intake_id WHERE intake.id = $INTAKE_ID GROUP BY pickerSheets.id";

            $intakeQueryResult = mysqli_query($conn, $intakePicksheetSearchQuery);
            
            while($intakePicksheet = mysqli_fetch_array($intakeQueryResult)){
                array_push($picksheet_ids, $intakePicksheet['id']);
            }

            if(sizeof($picksheet_ids) > 0){
                $picksheet_ids = implode(',', $picksheet_ids);

                $intakeQueryPiece = " && pallet.intake_id=$INTAKE_ID && pickerSheets.id IN ($picksheet_ids)";
            }
        }

        if($PALLET_ID != 0){
            $picksheet_ids = array();

            $palletPicksheetSearchQuery = "SELECT pickerSheets.id FROM `pickerSheets`
                        JOIN `pickerItems` ON pickerItems.pickersheet_id = pickerSheets.id
                        JOIN `product` ON product.id = pickerItems.product_id
                        JOIN `pallet` ON pallet.id = product.pallet_id WHERE pallet.id = $PALLET_ID GROUP BY pickerSheets.id";

            $palletQueryResult = mysqli_query($conn, $palletPicksheetSearchQuery);
            
            while($palletPicksheet = mysqli_fetch_array($palletQueryResult)){
                array_push($picksheet_ids, $palletPicksheet['id']);
            }

            if(sizeof($picksheet_ids) > 0){
                $picksheet_ids = implode(',', $picksheet_ids);

                $palletQueryPiece = " && pickerSheets.id IN ($picksheet_ids)";
            }
        }

        if($SPECIES_ID != 0){
            $cuts_array = array();
            
            if($CUT_ID != 0){
                array_push($cuts_array, $CUT_ID);
            }else{ // no cut was posted in the form, get all cuts for the posted species_id
                $cutsResult = getCutsFor($SPECIES_ID);
                while($cut = mysqli_fetch_array($cutsResult)){ array_push($cuts_array, $cut['id']); }
            }

            $cut_ids = implode(',', $cuts_array);
            
            $searchQueryString = "SELECT product.cost as product_cost, pickerItems.price as picker_price, pickerSheets.id as pick_id, pickerSheets.*, product.*, product.id as product_id FROM `pickerSheets`
                        JOIN `pickerItems` ON pickerItems.pickersheet_id = pickerSheets.id
                        JOIN `product` ON product.id = pickerItems.product_id
                        JOIN `pallet` ON product.pallet_id = pallet.id
                        WHERE pickerSheets.completed = 1 && product.cut_id in ($cut_ids) $intakeQueryPiece $palletQueryPiece $userQueryPiece $dateQueryPiece $customerQueryPiece GROUP BY pick_id LIMIT $toSkip, $limit";
        }else{

            $searchQueryString = "SELECT product.cost as product_cost, pickerItems.price as picker_price, pickerSheets.id as pick_id, pickerSheets.*, product.*, product.id as product_id FROM `pickerSheets`
                        JOIN `pickerItems` ON pickerItems.pickersheet_id = pickerSheets.id
                        JOIN `product` ON product.id = pickerItems.product_id
                        JOIN `pallet` ON product.pallet_id = pallet.id
                        WHERE pickerSheets.completed=1 $intakeQueryPiece $palletQueryPiece $userQueryPiece $dateQueryPiece $customerQueryPiece GROUP BY pick_id LIMIT $toSkip, $limit";
                        
        }

    }
?>
<?php if($toSkip == 0){ ?>
<tr>
    <th align="left">SALESMAN</th>
    <th align="left">DATE</th>
    <th align="left">INVOICE ID</th>
    <th align="left">Customer</th>

    <th align="left">Intake ID</th>
    <th align="left">Plt ID</th>
    <th align="left">Nationality</th>
    <th align="left">Temp.</th>
    <th align="left">Category</th>
    <th align="left">Product</th>
    <th align="left">Brand</th>
    <th align="left">Qty</th>
    <th align="left">Unit</th>
    <th align="left">kg</th>

    <th align="left">Cost</th>
    <th align="left">Sell</th>
    <th align="left">Profit</th>
</tr>
<?php } ?>
<?php
    $searchResults = mysqli_query($conn, $searchQueryString);

    $count = mysqli_num_rows($searchResults);
    $newSkipCount = ($toSkip + $count);

    if($count == $limit){
        $moreRowsAvailable = 1;
    }else{
        $moreRowsAvailable = 0;
    }

    
    while($invoice = mysqli_fetch_array($searchResults)){
    
        $row_intake_id = intakeIDfromPalletID($invoice['pallet_id']);
    
        if($INTAKE_ID != ''){
            if($INTAKE_ID != $row_intake_id){
                continue;
            }
        }

        if($PALLET_ID != ''){
            if($PALLET_ID != $invoice['pallet_id']){
                continue;
            }
        }

        $invoice_price = invoiceTotal($invoice['pick_id']);

        $quantity = weightCountOfProductOnPicksheet($invoice['pick_id'], $invoice['product_id']);
        $weight_total = weightValueOfProductOnPicksheet($invoice['pick_id'], $invoice['product_id']);

        if($invoice['unit'] == 'PPC'){
            $total_product_cost = $invoice['product_cost'] * $quantity;
            $total_product_sell = $invoice['picker_price'] * $quantity;    
        }else{
            $total_product_cost = $invoice['product_cost'] * $weight_total;
            $total_product_sell = $invoice['picker_price'] * $weight_total;
        }
        



        ?>
        <tr class="result">
            <td><?php echo getUsername($invoice['user_from_id']); ?></td>
            <td>
                <?php
                    $date_completed = $invoice['date_completed'];

                    $date_completed = str_replace('/', '-', $date_completed);
                    echo date('d/m/Y', strtotime($date_completed));
                ?>
            </td>
            <td><a href="invoice.php?id=<?php echo $invoice['pick_id']; ?>" target="_blank"><?php echo $invoice['pick_id']; ?></a></td>
            <td><?php echo customerName($invoice['customer_id']); ?> </td>
            
            <td><?php echo $row_intake_id; ?></td>
            <td><?php echo $invoice['pallet_id']; ?></td>
            <td><?php echo getNationality($invoice['nationality_id']); ?></td>
            <td><?php echo getTemp($invoice['cooling_id']); ?></td>
            <td><?php echo getCutGroupNameFromCut($invoice['cut_id']); ?></td>
            <td><?php echo getSpeciesFromCutID($invoice['cut_id']) .' ' . getCut($invoice['cut_id']); ?></td>
            <td><?php echo getBrand($invoice['brand_id']); ?></td>
            <td>
                <input type="hidden" class="quantityValue" value="<?php echo $quantity; ?>">
                <?php echo $quantity; ?>
            </td>
            <td><?php 
                if($invoice['unit'] == 'C'){
                    echo 'Cases';
                }else if($invoice['unit'] == 'P'){
                    echo 'GT';
                }else{
                    echo $invoice['unit'];
                }
            ?></td>
            <td>
                <input type="hidden" class="weightValue" value="<?php echo $weight_total; ?>">
                <?php echo $weight_total; ?> kg
            </td>

            <td>
                <?php
                    $cost_formatted = number_format($total_product_cost, 2);
                    
                    $cost = str_replace(",","",$cost_formatted);
                ?>

                <input type="hidden" class="costValue" value="<?php echo $cost; ?>">
                £<?php echo $cost_formatted; ?>
            </td>
            <td>
                <?php
                    $sell_formatted = number_format($total_product_sell, 2);
                    
                    $sell = str_replace(",","",$sell_formatted);
                ?>

                <input type="hidden" class="sellValue" value="<?php echo $sell; ?>">
                £<?php echo $sell_formatted; ?></td>
            <td>
                £<?php echo number_format(floor($total_product_sell) - floor($total_product_cost), 2); ?>
            </td>
        </tr>
        <?php
    }
?>
  <tr class="totals" style="background:#d6d6d6;padding:10px;font-weight:bold;">
    <td>&nbsp;&nbsp;</td>
    <td>&nbsp;&nbsp;</td>
    <td>&nbsp;&nbsp;</td>
    <td>&nbsp;&nbsp;</td>
    <td>&nbsp;&nbsp;</td>
    <td>&nbsp;&nbsp;</td>
    <td>&nbsp;&nbsp;</td>
    <td>&nbsp;&nbsp;</td>
    <td>&nbsp;&nbsp;</td>
    <td>&nbsp;&nbsp;</td>
    <td>&nbsp;&nbsp;</td>
    <td><div class="totalQuantityValue" style="font-size:13px;"></div></td>
    <td>&nbsp;&nbsp;</td>
    <td><div class="totalWeightValue" style="font-size:13px;"></div></td>
    <td><div class="totalCostValue" style="font-size:13px;"></div></td>
    <td><div class="totalSellValue" style="font-size:13px;"></div></td>
    <td><div class="totalProfitValue" style="font-size:13px;"></div></td>
</tr>
<script>
    <?php if($toSkip != 0){ ?>
        $('#resultsTable').find('tr.totals').first().remove();
    <?php } ?>
    $('#toSkipCount').val(<?php echo $newSkipCount; ?>);
    $('#moreRowsAvailable').val(<?php echo $moreRowsAvailable; ?>);
</script>