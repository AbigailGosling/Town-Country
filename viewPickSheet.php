<?php
	include('includes/frontHeader.php');
	
	$picksheetid = $_GET['id'];
	
	$x = "SELECT * FROM `pickerSheets` WHERE id ='$picksheetid'";
	$y = mysqli_query($conn, $x);
	
	$pickerSheet = mysqli_fetch_array($y);
	
	
	$customerName = customerName($pickerSheet['customer_id']);
	
?>
<div id="top">
	<a href="menu.php" id="menu">MENU</a>
	<a href="logout.php" id="logout">LOGOUT</a>
</div>
<script type="text/javascript">
</script>

<main class="int container">
	
	<a href="<?php echo $domain; ?>pickSheetList.php" class="backbtn">< Back</a>
	
	<h1>PICK FORM</h1>
	
	<div>
		<a href="picknote.php?id=<?php echo $pickerSheet['id']; ?>">Pick Note</a>
		|<a href="deliverynote.php?id=<?php echo $pickerSheet['id']; ?>">Delivery Note</a>|
		<a href="invoice.php?id=<?php echo $pickerSheet['id']; ?>">Invoice</a>
	</div><br/>
	
	<div style="padding-bottom:10px;font-size: 18px;">
		<label>Customer Name: <?php echo $customerName; ?></label>
	</div>
	
	<div style="padding-bottom:10px;font-size: 18px;">
		<label>Delivery Date: <?php echo $pickerSheet['estimated_delivery_date']; ?></label>
	</div>
	<form method="POST" id="addtoPalletForm" action="/scripts/buildOutPallet.php?id=<?php echo $picksheetid; ?>">
	<?php
		
		##########################
		$x = "SELECT * FROM `pickerItems` WHERE pickersheet_id='$picksheetid' GROUP BY product_id";
		$y = mysqli_query($conn, $x);
		
		$productids = '';
		
		while($row = mysqli_fetch_array($y)){ $productids .= ' id = ' . $row['product_id'] . ' ||'; }
		$productids = rtrim($productids," ||");
		##########################
		
		$productsQuery = "SELECT * FROM `product` WHERE $productids";
		$productsResult = mysqli_query($conn, $productsQuery);
		
		while($product = mysqli_fetch_array($productsResult)){
		$palletID = $product['pallet_id'];
			
		$productID = $product['id'];
		$cut_id = $product['cut_id']; 
		
		
		# PALLET START
		$xPallet = "SELECT * FROM `pallet` WHERE id='$palletID' LIMIT 1";
		$yPallet = mysqli_query($conn, $xPallet);
		$pallet = mysqli_fetch_array($yPallet);
		# PALLET END
		
		# COUNT START
		$xCount = "SELECT id FROM `pickerItems` WHERE pickersheet_id='$picksheetid' && product_id='$productID'";
		$yCount = mysqli_query($conn, $xCount);
		$numRequired = mysqli_num_rows($yCount);
		# COUNT END
	?>
	<div class="productGroup" id="topform<?php echo $product['id']; ?>" targetamount="<?php echo $numRequired; ?>">
	<?php 
		$temp_id = $product['cooling_id'];
		$smallestDate = $product['range_from'];
		$largestDate = $product['range_to'];

		$ubbb = $product['ubbb'];
		$smallestDate = $product['range_from'];
		$largestDate = $product['range_to'];

		$nationality_id = $product['nationality_id'];

		if($ubbb == 0){
			$ubtext = 'UB';
		}else if($ubbb == 1){
			$ubtext = 'BB';
		}else{
			$ubtext = 'N/A';
		}

	?>
		<div class="picksheetType">
			<table>
				<tr>
					<td>Intake ID</td>
					<td></td>
					<td>Pallet ID</td>
					<td><?php if($pallet['storage_location']){ echo $pallet['storage_location']; }?></td>
					<td colspan="3"></td>
					<td>Advised Weight</td>
				</tr>
				<tr>
					<td><?php echo intakeIDfromPalletID($product['pallet_id']); ?></td>
					<td></td>
					<td><?php echo $product['pallet_id']; ?></td>
					<td <?php if($temp_id == 1){ echo 'style="background:#c0392b;color:#fff;padding:5px;"'; }else { echo 'style="background:#2980b9;color:#fff;padding:5px;"'; } ?>>
						<?php echo getTemp($temp_id); ?>
					</td>
					<td style="padding-left:20px;padding-right:20px;"><?php echo getSpeciesFromCutID($product['cut_id']) . ' ' . getCut($product['cut_id']); ?></td>
					<td><?php echo getNationality($product['nationality_id']); ?></td>
					<td style="padding-right:20px;"><?php echo getBrand($product['brand_id']); ?></td>
 					<td><?php echo $product['akg']; ?></td>
				</tr>
			</table>
			
			<div class="rowEndContainer">
				<div class="numRequired"><?php echo $numRequired; ?></div>
				<?php if($product['weightnote'] != ''){ ?>
				<div class="weightcomment"><?php echo $product['weightnote'] . 'kg'; ?></div>
				<?php } ?>
			</div>
		</div>
		<input type="text" value="0" class="counter" id="counter-<?php echo $cut_id . '-'. $product['id']; ?>" style="display:none">
		<input type="text" value="<?php echo $numRequired; ?>" id="counter-<?php echo $cut_id . '-'. $product['id']; ?>-max" style="display:none">
		<div class="pickerSheetType_content" style="position:relative;">
 			<div class="picksheetPalletDetail" style="display:block">
				<div class="row">
				<?php
					if($product['akg'] != ''){
						$thisproductid = $product['id'];
						$w1 = "SELECT * FROM `weights` WHERE product_id='$thisproductid'";
						$w2 = mysqli_query($conn, $w1);
						
						$thisweight = mysqli_fetch_array($w2);
					?>
						<input type="text" name="dolavs[]" value="<?php echo $thisweight['id']; ?>" style="display:none;">
						<div style="padding:10px;"><input type="number" name="dolav_<?php echo $thisweight['id']; ?>"><span> / <?php echo $product['akg']; ?></span></div>
					<?php					
					}else{
					?>
					
					<?php
					$weightsQuery = "SELECT * FROM `weights` WHERE product_id='$productID' && status_id != '1' ORDER BY ABS(weight_gross) ASC";
 					$weightsResult = mysqli_query($conn, $weightsQuery);
					$numrows = mysqli_num_rows($weightsResult);
					
					if($numRequired >= 10 && $numrows != 0){ ?><div class="rowSelector" valselect='<?php echo $numRequired; ?>'><b>Select</b></div><?php }
					
					$count=0;
                    
					while($weights = mysqli_fetch_array($weightsResult)){
						$count++;
						
						$weightgross = $weights['weight_gross'];
						
						// $weightsQuery2 = "SELECT id FROM `weights` WHERE product_id='$productID' && weight_gross='$weightgross'";
						// $weightsResult2 = mysqli_query($conn, $weightsQuery2);
						// $weightsRow = mysqli_fetch_array($weightsResult2);
						// $ccount = mysqli_num_rows($weightsResult2);
						
						
                        $someString = getSpeciesFromCutID($product['cut_id']) . ' ' . getCut($product['cut_id']). ' ' . getNationality($product['nationality_id']) . ' ' . $numRequired;
                      
                    
                        if($pallet['grosspallet']){
                            
                            $netWeight = number_format($weights['weight_gross'], 2, '.', '');
                        ?>
                            
                            <div style="position:relative;padding:10px;">
                                <input type="hidden" value="<?php echo $weights['id']; ?>" name="grossids[]">
                                <input type="number" name="gross_<?php echo $weights['id']; ?>" value="0" max="<?php echo $netWeight; ?>"><div style="position:absolute;right:25px;top:12px;color:red;"> / <?php echo $netWeight; ?></div>
                            </div>
                            <?php             
                        }else{
                        ?>
                        <div class="weightbox" onclick="addStringName('<?php echo $someString; ?>'); addBoxIDtoList(<?php echo $weights['id']; ?>,<?php echo $product['cut_id']; ?>,<?php echo $product['id']; ?>,this,'<?php if($product['weightnote'] != ''){ echo 'true'; }else{ echo 'false'; } ?>');">
                        <?php echo $weights['weight_gross']; ?>
                        </div>
                        <?php
                        }
				?>
                       <?php
						if($count == 10){
							echo '</div><div class="row">';
							if($numRequired >= 10){ echo '<div class="rowSelector" valselect="' . $numRequired . '"><b>Select</b></div>'; }
							$count=0;
						}
					}
				?>
					<?php					
					}
				?>
			</div>
			
			<div class="customWeightContainer" style="display:none;"><input type="text" class="selectedValue" name="selectedValue"></div>
		</div>
	</div>
	</div>
	<?php } ?>
		  
	<br/><br/>
	
	<?php
		$outpalletQuery = "SELECT * FROM `palletsOut` WHERE pickersheet_id='$picksheetid'";
		$outpalletResult = mysqli_query($conn, $outpalletQuery);
		
		$outpalletCount = mysqli_num_rows($outpalletResult);
	?>
	
	<?php if($pickerSheet['completed'] != '1'){ ?>
	<div class="picksheet_controls" style="position:fixed;bottom:0px;right:10px;">
		 	<input type="text" id="weightids" name="weightids" style="display:none;">
			<input type="text" id="functype" name="functype" style="display:none;">
			<input type="text" id="newweightvals" name="newweightvals" style="display:none">
			<input type="submit" style="display:none;">
 			<input type="button" id="addToPallet" value="Add to Pallet" style="width:323px;height:34px;margin-bottom:10px;display:block;">
			<input type="button" id="nextPallet" value="Next Pallet" style="width:323px;height:34px;margin-bottom:10px;display: block;">
		</form>
		<br/>
		<form method="POST" action="/scripts/markPickerSheetCompleted.php?id=<?php echo $picksheetid; ?>" id="markCompletedForm">
			<?php if($outpalletCount == 0){ ?><div class="completepickwarning">Not ready</div><?php } ?>
			<input type="button" id="completeFormBtn" value="Completed" style="width:323px;height:34px;margin-bottom:10px;"<?php if($outpalletCount == 0){ ?> disabled <?php } ?>>
		</form>
	</div>
	
	<script>
		var globalReady = 0;
		var globalNeed = 1;
	</script>
	 
	<?php } ?>
	
	<br/><br/><br/>

        <div>
            <?php
                $outpalletQuery = "SELECT * FROM `palletsOut` WHERE pickersheet_id='$picksheetid'";
                $outpalletResult2 = mysqli_query($conn, $outpalletQuery);
                
                $outpalletCount = mysqli_num_rows($outpalletResult2);

                while($outpallet = mysqli_fetch_array($outpalletResult2)){
                    $weightids = explode(',', $outpallet['weight_ids']);
                    ?><h3 style="text-align:left;">Outgoing Pallet: <?php echo str_pad($outpallet['id'], 5, '0', STR_PAD_LEFT); ?></h3><?php

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
                        <script>
							var howManyWeGot = '<?php echo ${"globalProductCount" . $product['id']}; ?>';
							var target = $('#topform<?php echo $product['id']; ?>').attr('targetamount');
							console.log('How many we have: ' + howManyWeGot + ' ||||  target: ' + target + '  ('+ howManyWeGot +'/'+ target +')');
							
							if(parseInt(howManyWeGot) >= parseInt(target)){
								$('#topform<?php echo $product['id']; ?>').css('opacity','0.2');
								$('#topform<?php echo $product['id']; ?>').css("pointer-events", "none");
								globalReady++;

                                $('#counter-<?php echo $product['cut_id']; ?>-<?php echo $product['id']; ?>').val( $('#counter-<?php echo $product['cut_id']; ?>-<?php echo $product['id']; ?>-max').val());
							}
						</script>
                        <?php
                        $k = 0;

                        while($weight = mysqli_fetch_array($y2)){
                            
                            if($weight['weight_tear'] == $weight['weight_gross']){
                                $w = $weight['weight_gross'];
                            }else{
                                $w = $weight['weight_gross'] - $weight['weight_tear'];
                            }

                            $k = $k + $w;
                        }
                        ?><div><?php echo $count; ?> <?php echo getSpeciesFromCutID($product['cut_id']); ?> - <?php echo getCut($product['cut_id']); ?>
							<?php if($product['unit'] != 'PPC'){ ?>[<?php echo $k . $ext; $k = 0; ?>]</div> <?php } ?>
						<?php
                    }
                }
            ?>
        </div>
