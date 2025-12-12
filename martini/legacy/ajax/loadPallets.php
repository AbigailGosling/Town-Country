	<?php
		include(__DIR__.'/../functions.php');
		$intake_id = request()->input('intake_id');
	?>

	<div class="clearfix"></div>
	<br/><br/>


	<div id="product_list">
		<div style="display:none;">
		<div id="printPreview" style="border:1px dashed grey;margin-bottom:10px;padding:10px;">
			<b>*temp*</b>
		</div>
		</div>
			<?php
			$pallets = getPalletsOnThisIntake2($intake_id);

			while($pallet = mysqli_fetch_array($pallets)){
				$pallet_id = $pallet['id'];

			?>
			<!-- START OF PRODUCT BLOCK !-->
			<div class="product" style="margin-bottom:20px;">
				<div class="title">

					<div class="title">
						<div onclick="palletDetail(<?php echo $pallet_id; ?>)" onselectstart="return false" style="font-size:12px;display:block;width:50%;float:left; cursor:pointer;padding-left: 4px;">
						Pallet No. <span>0000<?php echo $pallet_id; ?></span>
                        <?php if($pallet['user_id'] != ''){ ?>
                            <b>(created by: <?php echo getUsername($pallet['user_id']); ?>)</b>
                        <?php } ?>
                        <label><input type='checkbox' id="qc_hold<?php echo $pallet["id"]; ?>" onclick='qc_hold(<?php echo $pallet["id"]; ?> );' <?php if ($pallet['qc_hold']==1) echo "checked=checked" ?>>QC HOLD?</label>
                        <label><input type='checkbox' id="is_hidden<?php echo $pallet["id"]; ?>" onclick='is_hidden(<?php echo $pallet["id"]; ?> );' <?php if ($pallet['is_hidden']==1) echo "checked=checked" ?>>HIDDEN</label>
                        </div>
					</div>
					<div class="buttonsContainer">
						<i class="fa fa-print printICON" title="print pallet note" onclick="printPallet(<?php echo $intake_id; ?>,<?php echo $pallet_id; ?>);" aria-hidden="true"><span>print pallet note</span></i>
						<?php if($pallet['grosspallet'] == 0){ ?><i class="fa fa-plus" style="margin-left:30px;font-size:24px" onclick="openAddtoPallet(<?php echo $intake_id; ?>,<?php echo $pallet_id; ?>);" aria-hidden="true"></i><?php } ?>
						<a href="javascript:;" onclick="deleteRow(<?php echo $intake_id; ?>,<?php echo $pallet_id; ?>)">
							<i class="fa fa-trash" aria-hidden="true" style="margin-left:30px;font-size:24px;color:#000;"></i>
						</a>
					</div>
				</div>
				<div class="overview">
					<?php
 						$kk = prepareExecuteQuery("SELECT product.* FROM product INNER JOIN cuts ON product.cut_id=cuts.id && product.pallet_id=?",'i',[$pallet_id]);

						$totalWeight2 = (double)0;
						while($product = mysqli_fetch_array($kk)){
								$product_id = $product['id'];
								$cut_id = $product['cut_id'];
								$species_id = getSpeciesFromCut($cut_id);

								$unit = $product['unit'];

								if($unit == 'PP'){
									$xx = "SELECT * FROM `weights` WHERE product_id=?";
									$yy = prepareExecuteQuery($xx,'i',[$product_id]);
									$howManyCases = 0;
									while($row = mysqli_fetch_array($yy)){
										$howManyCases = $howManyCases + $row['weight_gross'];
									}
								}

							?>
							<input type="text" class="aWeight" value="<?php echo $totalWeightOfProduct; ?>" style="display:none;">
							<div style='font-size:11px;padding-top:10px;float:left;width:100%;padding-left: 7px;'>

								<?php
									$xk = "SELECT * FROM `weights` WHERE product_id=?";
									$yk = prepareExecuteQuery($xk,'i',[$product_id]);
									$ykRow = mysqli_fetch_array($yk);

									if($pallet['grosspallet'] == 1){ # Gross Tare

										echo '[GT] ' . $pallet['number_of_cartons'] . ' Cartons of ';
										echo getSpecies($species_id);
										echo ' ';
										echo getCut($cut_id);
										$weightthing = weightFromProductID($product_id);
										echo ' [' . number_format($weightthing, 3, '.', '') .'KG]';
										if($product['product_temp']){ echo ' ['. $product['product_temp'] . '&deg;C]'; }

                                    }else{ # This is not a Gross Tare

                                        if($product['akg'] == ''){
                                            echo $count = mysqli_num_rows($yk) . ' ';
                                        }
										echo getSpecies($species_id);
										echo ' ';
										echo getCut($cut_id);


										(double)$totalWeight2 = (double)$totalWeight2 + (double)$ykRow['weight_gross'];
										$weightthing = weightFromProductID($product_id);

										if($unit == 'PP'){
											echo '['.$howManyCases . ' Cases]';
										}else if($unit == 'PPC'){
											echo ' [PPC]';
										}else{
											echo ' [' . number_format($weightthing, 3, '.', '') .'KG]';
										}

										if($product['product_temp']){ echo ' ['. $product['product_temp'] . '&deg;C]'; }

                                        if($product['note_units'] != ''){ echo '.. ('. $product['note_units'] .' cases)'; }
                                        if($product['akg'] != ''){ echo ' ['. $product['quantity'] . '  Cases Advised KG] '; }
									}

								?>
							</div>
							<?php

							$totalWeight2 = 0;
						}
					?>
				</div>
				<div class="palletDetail-<?php echo $pallet_id;?>" <?php if($ykRow['pallet_tare'] == ''){ echo 'style="display:none;"'; } ?>>
					<br/>
					<b style="font-size: 12px;">Pallet Details</b>
					<br/>
					<table border="0" style="border:1px dashed grey">

						<?php

							$x = "SELECT * FROM `product` WHERE pallet_id=?"; // This was group by cut
							$y = prepareExecuteQuery($x,'i',[$pallet_id]);

							$counter = 0;

							while($product2 = mysqli_fetch_array($y)){
							$product_id = $product2['id'];
							$cut_id = $product2['cut_id'];

							$unit = $product2['unit'];

							if($unit == 'PP'){

								$xx = "SELECT id FROM `weights` WHERE product_id=? LIMIT 1";
								$yy = prepareExecuteQuery($xx,'i',[$product_id]);
								$row = mysqli_fetch_array($yy);

								$howManyCases = $row['weight_gross'];
							}



							$productsX = "SELECT * FROM `weights` WHERE product_id=?";
							$productsY = prepareExecuteQuery($productsX,'i',[$product_id]);

							$numWeightsOfProduct = mysqli_num_rows($productsY);

							$weights = mysqli_fetch_array($productsY);
								$weightID = $weights['id'];
								$counter++;
								$species_id = getSpeciesFromCut($product2['cut_id']);
								if($counter == 1){ ?>
								<thead>
									<tr>
										<th>product id</th>
										<th>Quantity</th>
										<th>species</th>
										<th>cut</th>
										<th>brand</th>
										<th>Fresh/Frozen</th>
										<th>Type</th>
										<th>From</th>
										<th>To</th>
									</tr>
								</thead>
								<?php } ?>
								<tbody>
								<tr>
									<td><?php echo $product2['id']; ?></td>
									<td><?php echo $numWeightsOfProduct; ?></td>
									<td><?php echo getSpecies($species_id); ?></td>
									<td><?php echo getCut($product2['cut_id']); ?></td>
									<td><?php echo getBrand($product2['brand_id']); ?></td>
									<td><?php echo getTemp($product2['cooling_id']); ?></td>
									<td><?php
											$types = Array('UB','BB','N/A','PB','EX');

											echo $types[$product2['ubbb']];

										?></td>
									<td><?php if($product2['range_from'] != ''){ echo $product2['range_from']; }else { echo 'N/A'; } ?></td>
									<td><?php if($product2['range_to'] != ''){ echo ($product2['range_extension']!= null && $product2['range_extension']!= '')?$product2['range_extension']:$product2['range_to']; }else { echo 'N/A'; } ?></td>
									<?php
										$product_id = $product2['id'];

										$xR = "SELECT * FROM `weights` WHERE product_id=?";
										$yR = prepareExecuteQuery($xR,'i',[$product_id]);
										$boxR= mysqli_fetch_array($yR);

										if($unit == 'PPC'){
											?><td>PPC</td><?php
										}else if($boxR['weight_gross'] != '' && $boxR['weight_gross'] != $boxR['weight_tear']){
											?>
											<td><?php echo $boxR['weight_gross']; ?></td>
											<td><?php echo $boxR['weight_tear']; ?></td>
											<?php
										}else{
										?>
										<td><?php if($unit == 'PP'){ echo $howManyCases . ' Cases'; } else{ echo number_format(weightFromProductID($product_id), 3, '.', ''); } ?></td>
										<?php
										}
									?>
									<td>
										<a href="javascript:;" onclick="editWeight('<?php echo $intake_id; ?>','<?php echo $pallet_id; ?>','<?php echo $product_id; ?>','<?php echo $weightID; ?>')">
											<i class="fa fa-pencil" aria-hidden="true" style="font-size:18px;color:#000;"></i>
										</a>
										<a href="scripts/deleteProduct.php?productid=<?php echo $product_id; ?>&intakeid=<?php echo $intake_id; ?>">
											<i class="fa fa-trash" aria-hidden="true" style="font-size:18px;color:#000;"></i>
										</a>
									</td>

								</tr>
								<?php if($pallet['grosspallet'] == 1){ ?>
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
													<td align="right"><?php echo number_format(weightFromProductID($product_id), 3, '.', ''); ?></td>
												</tr>
											</table>
 										</div>
									</td>
								</tr>
								<tr>
								<td colspan="11" style="padding-bottom:0px;">
								<div style="display:flex;">
								<?php

									$productID = $product2['id'];

									$xWeights = "SELECT * FROM `weights` WHERE product_id = ?";
									$yWeights = prepareExecuteQuery($xWeights,'i',[$productID]);

									while($w = mysqli_fetch_array($yWeights)){
										?><div class="weightbox" style="min-width:90px;"><?php echo number_format($w['weight_gross'], 3, '.', ''); ?>kg </div><?php
									}
								?></div></td></tr><?php
								}




							}
						?>
						</tbody>
					</table>
				</div>

			</div>
			<!-- END OF PRODUCT BLOCK !-->
			<?php } ?>
	</div>
