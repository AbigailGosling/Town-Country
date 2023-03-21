<?php
   	require(__DIR__.'/../functions.php');

    if(request('user_id'] != '' || $_POST['customer_id'] != '' || $_POST['species_id'] != '' || $_POST['intake_id'] != '' || $_POST['pallet_id'] != '' || $_POST['invoice_id'] != ''  || $_POST['brand_id'] != '' || $_POST['nationality_id') != ''){
        
        $INVOICE_ID = $mysqli->real_escape_string( request('invoice_id'));
        $INTAKE_ID = $mysqli->real_escape_string( request('intake_id'));
        $PALLET_ID = $mysqli->real_escape_string( request('pallet_id'));
        $USER_ID = $mysqli->real_escape_string( request('user_id'));
        $CUSTOMER_ID = $mysqli->real_escape_string( request('customer_id'));
        $SPECIES_ID = $mysqli->real_escape_string( request('species_id'));
        $CUTGROUP_ID = $mysqli->real_escape_string( request('cutgroup_id'));
        $COOLING_ID = $mysqli->real_escape_string( request('cooling_id'));
        $BRAND_ID = $mysqli->real_escape_string( request('brand_id'));
        $NATIONALITY_ID = $mysqli->real_escape_string( request('nationality_id'));
        $SUPPLIER_ID = $mysqli->real_escape_string( request('supplier_id'));
        $QUERY_VARS = array();
        if(request('date_start') != ''){
            $date_start = $mysqli->real_escape_string( request('date_start'));
            $date_start = str_replace('/', '-', $date_start);
            $date_start = date('Y-m-d', strtotime($date_start));
            
            if(request('date_end') == ''){
                $date_end = date('d/m/Y');
            }else{
                $date_end = $mysqli->real_escape_string( request('date_end'));
            }

            $date_end = str_replace('/', '-', $date_end);
            $date_end = date('Y-m-d', strtotime($date_end));

         
            $dateQueryPiece = " && pickerSheets.date_completed >= ? && pickerSheets.date_completed <= ?";
            $QUERY_VARS[] = $date_start;
            $QUERY_VARS[] = $date_end;
        }

        if($CUSTOMER_ID != "" && $CUSTOMER_ID != 0){
            $customerQueryPiece = " && pickerSheets.customer_id = ?";
            $QUERY_VARS[] = $CUSTOMER_ID;
        }else{
            $customerQueryPiece = "";
        }

        if($USER_ID != "" && $USER_ID != 0){
            $userQueryPiece = " && pickerSheets.user_from_id = ?";
            $QUERY_VARS[] = $USER_ID;
        }else{
            $userQueryPiece = "";
        }

        if($COOLING_ID != "" && $COOLING_ID != 0){
            $coolingQueryPiece = " && product.cooling_id = ?";
            $QUERY_VARS[] = $COOLING_ID;
        }else{
            $coolingQueryPiece = "";
        }
        
        if($BRAND_ID != "" && $BRAND_ID != 0){
            $brandQueryPiece = " && product.brand_id = ?";
            $QUERY_VARS[] = $BRAND_ID;
        }else{
            $brandQueryPiece = "";
        }

        if($NATIONALITY_ID != "" && $NATIONALITY_ID != 0){
            $nationQueryPiece = " && product.nationality_id = ?";
            $QUERY_VARS[] = $NATIONALITY_ID;
        }else{
            $nationQueryPiece = "";
        }

        if($SUPPLIER_ID != "" && $SUPPLIER_ID != 0){
            $supplierQueryPiece = " && intake.supplier_id = ?";            
            $QUERY_VARS[] = $SUPPLIER_ID;
        }else{
            $supplierQueryPiece = "";
        }

        if($INTAKE_ID != "" && $INTAKE_ID != ''){
            $picksheet_ids = array();

            $intakePicksheetSearchQuery = "SELECT pickerSheets.id FROM `pickerSheets`
                        JOIN `pickerItems` ON pickerItems.pickersheet_id = pickerSheets.id
                        JOIN `product` ON product.id = pickerItems.product_id
                        JOIN `pallet` ON pallet.id = product.pallet_id
                        JOIN `intake` ON intake.id = pallet.intake_id WHERE intake.id = ? GROUP BY pickerSheets.id";

            $intakeQueryResult = prepareExecuteQuery($intakePicksheetSearchQuery,'i',[$INTAKE_ID]);
            
            while($intakePicksheet = mysqli_fetch_array($intakeQueryResult)){
                array_push($picksheet_ids, $intakePicksheet['id']);
            }

            if(sizeof($picksheet_ids) > 0){
                $picksheet_ids = implode(',', $picksheet_ids);

                $intakeQueryPiece = " && pallet.intake_id=? && pickerSheets.id IN ($picksheet_ids)";
                $QUERY_VARS[] = $INTAKE_ID;
            }
        }

        if($INVOICE_ID != "" && $INVOICE_ID != 0){
            $internal_invoice_ids = explode(",",$INVOICE_ID);
            foreach($internal_invoice_ids as $invoice_id_temp)
            {
                $QUERY_VARS[] = $invoice_id_temp;
            }
            $invoiceQueryPiece = " && pickerSheets.id IN (".implode(",",array_fill(0,count($internal_invoice_ids),"?")).")";
        }

        if($PALLET_ID != "" && $PALLET_ID != 0){
            $picksheet_ids = array();

            $palletPicksheetSearchQuery = "SELECT pickerSheets.id FROM `pickerSheets`
                        JOIN `pickerItems` ON pickerItems.pickersheet_id = pickerSheets.id
                        JOIN `product` ON product.id = pickerItems.product_id
                        JOIN `pallet` ON pallet.id = product.pallet_id WHERE pallet.id = ? GROUP BY pickerSheets.id";

            $palletQueryResult = prepareExecuteQuery($palletPicksheetSearchQuery,'i',[$PALLET_ID]);
            
            while($palletPicksheet = mysqli_fetch_array($palletQueryResult)){
                array_push($picksheet_ids, $palletPicksheet['id']);
            }

            if(sizeof($picksheet_ids) > 0){
                $picksheet_ids = implode(',', $picksheet_ids);

                $palletQueryPiece = " && pallet.id=? && pickerSheets.id IN ($picksheet_ids)";
                $QUERY_VARS[] = $PALLET_ID;
            }
        }

        if($SPECIES_ID != "" && $SPECIES_ID != 0){
            
            if($CUTGROUP_ID != 0){
                $ARRAY_CUTS = cutsFromCutGroup($SPECIES_ID, $CUTGROUP_ID);
                $cut_ids = implode(',', $ARRAY_CUTS);
            }else{
                $cut_ids = array();
                $cuts = getCutsFor($SPECIES_ID);
                while($cut = mysqli_fetch_array($cuts)){ array_push($cut_ids, $cut['id']); }

                $cut_ids = implode(',', $cut_ids);
            }

            $searchResults = prepareExecuteQuery("SELECT pallet.intake_id as intake_id, product.cost as product_cost, pickerItems.price as picker_price, pickerSheets.id as pick_id, pickerSheets.*, product.*, product.id as product_id, intake.supplier_id FROM `pickerSheets`
                        JOIN `pickerItems` ON pickerItems.pickersheet_id = pickerSheets.id
                        JOIN `product` ON product.id = pickerItems.product_id
                        JOIN `pallet` ON product.pallet_id = pallet.id
                        JOIN `intake` ON pallet.intake_id = intake.id
                        WHERE pickerSheets.completed = 1 && product.cut_id in ($cut_ids) $dateQueryPiece $customerQueryPiece $userQueryPiece $coolingQueryPiece $brandQueryPiece $nationQueryPiece $supplierQueryPiece $intakeQueryPiece $invoiceQueryPiece $palletQueryPiece GROUP BY pick_id, pickerItems.product_id"
                        ,str_repeat('s',count($QUERY_VARS)),$QUERY_VARS);


        }else{

            $searchResults = prepareExecuteQuery("SELECT pallet.intake_id as intake_id, product.cost as product_cost, pickerItems.price as picker_price, pickerSheets.id as pick_id, pickerSheets.*, product.*, product.id as product_id, intake.supplier_id FROM `pickerSheets`
                        JOIN `pickerItems` ON pickerItems.pickersheet_id = pickerSheets.id
                        JOIN `product` ON product.id = pickerItems.product_id
                        JOIN `pallet` ON product.pallet_id = pallet.id
                        JOIN `intake` ON pallet.intake_id = intake.id
                        WHERE pickerSheets.completed=1 $dateQueryPiece $customerQueryPiece $userQueryPiece $coolingQueryPiece $brandQueryPiece $nationQueryPiece $supplierQueryPiece $intakeQueryPiece $invoiceQueryPiece $palletQueryPiece GROUP BY pickerSheets.id, pickerItems.product_id"
                        ,str_repeat('s',count($QUERY_VARS)),$QUERY_VARS);
                        
        }

    }
