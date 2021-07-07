<?php
   	require('../functions.php');

    $toSkip = $_POST['toSkip'];
    $limit = 80;

    if($_POST['user_id'] != '' || $_POST['customer_id'] != '' || $_POST['species_id'] != '' || $_POST['intake_id'] != '' || $_POST['pallet_id'] != ''){
        $INTAKE_ID = mysqli_real_escape_string($conn, $_POST['intake_id']);
        $PALLET_ID = mysqli_real_escape_string($conn, $_POST['pallet_id']);
        $USER_ID = mysqli_real_escape_string($conn, $_POST['user_id']);
        $CUSTOMER_ID = mysqli_real_escape_string($conn, $_POST['customer_id']);
        $SPECIES_ID = mysqli_real_escape_string($conn, $_POST['species_id']);

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
        
        if($INTAKE_ID != 0){
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

                $intakeQueryPiece = " && pickerSheets.id IN ($picksheet_ids)";
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
            
            $cutsResult = getCutsFor($SPECIES_ID);
            
            while($cut = mysqli_fetch_array($cutsResult)){ array_push($cuts_array, $cut['id']); }

            $cut_ids = implode(',', $cuts_array);
            
            $searchQueryString = "SELECT pickerSheets.*, product.*, product.id as product_id FROM `pickerSheets`
                        JOIN `pickerItems` ON pickerItems.pickersheet_id = pickerSheets.id
                        JOIN `product` ON product.id = pickerItems.product_id
                        WHERE pickerSheets.completed = 1 && product.cut_id in ($cut_ids) $intakeQueryPiece $palletQueryPiece $userQueryPiece $dateQueryPiece $customerQueryPiece  LIMIT $toSkip, $limit";
        }else{
                $searchQueryString = "SELECT pickerSheets.*, product.*, product.id as product_id FROM `pickerSheets`
                            JOIN `pickerItems` ON pickerItems.pickersheet_id = pickerSheets.id
                            JOIN `product` ON product.id = pickerItems.product_id
                            WHERE completed=1 $intakeQueryPiece $palletQueryPiece $userQueryPiece $dateQueryPiece $customerQueryPiece LIMIT $toSkip, $limit";
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
    <th align="left">Quantity</th>
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
        $invoice_cost = invoiceTotalCost($invoice['id']);
        
        $invoice_price = invoiceTotal($invoice['id']);
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
            <td><a href="invoice.php?id=<?php echo $invoice['id']; ?>" target="_blank"><?php echo $invoice['id']; ?></a></td>
            <td><?php echo customerName($invoice['customer_id']); ?> </td>
            
            <td><?php echo intakeIDfromPalletID($invoice['pallet_id']); ?></td>
            <td><?php echo $invoice['pallet_id']; ?></td>
            <td><?php echo getNationality($invoice['nationality_id']); ?></td>
            <td><?php echo getTemp($invoice['cooling_id']); ?></td>
            <td><?php echo getCutGroupNameFromCut($invoice['cut_id']); ?></td>
            <td><?php echo getSpecies($invoice['species_id']) . ' ' . getCut($invoice['cut_id']); ?></td>
            <td><?php echo getBrand($invoice['brand_id']); ?></td>
            <td><?php echo countFromProductIDArray(array($invoice['product_id'])); ?></td>
            <td><?php 
                if($invoice['unit'] == 'C'){
                    echo 'Cases';
                }else{
                    echo $invoice['unit'];
                }
            ?></td>
            <td><?php echo getWeightFromProductID($invoice['product_id']); ?> kg</td>

            <td>£<?php echo number_format($invoice_cost, 2); ?></td>
            <td>£<?php echo number_format($invoice_price, 2); ?></td>
            <td>£<?php echo number_format($invoice_price - $invoice_cost, 2); ?></td>
        </tr>
        <?php
    }
?>
 
<script>
    $('#toSkipCount').val(<?php echo $newSkipCount; ?>);
    $('#moreRowsAvailable').val(<?php echo $moreRowsAvailable; ?>);
</script>