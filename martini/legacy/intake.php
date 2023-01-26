<?php
	include('includes/frontHeader.php');
    ini_set('memory_limit','15M'); //this might kill the process - keep in mind
	$id = request('id');
	$intake_id = request('id');	
	
	$intake = getIntake($id);
	
	$userX = "SELECT * FROM `users` WHERE id=?";
	$userY = prepareExecuteQuery($userX,'i',[$userid]);
	$user = mysqli_fetch_array($userY);
	
	$supplier = getSupplier($intake['supplier_id']);
	
	if(request('hide') == 'true'){
		$pallet_id = request('pallet_id');
		
		$x1 = "UPDATE `product` SET `status`='1' WHERE pallet_id=?";
		$y1 = prepareExecuteQuery($x1,'i',[$pallet_id]);
		
		$od = request('id');
		
		header('location: intake.php?id='.$od);
	}
	
	if(request('savePrices') == 'true'){
		$productids = request('productid');
		$price = request('price');
		$cost = request('cost');
		
		$size = sizeof($productids);
		
		$intakeid = request('intakeid');
		
		for($i=0;$i<$size;$i++){
			$product_id = "(" . $mysqli->real_escape_string($productids[$i]) . ")"; 
			$cost = sprintf('%0.2f', request('cost')[$i]);
			$price = sprintf('%0.2f', request('price')[$i]);
			
			$weightnote = request('weightnote')[$i];
			if($product_id != ''){
				$x = "UPDATE `product` SET cost=?, price=?, weightnote=? WHERE id IN $product_id";
 				$y = prepareExecuteQuery($x,'sss',[$cost,$price,$weightnote]);
				 loggedDataChange('pallet',$pallet_id,$weightnote);
			}
		}

 	}
	
?>

<div id="top">
	<a href="menu.php" id="menu">MENU</a>
	<a href="logout.php" id="logout">LOGOUT</a>
</div>

<script type="text/javascript">
	
	function printPallet(intake_id, pallet_id){
		var x = "/printContent.php?intake_id=" + intake_id + "&pallet_id=" + pallet_id;
		
        window.open(x, '_blank');
	}
	
</script>
<style type="text/css">
	.pricetype{
		width: 80px;
		height: 30px;
	}
	
	.pricebox{
		outline: none;
		width: 60px;
		height: 24px;
		padding-left: 10px;
		padding-right: 10px;
	}
	  
	.printICON span{
		font-size:18px;
		text-transform:uppercase;
		font-weight:700;
		padding-left:10px;
	}
	
	.printICON{
		font-size:24px !important;
	}

	.printICON:active{
		color:#3faddd;
	}
	a{
		text-decoration:none !important;
	}