?>

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
    <th align="left">Supplier</th>
    <th align="left">Qty</th>
    <th align="left">Unit</th>
    <th align="left">kg</th>

    <th align="left">Cost</th>
    <th align="left">Sell</th>
    <th align="left">Profit</th>
</tr>

<?php

    $invoice_price_store = array();
    $quantity_store = array();
    $weight_total_store = array();

    while($invoice = mysqli_fetch_array($searchResults)){
        $row_intake_id = $invoice['intake_id'];
    
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
        if(array_key_exists($invoice['pick_id'],$invoice_price_store))
        {
            $invoice_price = $invoice_price_store[$invoice['pick_id']];
        }
        else
        {
            $invoice_price = invoiceTotal($invoice['pick_id']);
            $invoice_price_store[$invoice['pick_id']] = $invoice_price;
        }

        if(array_key_exists($invoice['pick_id'] ."_". $invoice['product_id'],$quantity_store))
        {
            $quantity = $quantity_store[$invoice['pick_id'] ."_". $invoice['product_id']];
        }
        else
        {
            $quantity = weightCountOfProductOnPicksheet($invoice['pick_id'], $invoice['product_id']);
            $quantity_store[$invoice['pick_id'] ."_". $invoice['product_id']] = $quantity;
        }

        if(array_key_exists($invoice['pick_id'] ."_". $invoice['product_id'],$weight_total_store))
        {
            $weight_total = $weight_total_store[$invoice['pick_id'] ."_". $invoice['product_id']];
        }
        else
        {
            $weight_total = weightValueOfProductOnPicksheet($invoice['pick_id'], $invoice['product_id']);
            $weight_total_store[$invoice['pick_id'] ."_". $invoice['product_id']] = $weight_total;
        }

        if($invoice['unit'] == 'PPC'){
            $total_product_cost = $invoice['product_cost'] * $quantity;
            $total_product_sell = $invoice['picker_price'] * $quantity;    
        }else{
            $total_product_cost = $invoice['product_cost'] * $weight_total;
            $total_product_sell = $invoice['picker_price'] * $weight_total;
        }
        $date_completed = $invoice['date_completed'];
        $date_completed = str_replace('/', '-', $date_completed);
        $cell_date_completed = date('d/m/Y', strtotime($date_completed));

        $cell_username = getUsername($invoice['user_from_id']);
        $cell_customer_name = customerName($invoice['customer_id']);
        $cell_nationality = getNationality($invoice['nationality_id']);
        $cell_temp = getTemp($invoice['cooling_id']);
        $cell_cutgroup = getCutGroupNameFromCut($invoice['cut_id']);
        $cell_product = getSpeciesFromCutID($invoice['cut_id']) .' ' . getCut($invoice['cut_id']);
        $cell_brand = getBrand($invoice['brand_id']);
        $cell_supplier = getSupplier($invoice['supplier_id'])['name'];

        ?>
        <tr class="result">
            <td><?php echo $cell_username ?></td>
            <td><?php echo $cell_date_completed; ?></td>
            <td><a href="invoice.php?id=<?php echo $invoice['pick_id']; ?>" target="_blank"><?php echo $invoice['pick_id']; ?></a></td>
            <td><?php echo $cell_customer_name; ?> </td>
            <td><?php echo $row_intake_id; ?></td>
            <td><?php echo $invoice['pallet_id']; ?></td>
            <td><?php echo $cell_nationality ?></td>
            <td><?php echo $cell_temp; ?></td>
            <td><?php echo $cell_cutgroup; ?></td>
            <td><?php echo $cell_product; ?></td>
            <td><?php echo $cell_brand; ?></td>
            <td><?php echo $cell_supplier; ?></td>
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
                £<?php echo number_format($total_product_sell - $total_product_cost, 2); ?>
            </td>
        </tr>

        <?php
            $credit_value = 0;
            $credit_qty = 0;
            $weightReturned = 0;
            $cost_value = 0;
            $invoice_id = $invoice['pick_id'];
            
            $payment_ids = [];
            $credit_payments = prepareExecuteQuery("SELECT * FROM `invoice_payments` WHERE invoice_id=? && payment_method='CREDIT_NOTE'",'i',[$invoice_id]);
            while($credit_payment = mysqli_fetch_array($credit_payments)){ array_push($payment_ids, $credit_payment['id']); }
            if (count($payment_ids) > 0)
            {
                $payment_ids = implode(',', $payment_ids);

                
                $credit_items = prepareExecuteQuery("SELECT * FROM `credit_note_items` WHERE payment_id IN ($payment_ids)");
                while($credit_item = mysqli_fetch_array($credit_items)){
                    $returned_product_id = $credit_item['product_id'];
                    

                    $real_cut_id = $invoice['cut_id'];

                    $returned_product_result =  prepareExecuteQuery("SELECT * FROM `product` WHERE id=? && cut_id = ?",'ii',[$returned_product_id,$real_cut_id]);

                    $returned_product_count = mysqli_num_rows($returned_product_result);
                    if($returned_product_count > 0){
                        $creditNoteCheck = prepareExecuteQuery("SELECT `credit_note_items`.*,`product`.cost,`product`.pallet_id FROM `credit_note_items` INNER JOIN `product` ON `product`.id = `credit_note_items`.product_id WHERE `credit_note_items`.product_id='$returned_product_id' AND `product`.original_pallet_id = ?",'i',[$invoice['pallet_id']]);
                        
                        while($creditItem = mysqli_fetch_array($creditNoteCheck)){
                            $creditItempallet_id = $creditItem['pallet_id'];
                            $weight = weightFromProductIDArray([$returned_product_id]);
                            $weightReturned += $weight;
                            $credit_value += number_format((float)$creditItem['price'] * $weight, 2, '.', '');
                            $cost_value += number_format((float)$creditItem['cost'] * $weight, 2, '.', '');
                            $credit_qty += $creditItem['quantity'];
                        }
                    }
                }
                
            }
            // cost = original product total
            // sell = creditnote total
            // profit = credit - cost

            // $product_id = $invoice['product_id'];
            // $creditNoteCheck = prepareExecuteQuery("SELECT * FROM `credit_note_items` WHERE product_id='$product_id'");
            
            // while($creditItem = mysqli_fetch_array($creditNoteCheck)){
            //     $credit_value += ((float) $creditItem['price'] * $creditItem['quantity']);
            //     $credit_qty += $creditItem['quantity'];
            // }

            if($credit_qty > 0){
        ?>
        <tr class="result" style="height:28px;">
            <td style="color:red;"><?php echo $cell_username ?></td>
            <td style="color:red;"><?php echo $cell_date_completed; ?></td>
            <td style="color:red;"><a href="invoice.php?id=<?php echo $invoice['pick_id']; ?>" target="_blank"><?php echo $invoice['pick_id']; ?></a></td>
            <td style="color:red;"><?php echo $cell_customer_name; ?> </td>
            <td style="color:red;"><?php echo $row_intake_id; ?></td>
            <td style="color:red;"><?php echo $creditItempallet_id; ?></td>
            <td style="color:red;"><?php echo $cell_nationality ?></td>
            <td style="color:red;"><?php echo $cell_temp; ?></td>
            <td style="color:red;"><?php echo $cell_cutgroup; ?></td>
            <td style="color:red;"><?php echo $cell_product; ?></td>
            <td style="color:red;"><?php echo $cell_brand; ?></td>
            <td style="color:red;"><?php echo $cell_supplier; ?></td>
            <td style="color:red;" colspan="1"><?php echo $credit_qty; ?></td>
            <td style="color:red;">
                <?php 
                    if($invoice['unit'] == 'C'){
                        echo 'Cases';
                    }else if($invoice['unit'] == 'P'){
                        echo 'GT';
                    }else{
                        echo $invoice['unit'];
                    }
                ?>
            </td>
            <td style="color:red;"><?php echo $weightReturned; ?> kg</td>
            <td style="color:red;">
                <?php
                    $sell_formatted = number_format($credit_value, 2);
                    $sell = str_replace(",","",$sell_formatted);
                ?>
                £<?php echo $sell_formatted; ?></td>
            </td>
            <td style="color:red;">
                £<?php echo number_format($cost_value, 2); ?>
            </td>
            <td style="color:red;">
            <?php
                $profit = $cost_value - $credit_value;
            ?>
                <input type="hidden" class="costValue" value="<?php echo abs($profit); ?>">
                £<?php echo number_format($profit, 2); ?>
            </td>
        </tr>
        <?php
            }
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
    <td>&nbsp;&nbsp;</td>
    <td><div class="totalQuantityValue" style="font-size:13px;"></div></td>
    <td>&nbsp;&nbsp;</td>
    <td><div class="totalWeightValue" style="font-size:13px;"></div></td>
    <td><div class="totalCostValue" style="font-size:13px;"></div></td>
    <td><div class="totalSellValue" style="font-size:13px;"></div></td>
    <td><div class="totalProfitValue" style="font-size:13px;"></div><div class="totalProfitPercent" style="font-size:13px;"></div></td>
</tr>
