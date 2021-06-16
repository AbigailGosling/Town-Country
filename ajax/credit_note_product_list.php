<?php
    require('../functions.php');

	$invoiceID = $_POST['invoiceID'];
?>
<table width="70%" border="0">
<tr style="border-bottom:1px solid #f1f1f1;">
    <th align="left">Intake ID</th>
    <th align="left">Pallet ID</th>
    <th align="left">Product</th>
    <th align="left">Quantity</th>
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

            ${"globalProductCount" . $product['id']} += $count;
            
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
                <select style="width:55px;height:30px;">
                    <?php
                        $tempcount = $count+1;
                        for($i=1;$i<$tempcount;$i++) { ?>
                        <option><?php echo $i; ?></option>
                    <?php } ?>
                </select>
            </td>
            <td align="left" style="display:none;">
                <b class="unit">
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
            <td align="left" style="display:none">
                <b class="weight">
                <?php
                
                    $qBit = '';
                    
                    $kg = 0;
                    
                    foreach($weightids as $weightid){
                        $qBit .= " id = '$weightid' && product_id='$productID' || ";
                    }

                    $qBit = rtrim($qBit," || ");
                    
                    $xxWeight = "SELECT * FROM `weights` WHERE $qBit";
                    $yyWeight = mysqli_query($conn, $xxWeight);
                    
                    while($weightRow = mysqli_fetch_array($yyWeight)){
                        
                        if($weightRow['weight_tear'] == $weightRow['weight_gross']){
                            $tw = $weightRow['weight_gross'];
                        }else{
                            $tw = $weightRow['weight_gross'] - $weightRow['weight_tear'];
                        }
                        
                        $kg = $kg + $tw;
                        
                        $kg = number_format($kg, 2, '.', '');
                    }
                    
                    if($product['unit'] == 'PPC'){
                        echo $count . ' Cases';
                        $totalPriceRow = number_format((float)$count * $pickerItem['price'], 2, '.', '');
                        $totalPrice += number_format((float)$count * $pickerItem['price'], 2, '.', '');
                        $total_case_count += $count;
                    }else{
                        echo $kg . ' kg';
                        $totalPriceRow = number_format((float)$kg * $pickerItem['price'], 2, '.', '');
                        $totalPrice += number_format((float)$kg * $pickerItem['price'], 2, '.', '');
                        $total_weight_count += $kg;
                    }
                    
                ?>
                </b>
            </td>
            <td align="left" class="">£<input type="number" style="outline:none;border:0;border-bottom:1px dashed black;width:100px;margin-left:10px;" value="<?php echo number_format((float)$pickerItem['price'], 2, '.', ''); ?>"></td>
            <td>
                <a href="javascript:removeProductRow('<?php echo $rowClass; ?>');" class="fa fa-times" style="color:red;text-decoration:none;font-size:22px;"></a>
            </td>
        </tr>
        <?php
            }
        }
    ?>
</table>