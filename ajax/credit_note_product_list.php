<?php
    require('../functions.php');

	$invoiceID = $_POST['invoiceID'];

    # get all returned intakes related to this invoice
    $returnIntakeResult = mysqli_query($conn, "SELECT * FROM `intake` WHERE delivery_note_number='$invoiceID'");

    $countReturnedIntakes = mysqli_num_rows($returnIntakeResult);

    if($countReturnedIntakes == 0){ # no returned intakes for this invoice
        echo '<b style="color:red;font-size:18px;">No returned intakes for Invoice #' . $invoiceID . ' was found</b>';
    }else{
        ?>           
        <table width="75%" border="0">
        <tr style="border-bottom:1px solid #f1f1f1;">
            <th align="left">Intake ID</th>
            <th align="left">Pallet ID</th>
            <th align="left">Product</th>
            <th align="left">Quantity</th>
            <th align="left">Price</th>
            <th align="left"></th>
        </tr>
        <?php
        # gather IDs of all returned intakes
        $returnedIntakeIDS = [];
        while($returnedIntake = mysqli_fetch_array($returnIntakeResult)){ array_push($returnedIntakeIDS, $returnedIntake['id']); }        
        $returnedIntakeIDS = implode(',', $returnedIntakeIDS);

        # 
        $palletsResult = mysqli_query($conn, "SELECT GROUP_CONCAT(id) as pallet_ids from `pallet` WHERE intake_id IN ($returnedIntakeIDS)");
        $palletData = mysqli_fetch_array($palletsResult);

        $pallet_ids = $palletData['pallet_ids'];

        $productsResult = mysqli_query($conn, "SELECT * FROM `product` WHERE pallet_id IN ($pallet_ids)");
        
        $i = 0;
        while($product = mysqli_fetch_array($productsResult)){
            $i++;
            $rowClass = "productRow" . $i;
            $productID = $product['id'];

            # get number of weights for this product
		    $weightCountResult = mysqli_query($conn, "SELECT id FROM `weights` WHERE product_id=$productID");
            $count = mysqli_num_rows($weightCountResult);

        ?>
        <tr class="<?php echo $rowClass; ?>" style="height:50px;border-bottom:1px solid #f1f1f1;">
            <td align="left">
                <span class=""><?php echo intakeIDfromPalletID($product['pallet_id']); ?></span>
                <input type="hidden" name="product_id[]" value="<?php echo $product['id']; ?>">    
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
                <select style="width:55px;height:30px;" name="quantity[]">
                    <?php
                        $tempcount = $count+1;
                        for($i=1;$i<$tempcount;$i++) { ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                    <?php } ?>
                </select>
            </td>
            <td align="left" class="">£<input type="number" name="price[]" style="outline:none;border:0;border-bottom:1px dashed black;width:100px;margin-left:10px;" value="<?php echo number_format((float)$product['price'], 2, '.', ''); ?>"></td>
            <td>
                <a href="javascript:removeProductRow('<?php echo $rowClass; ?>');" class="fa fa-times" style="color:red;text-decoration:none;font-size:22px;"></a>
            </td>
        </tr>
        <?php
        }
    }
?>
</table>