</style>
<main class="int">

	<a href="javascript:;" onclick="window.history.back();" class="backbtn">< Back</a>
		
	<form style="float:right;padding-bottom:10px;display:none;" method="POST" action="markIntakeAs.php">
		<input type="text" name="intakeid" value="<?php echo $intake_id; ?>" style="display:none;">
		<select name="state">
            <option value="0">Mark as unsold</option>
			<option value="1">Mark as sold</option>
		</select>
		
		<input type="submit" value="SAVE">
	</form>
	
	<div class="overview">
		<div class="overview_block">
			<label>Intake ID</label>
			<?php echo $intake['id']; ?>
		</div>
		
		<?php if($intake['returned'] == 1){ ?>
		<div class="overview_block">
			<label>Customer</label>
			<form id="changeIntakeSupplierForm" method="post" action="/scripts/changeIntakeSupplier.php">
                <input type="hidden" name="intake_id" value="<?php echo $intake['id']; ?>">
                <select id="changeIntakeSupplier" style="height:30px;outline:none;border:0px;width: 100%;" name="supplier_id">
                    <?php
                        $y = prepareExecuteQuery("SELECT * FROM `customers` WHERE `disabled`=0 OR `id` = ? ORDER BY `businessname` ASC",'s',[$intake['supplier_id']]);

                        while($customer = mysqli_fetch_array($y)){
                        ?><option value="<?php echo $customer['id']; ?>" <?php if($customer['id'] == $intake['supplier_id']){ echo 'selected'; } ?>><?php echo $customer['businessname']; ?></option>
                        <?php }
                    ?>
                </select>   
            </form>

		</div>
		
		<?php }else{ ?>
		<div class="overview_block">
            <label>Supplier</label>
            <form id="changeIntakeSupplierForm" method="post" action="/scripts/changeIntakeSupplier.php">
                <input type="hidden" name="intake_id" value="<?php echo $intake['id']; ?>">
                <select id="changeIntakeSupplier" style="height:30px;outline:none;border:0px;width: 100%;" name="supplier_id">
                    <?php
                        $y = prepareExecuteQuery("SELECT * FROM `supplier` ORDER BY `name` ASC");

                        while($supplier = mysqli_fetch_array($y)){
                        ?><option value="<?php echo $supplier['id']; ?>" <?php if($supplier['id'] == $intake['supplier_id']){ echo 'selected'; } ?>><?php echo $supplier['name']; ?></option>
                        <?php }
                    ?>
                </select>   
            </form>
		</div>		
		<?php } ?>

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
			<label>Vehicle Temp</label>
			<?php echo $intake['vehicle_temperature']; ?>&deg;C
		</div>
		
		<div class="overview_block">
			<label>Delivery Note Number</label>
			<?php if($intake['returned'] == 1){ ?>
			<form method="POST" action="/scripts/changeIntakeDeliveryNoteNumber.php" class="flex">
				<input type="hidden" name="intake_id" value="<?php echo $intake['id']; ?>">
				<input type="text" name="delivery_note_number" value="<?php echo $intake['delivery_note_number']; ?>" style="width:140px;">
				<input type="submit" value="Save">
			</form>
			<?php }else{
				echo $intake['delivery_note_number'];
			} ?>
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
			<div>
				<label>Total Intake Weight</label>
				<div id="intakeTotalWeightA">0</div>
			</div>
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
	<br/><br/>
	
	<div style="display:flex;justify-content:space-between;flex-wrap:wrap;">
	<div style="width:45%;padding:15px;border: 1px solid grey;">
		<h2 style="font-size: 20px;">Intake Notes</h2>
		<form method="POST" action="/scripts/saveIntakeNotes.php" enctype="multipart/form-data">
			<input type="text" name="intakeid" value="<?php echo $intake['id']; ?>" style="display:none;">
			
			<label>Notes</label><br/>
			<textarea class="intakeNotes" name="notes"><?php echo $intake['notes']; ?></textarea>
			
			<br/><br/>
			<input type="submit" value="Save">

		</form>
	</div>
    
    <?php
        $x = "SELECT `id` FROM `intakeDocs` WHERE `intakeid`=?";
		$y = prepareExecuteQuery($x,'i',[$intake_id]);
        $count = mysqli_num_rows($y);
        
        if($count < 5){ 
    ?>
	<div style="width:45%;padding:15px;border: 1px solid grey;">
		<h2 style="font-size: 20px;">Add Document</h2>
		<form method="POST" action="/scripts/addImageToIntake.php" enctype="multipart/form-data">
			<input type="text" name="intakeid" value="<?php echo $intake['id']; ?>" style="display:none;">
			
			<label>Document Name</label><br/>
			<input type="text" name="name" required>
			<br/><br/>
			
			<label>Image</label><br/>
			<input type="file" name="dfile" style="border: 1px solid #cacaca;" required><br/>
			
			<br/><br/>
			<input type="submit" value="Attach to intake">
			 
		</form>
    </div>
    <?php } ?>
    <?php
        $x = "SELECT * FROM `intakeDocs` WHERE intakeid=?";
		$y = prepareExecuteQuery($x,'s',[$intake_id]);
		$count = mysqli_num_rows($y);
		
		if($count > 0){
			?><div style="padding: 15px;border: 1px solid grey;<?php if($count < 5){ echo 'width:100%;margin-top:40px;'; } else{ echo 'width:45%;'; }?>">
			<h2 style="font-size:20px;">Intake Documents</h2><?php
			while($row = mysqli_fetch_array($y)){
			?>
			<a href="./scripts/deleteIntakeDoc.php?intakeid=<?php echo $intake_id; ?>&docid=<?php echo $row['id']; ?>">
				<i class="fa fa-trash" aria-hidden="true" style="text-decoration:none;font-size:24px;color:#000;"></i>
			</a> &nbsp;&nbsp;&nbsp; <a href="./docs/<?php echo $row['dfile']; ?>" target="_blank"><?php echo $row['name']; ?></a><br/><br/>
			<?php
			}
			
			echo '</div>';
		}
	?>
	</div>
	<br/><br/>
	<?php
		if($intake['purchase_id'] != ''){
	?>
	<div style="padding:10px;padding-left: 10px;border: 1px solid grey;position:relative;">
	
		<a href="<?php echo $domain; ?>createPurchase.php?id=<?php echo $intake['purchase_id']; ?>" class="viewpurchase">View Purchase</a>
		<h2 style="font-size: 20px;">Purchase Notes</h2>
		<?php
			$purchase_id = $intake['purchase_id'];
			
			$x = "SELECT * FROM purchase_form WHERE id=?";
			$y = prepareExecuteQuery($x,'i',[$purchase_id]);
			
			$row = mysqli_fetch_array($y);
		?>
		<b>Comments</b>
		<p style="margin-top: 4px;"><?php echo $row['purchase_comments']; ?></p>
		<ul style="padding-left:20px;">
		<?php
			
			$species = explode('|', $row['species']);
			$cuts = explode('|', $row['cut']);
			$units = explode('|', $row['units']);
			
			$size = sizeof($species);
			
			for($i=0;$i<$size;$i++){
			?>
			<li><?php echo ucfirst(strtolower($species[$i] . ': ' . $cuts[$i])); ?></li>
			<?php
			}
		?>
		</ul>
	</div>
	<?php } ?>
	
	<br/>
	<?php
		$x = "SELECT * FROM `users` WHERE id=?";
		$y = prepareExecuteQuery($x,'i',[$userid]);
		$user = mysqli_fetch_array($y);
		
		
		if($user['view_intake_prices'] == 1){
	?>
	<form method="POST" action="intake.php?savePrices=true&id=<?php echo $intake_id; ?>">
	<input type="text" name="intakeid" value="<?php echo $intake_id; ?>" style="display:none;">
		<table border="1" cellpadding="5" width="100%">
			<tr>
				<td colspan="8" align="center"><b>Overview</b></td>
			</tr>
			<tr>
				<th style="background:#3faddd;">Species</th>
				<th style="background:#3faddd;">Cut</th>
				<th style="background:#3faddd;">Cases</th>
				<th style="background:#3faddd;">Comments</th>
 				<th style="background:#3faddd;">Total Weight</th>
 				<th style="background:#3faddd;">Cost</th>
				<th style="background:#3faddd;">RRP</th>
			</tr>
			<?php
				
				$x = "SELECT id FROM `pallet` WHERE intake_id=?";
				$y = prepareExecuteQuery($x,'i',[$intake_id]);
				$countPallets = mysqli_num_rows($y);
				
				$qPallets = '';
                 
				while($row = mysqli_fetch_array($y)){
					$rowid = $row['id'];
					
					$qPallets .= " pallet_id = '$rowid' OR";
 				}
				
				$qPallets = substr($qPallets, 0, -2);
 				
				if($countPallets >= 1){
					$x = "SELECT * FROM product WHERE " . $qPallets . " GROUP BY cut_id";
				}else{
					$x = "SELECT * FROM product WHERE id = 0";
				}
				
				$y = prepareExecuteQuery($x);
				$count = mysqli_num_rows($y);
				
				$totalCases = 0;
				$totalWeight = 0;
				$totalWeight = 0;
				$c = 0;
				
				while($row = mysqli_fetch_array($y)){
					$c++;
					$product_id = $row['id'];
					
					$rowcutid = $row['cut_id'];
					
					if($countPallets >= 1){
						$x2 = "SELECT id FROM product WHERE (" . $qPallets . ") AND cut_id='$rowcutid'";
					}else{
						// ??: What does this do? 
						$x2 = "SELECT id FROM product WHERE id = 0"; 
					}
				
					$y2 = prepareExecuteQuery($x2);
					
					 
					$weightthing = 0;

					// used as a reference for updating the costs
					$productIDs = [];

					while($row2 = mysqli_fetch_array($y2)){
						$rowid = $row2['id'];
						$productIDs[] = $rowid;
						$weightthing += weightFromProductID($rowid);
						$totalWeight += weightFromProductID($rowid);
						$qAppend2 .= " product_id = '$rowid' OR";
					}
					 
					$qAppend2 = substr($qAppend2, 0, -2);
					$count2 = mysqli_num_rows($y2);
					
				?>
				<tr>
					<td><?php echo getSpeciesFromCutID($row['cut_id']); ?></td>
					<td><?php echo getCut($row['cut_id']);?></td>
					<td align="center"><?php 
							$cut_id = $row['cut_id'];
							$xk = "SELECT id FROM `weights` WHERE weight_gross > 0 AND (" . $qAppend2 . ")";
							$yk = prepareExecuteQuery($xk);
              
                            if($row['akg'] != ''){
                                $countQuery = prepareExecuteQuery("SELECT * FROM product WHERE " . $qPallets);
                                $theCount = mysqli_num_rows($countQuery);

                                $t_count = 0;
                                while($countRow = mysqli_fetch_array($countQuery)){
                                    $t_count += $countRow['quantity'];
                                }

                                echo $t_count . '<br/><span style="font-size:12px">Advised KG</span>';
                                $totalCases = $totalCases + $t_count;
                            }else{
								$count = mysqli_num_rows($yk);
								if ($count == 0)
								{
									$xk2 = "SELECT id FROM `weights` WHERE $qAppend2";
									$yk2 = prepareExecuteQuery($xk2);
									$count =mysqli_num_rows($yk2);
								}
                                echo $count;
                                $totalCases = $totalCases + $count;
                            }
							$qAppend2 = '';
							
							 
 			
						?>
					</td>
					<td>
                    	<textarea name="weightnote[]" class="overviewcomment" style="border:1px solid #f2f2f2;"><?php echo $row['weightnote']; ?></textarea>
						<?php
							
						?>
					</td>
 					<td align="right">
					<?php

						if($row['unit'] == 'PPC'){
							echo 'PPC';
						}else{
							$palletid = $row['pallet_id'];
							$yP = prepareExecuteQuery("SELECT id,grosspallet FROM `pallet` WHERE id=?",'i',[$palletid]);
							$pRow = mysqli_fetch_array($yP);
							
							if($pRow['grosspallet'] == 1){
								echo '[GT] ';
							}
							
							if($row['akg'] != ''){
								$t_count = 0;
								
								$countQuery = prepareExecuteQuery("SELECT * FROM product WHERE " . $qPallets);

								while($countRow = mysqli_fetch_array($countQuery)){
									$t_count += $countRow['akg'];
								}

								echo $t_count . ' kg';
							}else{
								echo number_format($weightthing, 3, '.', '') . ' kg'; 
								$weightthing = 0;
							}
						}
					?>
					</td>
					<td>
						<input type="text" name="productid[]" value="<?php echo implode(",",$productIDs); ?>" style="display:none;">
						<input type="text" name="cost[]" value="<?php if(empty($row['cost'])) echo ''; else echo number_format((float)$row['cost'], 2, '.', ''); ?>">
					</td>
					<td>
						<input type="text" name="price[]" value="<?php if(empty($row['price'])) echo ''; else echo number_format((float)$row['price'], 2, '.', ''); ?>">
					</td>
				</tr>
			<?php } ?>
			<tr>
				<td colspan="2">Total</td>
				<td align="center"><?php echo $totalCases; ?></td>
				<td></td>
				<td align="right"><?php echo number_format($totalWeight, 3, '.', ''); ?>kg</td>
				<td colspan="3" align="right"><input type="submit" value="Save & Update"></td>
			</tr>
		</table>
		</form>		
	<?php }else{ ?>
		<table border="1" cellpadding="5" width="100%">
			<tr>
				<td colspan="6" align="center"><b>Overview</b></td>
			</tr>
			<tr>
				<th>Species</th>
				<th>Cut</th>
				<th>Cases</th>
				<th>Comments</th>
				<th>Total Weight</th>
			</tr>
			<?php
				
				$x = "SELECT id FROM `pallet` WHERE intake_id=?";
				$y = prepareExecuteQuery($x,'i',[$intake_id]);
				$countPallets = mysqli_num_rows($y);
				
				$qPallets = '';
				
				while($row = mysqli_fetch_array($y)){
					$rowid = $row['id'];
					
					$qPallets .= " pallet_id = '$rowid' OR";
				}
				
				$qPallets = substr($qPallets, 0, -2);
				
				
				if($countPallets >= 1){
					$x = "SELECT * FROM product WHERE " . $qPallets . " GROUP BY cut_id";
				}else{
					// $x = "SELECT * FROM product WHERE id = 0";
				}
				
		 
				$y = prepareExecuteQuery($x);
				$count = mysqli_num_rows($y);
				
				$totalCases = 0;
				$totalWeight = 0;
				$totalWeight = 0;
				$c = 0;
				
				while($row = mysqli_fetch_array($y)){
					$c++;
					$product_id = $row['id'];
					
					$rowcutid = $row['cut_id'];
					
					if($countPallets >= 1){
						$x2 = "SELECT id FROM product WHERE (" . $qPallets . ") AND cut_id='$rowcutid'";
					}else{ $x2 = "SELECT id FROM product WHERE id = 0"; }
				
					$y2 = prepareExecuteQuery($x2);
					
					 
					$weightthing = 0;
					while($row2 = mysqli_fetch_array($y2)){
						
                        $rowid = $row2['id'];
                        if(weightFromProductID($rowid) != 1){
						$weightthing += weightFromProductID($rowid);
						$totalWeight += weightFromProductID($rowid);
						$qAppend2 .= " product_id = '$rowid' OR";
                        }
                    }
					 
					$qAppend2 = substr($qAppend2, 0, -2);
					$count2 = mysqli_num_rows($y2);
					
				?>
				<tr>
					<td><?php echo getSpeciesFromCutID($row['cut_id']); ?></td>
					<td><?php echo getCut($row['cut_id']);?></td>
					<td><?php 
							
							$cut_id = $row['cut_id'];
							
							$xk = "SELECT * FROM `weights` WHERE " . $qAppend2;
							$yk = prepareExecuteQuery($xk);
							// $ykRow = mysqli_fetch_array($yk);
							
							$qAppend2 = '';
							echo $count = mysqli_num_rows($yk);
							
							 
							$totalCases = $totalCases + $count;
			
							  
						?>
					</td>
					<td style="width:100px;">
						<textarea name="comment" class="comment" productid="<?php echo $row['id'];?>" style="height:30px;width:124px;">
                            <?php echo $row['comments']; ?>
						</textarea>
					</td>
					<td align="right">
                    <?php
						if($row['unit'] == 'PPC'){
							echo 'PPC';
						}else{
							
							$productid = $row['id'];
							$xX = "select * from `weights` WHERE product_id =?";
							$yY = prepareExecuteQuery($xX,'i',[$productid]);

							$weightt = mysqli_fetch_array($yY);

							$original_gross = number_format($weightt['original_gross'], 2, '.', '');
							$num_cartons = number_format($weightt['number_of_cartons'], 2, '.', '');
							$pallet_tare = number_format($weightt['pallet_tare'], 2, '.', '');
							$tare_per_carton = number_format($weightt['tare_per_carton'], 2, '.', '');
							
							$carton_tare = $num_cartons * $tare_per_carton;
							
							$total_tare = $carton_tare + $pallet_tare;
							
							$tare = $original_gross - $total_tare;

							if($weightt['grosstare'] == 1){
								echo number_format($tare, 3, '.', ''); 
								$totalWeight+= $tare;
								$tare = 0;
							}else{
								echo number_format($weightthing, 3, '.', ''); $weightthing = 0;
							}
							echo 'kg';
						}
                        ?>
					</td>
				</tr>
			<?php } ?>
			<tr>
				<td colspan="2">Total</td>
				<td colspan="1"><?php echo $totalCases; ?></td>
				<td></td>
				<td align="right"><?php echo number_format($totalWeight, 3, '.', ''); ?>kg</td>
			</tr>
		</table> 
	<?php } ?>
	
	<?php 
		$xk = "SELECT * FROM product WHERE original_intake_id=?";
		$yk = prepareExecuteQuery($xk,'s',[$intake_id]);
	
		$counting = mysqli_num_rows($yk);
 
	if($counting){ ?>
		<br/>
 		<table border="1" cellpadding="5" width="100%">
			<tr>
				<td colspan="7" align="center"><b>Returned Stock Overview</b></td>
			</tr>
			<tr>
			    <th>Species</th>
                <th>Cut</th>
                <th>Cases</th>
				<th>Comments</th>
                <th>Total Weight</th>
                <th>Customer</th>
                <th>New Intake ID</th>
			</tr>
			<?php
                
                
				$x = "SELECT * FROM product WHERE original_intake_id=?";		 

				$y = prepareExecuteQuery($x,'s',[$intake_id]);
				$count = $counting;
				
				$totalCases = 0;
				$totalWeight = 0;
				$totalWeight = 0;
				$c = 0;
				
				while($row = mysqli_fetch_array($y)){

                    $returnedIntakeID = intakeIDfromPalletID($row['pallet_id']);

                    $xo = "SELECT id FROM `pallet` WHERE intake_id=?";
                    $yo = prepareExecuteQuery($xo,'s',[$returnedIntakeID]);
                    $countPallets = mysqli_num_rows($yo);
                    
                    $qPallets = array();
                    
                    while($roow = mysqli_fetch_array($yo)){
                        $rowid = $roow['id'];
                        $qPallets[] = $rowid;
                    }
                    

					$c++;
					$product_id = $row['id'];
					
					$rowcutid = $row['cut_id'];
					
					if($countPallets >= 1){
						$x2 = "SELECT id FROM product WHERE pallet_id IN (" . implode(",",$qPallets) . ") AND cut_id='$rowcutid'";
					}else{ $x2 = "SELECT id FROM product WHERE id = 0"; }
				
					$y2 = prepareExecuteQuery($x2);
					
					 
					$weightthing = 0;
					$rowid = $row['id'];
					if(weightFromProductID($rowid) != 1){
					$weightthing += weightFromProductID($rowid);
					$totalWeight += weightFromProductID($rowid);
					$qAppend2 .= " product_id = '$rowid' OR";
					}
					 
					$qAppend2 = substr($qAppend2, 0, -2);
					$count2 = mysqli_num_rows($y2);
					
				?>
				<tr>
					<td><?php echo getSpeciesFromCutID($row['cut_id']); ?></td>
					<td><?php echo getCut($row['cut_id']);?></td>
					<td><?php 
							
							$cut_id = $row['cut_id'];
							
							$xk = "SELECT * FROM `weights` WHERE " . $qAppend2;
							$yk = prepareExecuteQuery($xk);
							// $ykRow = mysqli_fetch_array($yk);
							
							$qAppend2 = '';
							echo $count = mysqli_num_rows($yk);
							
							 
							$totalCases = $totalCases + $count;
			
							  
						?>
					</td>
					<td style="width:100px;">
						<textarea name="comment" class="comment" productid="<?php echo $row['id'];?>" style="height:30px;width:124px;">
                            <?php echo $row['comments']; ?>
						</textarea>
					</td>
					<td align="right">
                    <?php
                        $productid = $row['id'];
						$stmt = $mysqli->prepare("select * from `weights` WHERE product_id = ?");
						$stmt->bind_param('i', $productid);
                        $stmt->execute();
                        $result = $stmt->get_result();
						$weightt = $result->fetch_assoc();

                        $original_gross = number_format($weightt['original_gross'], 2, '.', '');
                        $num_cartons = number_format($weightt['number_of_cartons'], 2, '.', '');
                        $pallet_tare = number_format($weightt['pallet_tare'], 2, '.', '');
                        $tare_per_carton = number_format($weightt['tare_per_carton'], 2, '.', '');
                        
                        $carton_tare = $num_cartons * $tare_per_carton;
                        
                        $total_tare = $carton_tare + $pallet_tare;
                        
                        $tare = $original_gross - $total_tare;

                        if($weightt['grosstare'] == 1){
                            echo number_format($tare, 3, '.', ''); 
                            $totalWeight+= $tare;
                            $tare = 0;
                        }else{
                            echo number_format($weightthing, 3, '.', ''); $weightthing = 0;
                        }

                        ?>kg
                    </td>
                    <td><?php
					
						$returnedIntakeID = intakeIDfromPalletID($row['pallet_id']);
						$returnedIntake = getIntake($returnedIntakeID);
						
						$customer = getCustomer($returnedIntake['supplier_id']);
						echo $customer['businessname'];
						?>
					</td>
					<td><a href="intake.php?id=<?php echo $returnedIntakeID; ?>"><?php echo $returnedIntakeID; ?></a></td>
				</tr>
			<?php } ?>
					  
		</table>
 	<?php } ?>
	
	<br/>
	<div id="printShow">
		<table style="height:100px;width:100%;">
			<tr>
				<td style="border: 1px dashed black;"></td><td style="border: 1px dashed black;"></td><td style="border: 1px dashed black;"></td>
			</tr>
			<tr>
				<td style="border: 1px dashed black;"></td><td style="border: 1px dashed black;"></td><td style="border: 1px dashed black;"></td>
			</tr>
		</table>
	</div>
	<a href="javascript:;" class="add_product" onclick="openAddPallet(<?php echo $intake_id; ?>);">Add a Pallet</a>
 	<a href="printIntake.php?intake_id=<?php echo $intake_id; ?>" class="print_intake" >Print Intake</a>
	 <a href="printAllPallets.php?intake_id=<?php echo $intake_id; ?>" class="print_intake" >Print all pallets</a>

	<center id="hidePalletBtnContainer"><br/><br/><br/><Br/><Br/><div class="loadPalletBtn" id="loadPalletBtn">Load Pallets</div></center>
	<div id="ajaxContent">
	 
	</div>
