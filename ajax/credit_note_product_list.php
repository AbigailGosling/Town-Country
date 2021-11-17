<?php
    require('../functions.php');

	$invoiceID = $_POST['invoiceID'];

    # get all returned intakes related to this invoice
    $returnIntakeResult = mysqli_query($conn, "SELECT * FROM `intake` WHERE delivery_note_number='$invoiceID' AND returned = 1");

    $countReturnedIntakes = mysqli_num_rows($returnIntakeResult);

    if($countReturnedIntakes == 0){ # no returned intakes for this invoice
    ?>
    <Br>
    <h2 style="font-size:22px;padding-bottom:10px;">Create Return</h2>
    <table width="75%" border="0">
        <tr style="border-bottom:1px solid #f1f1f1;">
            <th align="left">Description</th>
            <th align="left">Quantity</th>
            <th align="left"></th>
        </tr>
        <?php
            for($i=0;$i<5;$i++){
            $rowClass = 'classItem' . $i;
        ?>
        <tr class="<?php echo $rowClass; ?>" style="height:50px;border-bottom:1px solid #f1f1f1;">
            <td align="left">
                <input type="hidden" name="product_id[]" value="0">
                <input type="text" name="description[]" required>
            </td>

            <td align="left">
                <input type="text" name="quantity[]" style="width:90px;" required>
            </td>
            <td align="left" class="">£<input type="text" name="price[]" style="outline:none;border:0;border-bottom:1px dashed black;width:100px;margin-left:10px;" value="" required></td>
            <td>
                <a href="javascript:removeProductRow('<?php echo $rowClass; ?>');" class="fa fa-times" style="color:red;text-decoration:none;font-size:22px;"></a>
            </td>
        </tr>
        <?php } ?>
    </table>
    <?php
    }else{
?>
<div style="background:#f2f2f2;padding:10px;">
<h2 style="font-size:22px;padding-bottom:10px;">Original Invoice</h2>
<table width="100%" border="0">
<tr style="border-bottom:1px solid #f1f1f1;">
    <th align="left">Intake ID</th>
    <th align="left">Pallet ID</th>
    <th align="left">Product</th>
    <th align="left">Quantity</th>
    <th align="left">Unit</th>
    <th align="left">Weight</th>
    <th align="left">Price</th>
    <th align="left"></th>
</tr>
<?php

    $outpalletResult = mysqli_query($conn, "SELECT * FROM `palletsOut` WHERE pickersheet_id='$invoiceID'");
    $outpalletCount = mysqli_num_rows($outpalletResult);

    $total_weight_count = 0;
    $total_case_count = 0;
    $rowCount = 0;

    while($outpallet = mysqli_fetch_array($outpalletResult)){
        $rowCount++;
        $rowClass = "productRow" . $rowCount;

        $weightids = explode(',', $outpallet['weight_ids']);

        $productIDArray = array();

        foreach($weightids as $weightid){
            $x = "SELECT * FROM `weights` WHERE id='$weightid'";
            $y = mysqli_query($conn, $x);
            $weight = mysqli_fetch_array($y);

            if(!in_array($weight['product_id'], $productIDArray)){
                array_push($productIDArray, $weight['product_id']);
            }

            $queryBits .= ' id = ' . $weightid . ' || ';
        }
        $kg = 0;
        foreach($productIDArray as $productID){

            $x1 = "SELECT * FROM `product` WHERE id='$productID'";
            $y1 = mysqli_query($conn, $x1);
            $product = mysqli_fetch_array($y1);


            if($product['unit'] == 'PPC'){
                $ext = ' Cases';
            }else{
                $ext = ' kg';
            }

            $x2 = "SELECT * FROM `weights` WHERE ";

            foreach($weightids as $weightid){
                $x2 .= "product_id='$productID' && id='$weightid' || ";
            }

            $x2 = rtrim($x2," || ");
            $y2 = mysqli_query($conn, $x2);
            $count = mysqli_num_rows($y2);
            
             
            
            while($weightRow = mysqli_fetch_array($y2)){               
                if($weightRow['weight_tear'] == $weightRow['weight_gross']){
                    $tw = $weightRow['weight_gross'];
                }else{
                    $tw = $weightRow['weight_gross'] - $weightRow['weight_tear'];
                }
                
                $kg = $kg + $tw;
                
                $kg = number_format($kg, 2, '.', '');
            }

        ?>
        <tr class="<?php echo $rowClass; ?>" style="height:50px;border-bottom:1px solid #f1f1f1;">
            <td align="left"><span class=""><?php echo intakeIDfromPalletID($product['pallet_id']); ?></span></td>
            <td align="left"><span class=""><?php echo $product['pallet_id']; ?></span></td>
            <td align="left">                    
                <span class=""><?php echo getNationality($product['nationality_id']); ?></span>
                <span class=""><?php echo getTemp($product['cooling_id']); ?></span>
                <b class=""><?php echo getSpeciesFromCutID($product['cut_id']); ?></b>
                <b class=""><?php echo getCut($product['cut_id']); ?></b>
                <b class=""><?php echo getBrand($product['brand_id']); ?></b>
            </td>

            <?php
                $productID = $product['id'];
                $howManyX = "SELECT * FROM `pickerItems` WHERE pickersheet_id='$invoiceID' AND product_id='$productID'";
                $howManyY = mysqli_query($conn, $howManyX);
                $pickerItem = mysqli_fetch_array($howManyY);
                $howMany = mysqli_num_rows($howManyY);
            ?>
            <td align="left"><b class="">
                <b><?php echo $count; ?></b>
            </td>
            <td align="left">
                <b class="s">
                <?php

                    if($product['unit'] == 'C'){
                        $unit = 'Cases';
                    }else if($product['unit'] == 'PPC'){
                        $unit = 'Per Case';
                    }else if($product['unit'] == 'P'){
                        $unit = 'Pallet';
                    }else if($product['unit'] == 'KG'){
                        $unit = 'Kilo';
                    }else{
                        $unit = 'Cases';
                    }

                    echo $unit;
                ?>
                </b>
            </td>
            <td>
                <?php echo $kg; ?> kg
            </td>
             
            <td align="left" class="">£<input type="number" disabled style="outline:none;border:0;border-bottom:1px dashed black;width:100px;margin-left:10px;" value="<?php echo number_format((float)$pickerItem['price'], 2, '.', ''); ?>"></td>
            <td>
            </td>
        </tr>
        <?php
            }
        }
    ?>
</table> 
</div>
<Br/>
<h2 style="font-size:22px;padding-bottom:10px;">Returned Products</h2>

        <table width="75%" border="0">
        <tr style="border-bottom:1px solid #f1f1f1;">
            <th align="left">Intake ID</th>
            <th align="left">Pallet ID</th>
            <th align="left">Product</th>
            <th align="left">Quantity</th>
            <th align="left">Unit</th>
            <th align="left">Weight</th>
            <th align="left">Price</th>
            <th align="left"></th>
        </tr>
        <?php
        # gather IDs of all returned intakes
        $returnedIntakeIDS = [];
        while($returnedIntake = mysqli_fetch_array($returnIntakeResult)){ array_push($returnedIntakeIDS, $returnedIntake['id']); }        
        $returnedIntakeIDS = implode(',', $returnedIntakeIDS);

        # 
        debug_to_console("SELECT GROUP_CONCAT(id) as pallet_ids from `pallet` WHERE intake_id IN ($returnedIntakeIDS)");
        $palletsResult = mysqli_query($conn, "SELECT GROUP_CONCAT(id) as pallet_ids from `pallet` WHERE intake_id IN ($returnedIntakeIDS)");
        $palletData = mysqli_fetch_array($palletsResult);

        $pallet_ids = $palletData['pallet_ids'];

        $productsResult = mysqli_query($conn, "SELECT * FROM `product` WHERE pallet_id IN ($pallet_ids)");
        
        $i = 0;
        while($product = mysqli_fetch_array($productsResult)){
            $i++;
            $rowClass = "productRow" . $i;
            $productID = $product['id'];

            $productQuantityToDeduct = 0;

            # Check for credit notes with this product_id
            $creditNoteResult = mysqli_query($conn, "SELECT * FROM `credit_note_items` WHERE product_id='$productID'");

            # If this product has a credit note
            if(mysqli_num_rows($creditNoteResult) > 0){
                while($creditNoteData = mysqli_fetch_array($creditNoteResult)){
                    $productQuantityToDeduct += $creditNoteData['quantity'];
                }
            }
            
            # get number of weights for this product
		    $weightCountResult = mysqli_query($conn, "SELECT id FROM `weights` WHERE product_id=$productID");
            $count = mysqli_num_rows($weightCountResult);

            $loop_count = $count;
            $loop_count -= $productQuantityToDeduct;

        ?>
        <tr class="<?php echo $rowClass; ?>" style="height:50px;border-bottom:1px solid #f1f1f1;<?php if($loop_count <= 0){ echo 'opacity:0.4;pointer-events:none;'; } ?>">
            <td align="left">
                <span class=""><?php echo intakeIDfromPalletID($product['pallet_id']); ?></span>
                <?php if($loop_count > 0){ ?>
                <input type="hidden" name="product_id[]" value="<?php echo $product['id']; ?>">
                <?php } ?>
            </td>
            <td align="left"><span class=""><?php echo $product['pallet_id']; ?></span></td>
            <td align="left">                    
                <span class=""><?php echo getNationality($product['nationality_id']); ?></span>
                <span class=""><?php echo getTemp($product['cooling_id']); ?></span>
                <b class=""><?php echo getSpeciesFromCutID($product['cut_id']); ?></b>
                <b class=""><?php echo getCut($product['cut_id']); ?></b>
                <b class=""><?php echo getBrand($product['brand_id']); ?></b>
            </td>
            
            <?php
                $howManyX = "SELECT * FROM `pickerItems` WHERE pickersheet_id='$invoiceID' AND product_id='$productID'";
                $howManyY = mysqli_query($conn, $howManyX);
                $pickerItem = mysqli_fetch_array($howManyY);
                $howMany = mysqli_num_rows($howManyY);
            ?>
            <td align="left"><b class="">
                <?php if($loop_count > 0){ ?>
                    <select style="width:55px;height:30px;" name="quantity[]">
                        <?php
                            $fix_loop_val = $loop_count+1;
                            for($i=1;$i<$fix_loop_val;$i++) { ?>
                            <option value="<?php echo $i; ?>" <?php if($i == ($fix_loop_val-1)){ echo 'selected'; } ?>><?php echo $i; ?></option>
                        <?php } ?>
                    </select>
                <?php } ?>
            </td>
            <td>
                 <?php
                    if($product['unit'] == 'C'){
                        $unit = 'Cases';
                    }else if($product['unit'] == 'PPC'){
                        $unit = 'Per Case';
                    }else if($product['unit'] == 'P'){
                        $unit = 'Pallet';
                    }else if($product['unit'] == 'KG'){
                        $unit = 'Kilo';
                    }else{
                        $unit = 'Cases';
                    }

                    echo $unit;
                ?>
            </td>
            <td><?php echo weightFromProductIDArray([$product['id']]); ?> kg</td>
            <td align="left" class="">
                <?php if($loop_count > 0){ ?>    
                £<input type="text" name="price[]" style="outline:none;border:0;border-bottom:1px dashed black;width:100px;margin-left:10px;" value="<?php echo number_format((float)$product['price'], 2, '.', ''); ?>"></td>
                <?php } ?>
            <td>
                
                <?php if($loop_count > 0){ ?>
                <a href="javascript:removeProductRow('<?php echo $rowClass; ?>');" class="fa fa-times" style="color:red;text-decoration:none;font-size:22px;"></a>
                <?php } ?>
            </td>
        </tr>
        <?php
        }
    }
?>
</table>

<br/>