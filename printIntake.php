<?php
	include('includes/frontHeader.php');
	
	$id = $_GET['intake_id'];
	$intake_id = $_GET['intake_id'];	
	
	$types = Array('UB','BB','N/A','PB','EX');
	
	$intake = getIntake($id);
	
	$supplier = getSupplier($intake['supplier_id']);
?>	
<main>
	<a style="position:absolute;left:0px;top:20px;" href="intake.php?id=<?php echo $intake_id; ?>">Back</a>
	
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
			<div id="intakeTotalWeightA">0</div>
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
				
				$x = "SELECT id FROM `pallet` WHERE intake_id='$intake_id'";
				$y = mysqli_query($conn, $x);
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
				
		 
				$y = mysqli_query($conn, $x);
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
				
					$y2 = mysqli_query($conn, $x2);
					
					 
					$weightthing = 0;
					while($row2 = mysqli_fetch_array($y2)){
						
						$rowid = $row2['id'];
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
							$yk = mysqli_query($conn, $xk);
							// $ykRow = mysqli_fetch_array($yk);
							
							$qAppend2 = '';
							
							if($row['akg'] != ''){
                                $countQuery = mysqli_query($conn, "SELECT * FROM product WHERE " . $qPallets);
                                $theCount = mysqli_num_rows($countQuery);

                                
                                $t_count = 0;
                                while($countRow = mysqli_fetch_array($countQuery)){
                                    $t_count += $countRow['quantity'];
                                }


                                echo $t_count . '<br/><span style="font-size:12px">Advised KG</span>';
                                $totalCases = $totalCases + $t_count;

                             }else{
                                echo $count = mysqli_num_rows($yk);
                                $totalCases = $totalCases + $count;
                            }
							 
							
			
							  
						?>
					</td>
					<td align="right">
					<?php
						$palletid = $row['pallet_id'];
						
						$yPallet = mysqli_query($conn, "SELECT * FROM `pallet` WHERE id='$palletid'");
						$rowPallet = mysqli_fetch_array($yPallet);
						if($rowPallet['grosspallet'] == 1){
							echo '[GT] ';
                        }
                        								
					?>
					<?php echo number_format($weightthing, 3, '.', ''); $weightthing = 0;?>kg
					</td>
				</tr>
			<?php } ?>
			<tr>
				<td colspan="2">Total</td>
				<td><?php echo $totalCases; ?></td>
				<td align="right"><?php echo number_format($totalWeight, 3, '.', ''); ?>kg</td>
			</tr>
		</table>
	<div id="product_list">
 
		<?php
			$x = "SELECT * FROM `pallet` where intake_id = '$intake_id'";
			$y = mysqli_query($conn, $x);
			
			while($row = mysqli_fetch_array($y)){
				
				$pallet_id = $row['id'];
			?>
			<div class="product" style="padding-bottom:0px;">
				<div class="title">
					
					<div class="title" style="display:none;">
						<div onclick="palletDetail(<?php echo $pallet_id; ?>)" onselectstart="return false" style="font-size:12px;display:block;width:50%;float:left; cursor:pointer;padding-left: 4px;" >Pallet No. <span>0000<?php echo $pallet_id; ?></span><br/><br/></div> 
					</div>
					
				</div>
				<div class="overview" style="display:block;">
					<?php
						$cutResult = getCuts();
						
						while($cuts = mysqli_fetch_array($cutResult)){
							$cut_id = $cuts['id'];
							
							$xG = "SELECT * FROM `product` WHERE pallet_id='$pallet_id' && cut_id ='$cut_id'";
							$yG = mysqli_query($conn, $xG);
							
							while($product = mysqli_fetch_array($yG)){
								$product_id = $product['id'];
								$cut_id = $product['cut_id'];
								$species_id = getSpeciesFromCut($cut_id);
								$weightthing = weightFromProductID($product_id);
							?>
							<input type="text" class="aWeight" value="<?php echo $weightthing; ?>" style="display:none;">
							
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
                                        echo '<b>[' . number_format($weightthing, 3, '.', '') . 'kg]</b>';
                                    }
                                    
                                ?>
								<?php echo '[' . getTemp($product['cooling_id']) .']'; ?>
								<?php echo '[Pallet ID: <b>' . $pallet_id .'</b>]'; ?>
								<?php echo '[<b>' . $types[$product['ubbb']] .'</b>]'; ?>
								<?php echo '<span style="color:grey;">(' . $product['range_from'] . ' - '; ?>
								<?php echo $product['range_to'] . ')</span>'; ?>
								<div class="picksheetPalletDetail" style="padding:0px;display: flex;flex-wrap: wrap">
								<?php
									// $areThey = areWeightsAllTheSame($product_id);
									
									// if($areThey != 0){
										// $weightsX = "SELECT * FROM `weights` WHERE product_id='$product_id' GROUP BY weight_gross";
									// }else{
										$weightsX = "SELECT * FROM `weights` WHERE product_id='$product_id'";
									// }
									
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
						
						}
					?>
				</div>
			</div>
			<?php
			}
			$overallWeight = 0;
			$overallWeight2 = 0;
		?>
	</div>
	
	<!--<a href="javascript:;" class="add_product openAddPallet">Add a Pallet</a>-->
</main>
<script>

	$(document).ready(function(){
		
		var totalIntakeWeight = 0.0;
		
		$('.aWeight').each(function() {
			totalIntakeWeight = parseFloat(totalIntakeWeight) + parseFloat($(this).val());			
		});
		
		var xxD = parseFloat(totalIntakeWeight).toFixed(3);
		
		$('#intakeTotalWeightA').text(xxD + ' KG');
		
		print();
	
	});
	
	function printCompleted(){
		console.log();
	}
	
	function editProduct(intake_id, species_id, pallet_id, product_id, cut_id){
		console.log('intake_id ' + intake_id);
		console.log('species_id ' + species_id);
		console.log('pallet_id ' + pallet_id);
		console.log('product_id ' + product_id);
		console.log('cut_id ' + cut_id);
		
		
		$.get( "/ajax/getEditProduct.php?intake_id=" + intake_id + "&species_id=" + species_id + "&pallet_id=" + pallet_id + "&product_id=" + product_id + "&cut_id=" + cut_id, function( data ) {	
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
			$('#updateIntakeInfo').submit();
		}
	});
	
	function deleteProduct(product_id, cut_id){
		console.log(product_id);
		console.log(cut_id);
	}
	
	function palletDetail(id){
		
		$('.palletDetail-' + id).toggle();
	}
	
	function printPallet(intake_id, pallet_id){
		
		$.ajax({
			type: "POST",
			url: 'printContent.php?intake_id=' + intake_id + '&pallet_id=' + pallet_id,
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
				frameDoc.document.write('<html><head><title></title>');

	 


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
		
		$.get( "/ajax/addPalletForm.php?intake_id=" + intake_id, function( data ) {
			// console.log(data);
			// $('#cut_id').html('<option></option>');
			$('#box').html(data);
		});
		
		// $('#add_to_pallet_id').val(pallet_id);
		// $('.add_to_pallet_id').html('0000' + pallet_id);
		$('#box').fadeIn();
	}
	
	
	function openAddtoPallet(intake_id, pallet_id){
		
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
</body>
</html>