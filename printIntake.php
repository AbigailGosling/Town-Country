<?php
	include('includes/frontHeader.php');
	
	$id = $_GET['intake_id'];
	$intake_id = $_GET['intake_id'];	
	
	$types = Array('UB','BB','N/A','PB','EX');
	
	$intake = getIntake($id);
	
	$supplier = getSupplier($intake['supplier_id']);
?>	
<main>
	<a style="position:absolute;left:0px;top:20px;" class="printhide" href="intake.php?id=<?php echo $intake_id; ?>">Back</a>
	
	<h1 style="font-family: 'OpenSans_Semibold' !important;font-weight: 700;color: #000;padding-bottom:20px;font-size: 26px;text-align: left;">Intake Print Form</h1>
	<div class="overview">
	
		<div class="overview_block">
			<label>Intake ID</label>
			<?php echo $intake['id']; ?>
		</div>
		
		<div class="overview_block">
			<label>Supplier ID</label>
			<?php echo $intake['supplier_id']; ?>
			<?php echo supplierName($intake['supplier_id']); ?>
		</div>
		
		<div class="overview_block">
			<label>Vehicle Registration</label>
			<span style="text-transform:uppercase;"><?php echo $intake['vehicle_reg']; ?></span>
		</div>
		
		<div class="overview_block">
			<label>Date Recieved</label>
			<?php 
				$date_received2 = str_replace('/', '-', $intake['date_received']);
				$date_received2 = date('d/m/Y', strtotime($date_received2));
				
				echo $date_received2;
			?>
		</div>
		
		<div class="overview_block">
				<div style="width:50%;display:inline-block;float:left;">
				<label>Vehicle Temp</label>
				<?php echo $intake['vehicle_temperature']; ?>&deg;C
			</div>
			<div style="width:50%;display:inline-block;float:left;">
				<label>Product Temp</label>
				<?php echo $intake['product_temperature']; ?>&deg;C
			</div>
		</div>
		
		<div class="overview_block">
			<label>Delivery Note Number</label>
			<?php echo $intake['delivery_note_number']; ?>
		</div>
		
		<div class="overview_block">
			<label>Staff Name</label>
			<?php 
				if(is_numeric($intake['user_id'])){
					echo getUsername($intake['user_id']);
				}else{
					echo $intake['user_id'];
				}					
			?>
		</div>
		
		<br/><br/><br/>
		<div class="overview_block">
			<label>Total Intake Weight</label>
			<div id="intakeTotalWeight">0</div>
		</div>
		<?php if($intake['security_id'] != ''){ ?>
		<div class="overview_block">
			<div>
				<label>Security</label>
				<?php echo getSecurityName($intake['security_id']); ?>
			</div>
		</div>
		<?php } ?>
		
		<div style="clear:both;"></div>
	</div>
	<div class="clearfix"></div>
	<br/><br/>
	<table border="1" cellpadding="5" style="display:inline-block;">
		<tr>
			<td colspan="4" align="center"><b>Overview</b></td>
		</tr>
		<tr>
			<th>Species</th>
			<th>Cut</th>
			<th>No. Cases</th>
			<th>Total Weight</th>
		</tr>
		<?php
			$totalWeight = 0;
			$totalCount = 0;

			$PALLET_IDS = [];

			# Get all pallet ids & store in array
			$yPallets = mysqli_query($conn, "SELECT id FROM `pallet` WHERE intake_id='$intake_id'");
			while($pallet = mysqli_fetch_array($yPallets)){ array_push($PALLET_IDS, $pallet['id']); }
			$PALLET_IDS = implode($PALLET_IDS, ',');

			$yProductsGrouped = mysqli_query($conn, "SELECT * FROM product WHERE pallet_id IN ($PALLET_IDS) GROUP BY cut_id");

			while($productGrouped = mysqli_fetch_array($yProductsGrouped)){
				$PRODUCT_IDS = [];
				$this_cutid = $productGrouped['cut_id'];

				# Get other product ids in these pallets with the same cut
				$yProducts = mysqli_query($conn, "SELECT id FROM product WHERE pallet_id IN ($PALLET_IDS) && cut_id='$this_cutid'");
				while($product = mysqli_fetch_array($yProducts)){ array_push($PRODUCT_IDS, $product['id']); }

				$totalCountOfCut = countFromProductIDArray($PRODUCT_IDS);
				$totalWeightOfCut = weightFromProductIDArray($PRODUCT_IDS);

				$totalCount += $totalCountOfCut;
				$totalWeight += $totalWeightOfCut;
			?>
			<tr>
				<td><?php echo getSpeciesFromCutID($productGrouped['cut_id']); ?></td>
				<td><?php echo getCut($productGrouped['cut_id']);?></td>
				<td><?php echo $totalCountOfCut; ?></td>
				<td align="right"><?php echo number_format($totalWeightOfCut, 3, '.', ''); ?>kg</td>
			</tr>
			<?php
			}
		?>
		<tr>
			<td colspan="2">Total</td>
			<td><?php echo $totalCount; ?></td>
			<td align="right"><?php echo number_format($totalWeight, 3, '.', ''); ?>kg</td>
			<script> $('#intakeTotalWeight').text('<?php echo number_format($totalWeight, 3, '.', ''); ?>kg'); </script>
		</tr>
	</table>

	<div id="product_list">
 			<?php
				$y_pallets = mysqli_query($conn, "SELECT id FROM `pallet` WHERE intake_id='$intake_id'");
				while($pallet = mysqli_fetch_array($y_pallets)){
			?>
				<div class="product" style="padding-bottom:0px;">
				 	<div class="overview" style="display:block;">
						<?php
							$pallet_id = $pallet['id'];
							$product_y = mysqli_query($conn, "SELECT * FROM `product` WHERE pallet_id='$pallet_id'");

							while($product = mysqli_fetch_array($product_y)){
								$product_id = $product['id'];
								$cut_id = $product['cut_id'];
								$species_id = getSpeciesFromCut($cut_id);
								$this_weight = weightFromProductIDArray([$product_id]);
								
								?>							
							<div style='font-size:11px;padding-top:10px;float:left;width:100%;padding-left: 7px;'>
								<?php 
									
									if($row['grosspallet'] == 1){
										echo '[GT] ' . $row['number_of_cartons'] . ' Cartons of ';
									}
								
									$xk = "SELECT * FROM `weights` WHERE product_id='$product_id'";
									$yk = mysqli_query($conn, $xk);
									$ykRow = mysqli_fetch_array($yk);
									
									if($row['grosspallet'] == 0){
                                        if($product['akg'] == ''){
                                            echo $count = mysqli_num_rows($yk);
                                            
                                            if($product['unit'] =='C'){
                                                echo ' Cases';
                                            }else if($product['unit'] == 'P'){
                                                echo ' Pallet';
                                            }else if($product['unit'] =='PP'){
                                                echo ' Packet';
                                            }else if ($product['unit'] == 'KG'){
                                                echo ' Kilo';
                                            }
                                        }
									}
									  
									 
								?>
								<?php echo '<b>' . getSpecies($species_id); ?> <?php echo getCut($cut_id) . "</b>"; ?>
								<?php
								
                                    if($product['akg'] != ''){
                                        echo ' ['. $product['quantity'] . '  Cases Advised KG] ';
                                    }else{
										$weightthing = weightFromProductIDArray([$product['id']]);
										echo '<b>[' . number_format($weightthing, 3, '.', '') . 'kg]</b>';
                                    }
                                    
                                ?>
								<?php echo '[' . getTemp($product['cooling_id']) .']'; ?>
								<?php echo '[Pallet ID: <b>' . $pallet_id .'</b>]'; ?>
								<?php echo '[<b>' . $types[$product['ubbb']] .'</b>]'; ?>
								<?php if($product['range_from']) { echo '<span style="color:grey;">(' . $product['range_from'] . ' - '; ?>
								<?php echo $product['range_to'] . ')</span>'; } ?>
								<div class="picksheetPalletDetail" style="padding:0px;display: flex;flex-wrap: wrap">
								<?php
									$weightValue = weightFromProductID($product_id);
									$weightsX = "SELECT * FROM `weights` WHERE product_id='$product_id'";
									$weightsY = mysqli_query($conn, $weightsX);
									
									while($weights = mysqli_fetch_array($weightsY)){
									?>
									<?php
											if($weights['weight_tear'] == $weights['weight_gross']){
												$w = $weights['weight_gross'];
											}else{
												$w = $weights['weight_gross'] - $weights['weight_tear'];
											}
										?>
										<div class="weightbox" <?php if($w == 1){ ?> style="margin: 2px;width: 12px;"<?php }?>>
											<?php echo $w; ?>
										</div>
									<?php
									}
								?>
								</div>
							</div>
								<?php
							}							
						?>
					</div>
				</div>
				<?php		
				}

 			?>
	</div>
	</main>
<script>

	$(document).ready(function(){		
		$('.printhide').hide();
		print();
	
	});
	
	function printCompleted(){
        $('.printhide').show();
	}
	
 
</script>
</body>
</html>