</main>
<div id="btm"></div>
<script>
	
	$('.picksheetType').click(function(){
		$(this).next('.pickerSheetType_content').toggle();
	});
	
	$('.rowSelector').click(function(){
		
		var maxselect = $(this).attr('valselect');
		
		var i = 0;
		maxselect++;
		
		$(this).parent().find('.weightbox').each(function(){
			i++;
			if(i < maxselect){
				$(this).trigger('click');
			}
		});
	});
		
	
	function addBoxIDtoList(id, cut_id, product_id, ele, customWeight, count = 1){
		
		// console.log('customWeight: ' + customWeight);
		
		if(customWeight == 'true'){
			$('.customWeightContainer').fadeIn();
		}
		
		if($(ele).hasClass('activeWeight')){
			$(ele).removeClass('activeWeight');
			var ids = $('#weightids').val();
			
			
			console.log('id: ' + ids);
			ids = ids.replace(id + ',','');
			ids = ids.replace(id + '-' + cut_id + ',', '');
			console.log('new-ids: ' + ids);
			
			
			
			$('#weightids').val(ids);
			
			
			var counter = $('#counter-' + cut_id + '-' + product_id).val();
			counter--;
			$('#counter-' + cut_id + '-' + product_id).val(counter);
			
			
		}else{
			
			var maxCounter = $('#counter-' + cut_id + '-' + product_id + '-max').val();
			var counter = $('#counter-' + cut_id + '-' + product_id).val();
			
			if(counter == maxCounter){
				alert('You have already selected ' + maxCounter + '/' + maxCounter + ' weights!');
			}else{
				counter++;
				$('#counter-' + cut_id + '-' + product_id).val(counter);
				
				$(ele).addClass('activeWeight');
				
				if(count > 1){
					for(var i=0;i<count;i++){
						var ids = $('#weightids').val();
						$('#weightids').val(ids + id + ',');
					}
				}else{
					var ids = $('#weightids').val();
					$('#weightids').val(ids + id + ',');
				}
				
			}
			
		}
	}
	
	$('#nextPallet').click(function(){
		$('#functype').val('NEW');
		
		var bigValue = '';
		
		$('.selectedValue').each(function(){
			var value = $(this).val();
 			bigValue += value + ',';
 		});
		
		$('#newweightvals').val(bigValue);
		
		
		$('.productGroup').each(function(){
			
			var numRequired = $(this).attr('targetamount');
			var currentCount = $(this).find('.picksheetType').find('.counter').val();
			
			if(currentCount > 0){
				if(currentCount > 0 && numRequired != currentCount){
					 swal({
						title: "Are you sure?",
						text: "You haven't selected all the required weights",
						icon: "warning",
						buttons: true,
						dangerMode: true,
					})
					.then((isConfirm) => {
						if (isConfirm) {
							$('#addtoPalletForm').submit();
						} else {
							 
						}
					});

				}else{
					$('#addtoPalletForm').submit();
				}
			}
			
 		});
		
		$('#addtoPalletForm').submit();
	});

		
	$('#addToPallet').click(function(){
		$('#functype').val('ADD');
		
		var bigValue = '';
		
		$('.selectedValue').each(function(){
			var value = $(this).val();
 			bigValue += value + ',';
 		});
		
		$('#newweightvals').val(bigValue);
		
		
		$('.productGroup').each(function(){
			
			var numRequired = $(this).attr('targetamount');
			var currentCount = $(this).find('.picksheetType').find('.counter').val();
			
			if(currentCount > 0){
				if(currentCount > 0 && numRequired != currentCount){
					 swal({
						title: "Are you sure?",
						text: "You haven't selected all the required weights",
						icon: "warning",
						buttons: true,
						dangerMode: true,
					})
					.then((isConfirm) => {
						if (isConfirm) {
							$('#addtoPalletForm').submit();
						} else {
							 
						}
					});

				}else{
					$('#addtoPalletForm').submit();
				}
			}
			
 		});
		
		$('#addtoPalletForm').submit();
		
	});
	globalReady++;
	$('#completeFormBtn').click(function(){
		 
		if(parseInt(globalReady) >= parseInt(globalNeed)){
			$('#markCompletedForm').submit();
			console.log('#completeFormBtn => ready!');
		}else{
			console.log('#completeFormBtn => not ready! [' + globalReady +'/' + globalNeed + ']');
 		}
	});
	

	function addStringName(data){
		$('#theRowName').append(data);
	}
	
	t = 0;
	
	if(t > 1){
		window.onbeforeunload = function(){
			// alert('test');
			return 1;
		};
	
	}

</script>
</body>
</html>