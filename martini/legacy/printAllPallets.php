<?php
	include('includes/frontHeader.php');
	
	$id = request('intake_id');
	$intake_id = request('intake_id');	
	
	$types = Array('UB','BB','N/A','PB','EX');
	
	$intake = getIntake($id);
	
	$supplier = getSupplier($intake['supplier_id']);
?>	
<main>
	<a style="position:absolute;left:0px;top:20px;" class="printhide" href="intake.php?id=<?php echo $intake_id; ?>">Back</a>
	
	<h1 style="font-family: 'OpenSans_Semibold' !important;font-weight: 700;color: #000;padding-bottom:20px;font-size: 26px;text-align: left;"><?php if($intake['returned'] == '1'){ echo 'Returned '; } ?>ALL PALLETS ON INTAKE <?php echo $intake_id; ?></h1>
	<div class="overview">
	
		<div class="overview_block">
			<label>Intake ID</label>
			<?php echo $intake['id']; ?>
		</div>
		
		 

		<?php if($intake['returned'] == 1){ ?>
		<div class="overview_block">
			<label>Customer</label>
			<?php
				$customer = getCustomer($intake['supplier_id']);
			?>
			<?php echo $customer['businessname']; ?>
		</div>
		<?php }else{ ?>
			<div class="overview_block">
				<label>Supplier</label>
				<?php echo supplierName($intake['supplier_id']); ?>
			</div>
		<?php } ?>

		
		<div class="overview_block">
			<label>Vehicle Registration</label>
			<span style="text-transform:uppercase;"><?php echo $intake['vehicle_reg']; ?></span>
		</div>
		
		<div class="overview_block">
			<label>Date Received</label>
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
			$yPallets = prepareExecuteQuery("SELECT id FROM `pallet` WHERE intake_id=?",'i',[$intake_id]);
			while($pallet = mysqli_fetch_array($yPallets)){ array_push($PALLET_IDS, $pallet['id']); }
			$PALLET_IDS = implode(',',$PALLET_IDS);

			$yProductsGrouped = prepareExecuteQuery( "SELECT * FROM product WHERE pallet_id IN ($PALLET_IDS) GROUP BY cut_id");

			while($productGrouped = mysqli_fetch_array($yProductsGrouped)){
				$PRODUCT_IDS = [];
				$this_cutid = $productGrouped['cut_id'];

				# Get other product ids in these pallets with the same cut
				$yProducts = prepareExecuteQuery("SELECT id FROM product WHERE pallet_id IN ($PALLET_IDS) && cut_id='$this_cutid'");
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
				<td align="right">
					<?php
						if($productGrouped['unit'] == 'PP'){
							echo $totalCountOfCut . ' Cases';
						}else if($productGrouped['unit'] == 'PPC'){
							echo 'PPC';
						}else{
							echo number_format($totalWeightOfCut, 3, '.', '') . 'kg';
						}
					?>
				</td>
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
				$y_pallets = prepareExecuteQuery("SELECT id FROM `pallet` WHERE intake_id=? ORDER BY id ASC",'i',[$intake_id]);
				while($pallet = mysqli_fetch_array($y_pallets)){
			?>
				<div class="product" style="padding-bottom:0px;">
                <br/><h2 style="margin:0;">Pallet <?php echo $pallet['id']; ?></h2>
				 	<div class="overview" style="display:block;">
						<?php
							$pallet_id = $pallet['id'];
							$product_y = prepareExecuteQuery("SELECT * FROM `product` WHERE pallet_id='$pallet_id'");

							while($product = mysqli_fetch_array($product_y)){
								$product_id = $product['id'];
								$cut_id = $product['cut_id'];
								$species_id = getSpeciesFromCut($cut_id);
								$this_weight = weightFromProductIDArray([$product_id]);
								
								$pallet = getPallet($pallet_id);

								?>							
							<div style='font-size:11px;padding-top:10px;float:left;width:100%;padding-left: 7px;'>
								<?php 
									
									if($pallet['grosspallet'] == 1){
										echo '[GT] ' . $pallet['number_of_cartons'] . ' Cartons of ';
									}
								
									$xk = "SELECT * FROM `weights` WHERE product_id='$product_id'";
									$yk = prepareExecuteQuery($xk);
									$ykRow = mysqli_fetch_array($yk);
									
									if($pallet['grosspallet'] == 0){
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
										if($product['unit'] == 'PP'){
											echo '[' . $totalCountOfCut . ' Cases ]';
										}else if($product['unit'] == 'PPC'){
											echo '[PPC]';
										}else{
											$weightthing = weightFromProductIDArray([$product['id']]);
											echo '<b>[' . number_format($weightthing, 3, '.', '') . 'kg]</b>';
										}
										 
                                    }
                                    
                                ?>
								<?php echo '[' . getTemp($product['cooling_id']) .']'; ?>
								<?php echo '[<b>' . $types[$product['ubbb']] .'</b>]'; ?>
								<?php if($product['range_from']) { echo '<span style="color:grey;">(' . $product['range_from'] . ' - '; ?>
								<?php echo $product['range_to'] . ')</span>'; } ?>

								<?php if($product['unit'] == 'PPC'){ ?>
									<br/><Br/>
								<?php }else{ ?>
								<div class="picksheetPalletDetail" style="padding:0px;display: flex;flex-wrap: wrap">
								<?php
									$weightValue = weightFromProductID($product_id);
									$weightsX = "SELECT * FROM `weights` WHERE product_id='$product_id'";
									$weightsY = prepareExecuteQuery($weightsX);
									
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
								<?php
									if($pallet['grosspallet'] == 1){
									?>
										<table width="100%">
										<tr>
											<td colspan="11" style="padding-bottom:0px;">
												<div style="text-align:left;float:right;">
													<table border="1" style="background:#cacaca;">
														<tr>
															<td align="left"><b>Gross Weight: </b></td>
															<td align="right"><?php echo number_format($pallet['gross_weight'], 2, '.', ''); ?></td>
														
															<td align="left"><b>Pallet Tare: </b></td>
															<td align="right"><?php echo number_format($pallet['pallet_tare'], 2, '.', ''); ?></td>
														
															<td align="left"><b>Tare per carton: </b></td>
															<td align="right"><?php echo number_format($pallet['tare_per_carton'], 2, '.', ''); ?></td>
														
															<td align="left"><b>No of cartons: </b></td>
															<td align="right"><?php echo number_format($pallet['number_of_cartons'], 2, '.', ''); ?></td>
														
															<td align="left"><b>Net KG: </b></td>
															<td align="right"><?php echo number_format(weightFromProductID($product_id), 2, '.', ''); ?></td>
														</tr>
													</table>
												</div>
											</td>
										</tr>
										</table>
									<?php
									}	
								?>
								</div>
								<?php } ?>
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