</main>
<div id="btm"></div>
<div id="box" style="display:none;">

</div>
<div id="editBox" style="display:none;">

</div>
<style>
	.ui-dialog-titlebar{
		padding-top: 20px;
		padding-bottom: 20px;
	}
</style>
<script>
    $('#changeIntakeSupplier').change(function(){
		var formName = '#changeIntakeSupplierForm';
			var xhttp = new XMLHttpRequest();
			xhttp.open("POST", $(formName).attr('action'), true);
			xhttp.setRequestHeader('X-CSRF-TOKEN', "<?php echo csrf_token();?>");
			xhttp.send($(formName).serialize());
    });
    
	$('.loadPalletBtn').click(function(){
		$('#ajaxContent').html('<center><img src="https://i.gifer.com/7plQ.gif"></center>');
		$.get( "/legacy/ajax/loadPallets.php?intake_id=<?php echo $intake_id; ?> ", function( data ) {	
			$('#ajaxContent').html(data);
			$('#hidePalletBtnContainer').fadeOut();
		});
	});
	
	$(document).ready(function(){
		var totalIntakeWeight = 0.0;
		$('#printShow').hide();
		$('.aWeight').each(function() { totalIntakeWeight = parseFloat(totalIntakeWeight) + parseFloat($(this).val()); });
		var xxD = parseFloat(<?php echo $totalWeight; ?>).toFixed(3);
		$('#intakeTotalWeightA').text(xxD + ' KG');
		$(window).on({
			'beforeprint': () => {
				$('#printShow').show();
			},
			'afterprint': () => {
				$('#printShow').hide();
			}
});
	});
	
	function editWeight(intake_id, pallet_id, product_id, weight_id){
		console.log('intake_id ' + intake_id);
		console.log('pallet_id ' + pallet_id);
		console.log('product_id ' + product_id);
		console.log('weight_id ' + weight_id);
		
		$(window).scrollTop(0);

		
		$.get( "ajax/getEditProduct.php?intake_id=" + intake_id + "&pallet_id=" + pallet_id + "&product_id=" + product_id + "&weight_id=" + weight_id, function( data ) {	
			$('#editBox').html(data);
			$('#editBox').fadeIn();
		});
		
		
	}
	
	$('#updateIntakeButton').click(function(){
		
		var supplier_id = $('#supplier_id').val();
		var vehicle_reg = $('#vehicle_reg').val();
		var date_received = $('#date_received').val();
		var vehicle_temperature = $('#vehicle_temp').val();
		var delivery_note_number = $('#delivery_note_number').val();
		
		var good = 1;
		var msg = "";
		
		if(vehicle_reg == ''){
			msg = "The highlighted fields cannot be blank!";
			$('#vehicle_reg').css('border','2px solid red');
			good = 0;
		}else{
			$('#vehicle_reg').css('border','1px solid grey');
		}
		
		if(date_received == ''){
			msg = "The highlighted fields cannot be blank!";
			$('#date_received').css('border','2px solid red');
			good = 0;
		}else{
			$('#date_received').css('border','1px solid grey');
		}
		
		if(vehicle_temperature == ''){
			msg = "The highlighted fields cannot be blank!";
			$('#vehicle_temp').css('border','2px solid red');
			good = 0;
		}else{
			$('#vehicle_temperature').css('border','1px solid grey');
		}
		
		if(delivery_note_number == ''){
			msg = "The highlighted fields cannot be blank!";
			$('#delivery_note_number').css('border','2px solid red');
			good = 0;
		}else{
			$('#delivery_note_number').css('border','1px solid grey');
		}
		
		$('#msgNotice').html(msg);
		
		if(good == 1){
			var formName = '#updateIntakeInfo';
			var xhttp = new XMLHttpRequest();
			xhttp.open("POST", $(formName).attr('action'), true);
			xhttp.setRequestHeader('X-CSRF-TOKEN', "<?php echo csrf_token();?>");
			xhttp.send($(formName).serialize());
		}
	});
	
	function deleteProduct(product_id, cut_id){
		console.log(product_id);
		console.log(cut_id);
	}
	
	function palletDetail(id){
		
		$('.palletDetail-' + id).toggle();
	}
	
	
	function printIntake(intake_id){
		$.ajax({
			type: "POST",
			url: 'printIntake.php?intake_id=' + intake_id,
			type: 'get',
			success: function( response ) { 

				var contents = response;
				var idname = name;

				var frame1 = document.createElement('iframe');
				frame1.name = "frame1";
				frame1.style.position = "absolute";
				frame1.style.top = "-1000000px";
				document.body.appendChild(frame1);

				var frameDoc = frame1.contentWindow ? frame1.contentWindow : frame1.contentDocument.document ? frame1.contentDocument.document : frame1.contentDocument;

				frameDoc.document.open();
				frameDoc.document.write('<html><head><meta http-equiv="Content-Type" content="text/html; charset=euc-kr"><title></title>');

	 


				frameDoc.document.write('</head><body>');
				frameDoc.document.write(contents);
				frameDoc.document.write('</body></html>');
				frameDoc.document.close();
				setTimeout(function () {
				window.frames["frame1"].focus();
				window.frames["frame1"].print();
				document.body.removeChild(frame1);
				}, 500);
				return false; 
			}
		});
	}
	
	function printContent(el){
		var restorepage = $('body').html();
		var printcontent = $('#' + el).clone();
		$('body').empty().html(printcontent);
		window.print();
		// $('body').html(restorepage);
		
		setTimeout(
			function() {
				window.location.reload(1);
			}, 10000);
	}

	function palletDetail(id){
		
		$('.palletDetail-' + id).toggle();
	}
	
	function openAddPallet(intake_id){
		
		$.get( "ajax/addPalletForm.php?intake_id=" + intake_id, function( data ) {
			// console.log(data);
			// $('#cut_id').html('<option></option>');
			$('#box').html(data);
		});
		
		// $('#add_to_pallet_id').val(pallet_id);
		// $('.add_to_pallet_id').html('0000' + pallet_id);
		$('#box').fadeIn();
	}
	
	
	function openAddtoPallet(intake_id, pallet_id){
		
		$(window).scrollTop(0);
		
		$.get( "/ajax/editPalletForm.php?intake_id=" + intake_id + "&pallet_id=" + pallet_id, function( data ) {
			// console.log(data);
			// $('#cut_id').html('<option></option>');
			$('#box').html(data);
		});
		
		// $('#add_to_pallet_id').val(pallet_id);
		// $('.add_to_pallet_id').html('0000' + pallet_id);
		$('#box').fadeIn();
	}
	
	function deleteRow(intake_id, pallet_id){
		if(confirm('Are you sure you want to delete this?')){
			window.location.href = "/scripts/deletePallet.php?intake_id=" + intake_id + "&pallet_id=" + pallet_id;
			// console.log(intake_id + '  ' + pallet_id);
		}
	}
	
	// printContent(1);
	
	function addProductToProduct(intake_id, pallet_id, product_id){
		$.get( "/ajax/addProductToProduct.php?intake_id=" + intake_id + "&pallet_id=" + pallet_id  + "&product_id=" + product_id, function( data ) {
			$('#box').html(data);
		});

		$('#box').fadeIn();
	}
	
	function printContent(id){
	   $.ajax({
		
				type: "POST",
				url: 'printContent.php?id=' + id,
				type: 'get',
				success: function( response ) { 

					  var contents = response;
					 var idname = name;

					 var frame1 = document.createElement('iframe');
					 frame1.name = "frame1";
					 frame1.style.position = "absolute";
					frame1.style.top = "-1000000px";
					document.body.appendChild(frame1);

					var frameDoc = frame1.contentWindow ? frame1.contentWindow : frame1.contentDocument.document ? frame1.contentDocument.document : frame1.contentDocument;

				frameDoc.document.open();
				frameDoc.document.write('<html><head><title></title>');
			   
			 frameDoc.document.write('<style>table {  border-collapse: collapse;  border-spacing: 0; width:100%; margin-top:20px;} .table td, .table > tbody > tr > td, .table > tbody > tr > th, .table > tfoot > tr > td, .table > tfoot > tr > th, .table > thead > tr > td, .table > thead > tr > th{ padding:8px 18px;  } .table-bordered, .table-bordered > tbody > tr > td, .table-bordered > tbody > tr > th, .table-bordered > tfoot > tr > td, .table-bordered > tfoot > tr > th, .table-bordered > thead > tr > td, .table-bordered > thead > tr > th {     border: 1px solid #e2e2e2;} </style>');
		 
		  // your title
		   frameDoc.document.title = "Print Content with ajax in php";
	   
	   
		  frameDoc.document.write('</head><body>');
				frameDoc.document.write(contents);
				frameDoc.document.write('</body></html>');
				frameDoc.document.close();
				setTimeout(function () {
					window.frames["frame1"].focus();
					window.frames["frame1"].print();
					document.body.removeChild(frame1);
				}, 500);
				return false; 

		
		
		
				}
			});
	  
	}
	
	
	
</script>

<div class="popup">
	<?php
		// $x = "SELECT * FROM `product_form` WHERE ";
	?>
</div>
</body>
</html>
<script>
	$(document).ready(function(){
		
		$('.comment').each(function(){
			$(this).on('keypress',function(e) {
				if(e.which == 13) {
					var comment = $(this).val();
					// comment = comment + " &#10;";
	
					// console.log('the comment: ' + comment);
					
					
					
					var productid = $(this).attr('productid');
					
					
					console.log('the productid: ' + productid);
					
					$.get("<?php echo $domain; ?>ajax/saveComment.php?comment="+comment+'&productid='+productid, function(data, status){
						console.log(data);
					});
				}
			});
		});
		
		$('#closePalletPopup').click(function(){
			$('.palletnotepopup').fadeOut();
		});
		
		<?php if(request('pallet_id')){ ?>
			$('.palletnotepopup').fadeIn();
		<?php } ?>
	});
</script>
<div class="palletnotepopup">Pallet <span class="palletidpopup"><?php echo request('pallet_id'); ?></span> Noted <a href="javascript:;" class="close" id="closePalletPopup">X</a></div>


<?php
	if(request('palletupdated')){
	?>
		<script>
			$(document).ready(function(){
				$('#closePalletPopup').click(function(){
					$('.palletnotepopup').fadeOut();
				
				});
			});
		</script>
		<div class="palletnotepopup">Pallet <?php echo request('palletupdated'); ?> Updated <a href="javascript:;" class="close" id="closePalletPopup">X</a></div>
	<?php
	}
?>
