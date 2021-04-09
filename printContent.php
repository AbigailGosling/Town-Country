<?php
	include('includes/frontHeader.php');
	
	$id = $_GET['intake_id'];
	$intake_id = $_GET['intake_id'];	
	$pallet_id = $_GET['pallet_id'];	
	
	$intake = getIntake($id);

	$types = Array('UB','BB','N/A','PB','EX');
	
	$supplier = getSupplier($intake['supplier_id']);
?>	
<main>
	<a style="position:absolute;left:0px;top:20px;" href="intake.php?id=<?php echo $intake_id; ?>">Back</a>
	<h1 style="font-family: 'OpenSans_Semibold' !important;font-weight: 700;color: #000;padding-bottom:20px;font-size: 26px;text-align: left;">Pallet Note #0000<?php echo $pallet_id; ?></h1>
	<div class="overview">
		<div class="overview_block">
			<label>Intake ID</label>
			<?php echo $intake['id']; ?>
		</div>
		
		<div class="overview_block">
			<label>Supplier ID</label>
			<?php echo $intake['supplier_id']; ?>
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
			<label>Total Pallet Weight</label>
			<div id="intakeTotalWeightA">0</div>
		</div>
		
		<div style="clear:both;"></div>
	</div>
	<div class="clearfix"></div>
	
	<div id="product_list">
 
		<?php
			$x = "SELECT * FROM `pallet` where intake_id = '$intake_id' && id = '$pallet_id' LIMIT 1";
			// echo $x = "SELECT * FROM `pallet` where intake_id = '$intake_id' && id = '$pallet_id'";
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
							
							$x = "SELECT * FROM `product` WHERE pallet_id='$pallet_id' && cut_id ='$cut_id'";
							$y = mysqli_query($conn, $x);
							
							while($product = mysqli_fetch_array($y)){
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
								<?php echo '[<b>' . $types[$product['ubbb']] .'</b>]'; ?>
								<?php echo '<span style="color:grey;">(' . $product['range_from'] . ' - '; ?>
								<?php echo $product['range_to'] . ')</span>'; ?>
								
								<div class="picksheetPalletDetail">
								<?php
									// $areThey = areWeightsAllTheSame($product_id);
									
									// if($areThey != 0){
										// $weightsX = "SELECT * FROM `weights` WHERE product_id='$product_id' GROUP BY weight_gross";
									// }else{
										$weightsX = "SELECT * FROM `weights` WHERE product_id='$product_id'";
									// }
									
									$weightsY = mysqli_query($conn, $weightsX);
									
									while($weights = mysqli_fetch_array($weightsY)){
									 
											if($weights['weight_tear'] == $weights['weight_gross']){
												$w = $weights['weight_gross'];
											}else{
												$w = $weights['weight_gross'] - $weights['weight_tear'];
											}
										?>
										<div class="weightbox"><?php echo number_format($w, 3, '.', ''); ?></div>
									<?php
									}
								?>
								<?php
									if($row['grosspallet'] == 1){
									?>
										<table width="100%">
										<tr>
											<td colspan="11" style="padding-bottom:0px;">
												<div style="text-align:left;float:right;">
													<table border="1" style="background:#cacaca;">
														<tr>
															<td align="left"><b>Gross Weight: </b></td>
															<td align="right"><?php echo number_format($row['gross_weight'], 2, '.', ''); ?></td>
														
															<td align="left"><b>Pallet Tare: </b></td>
															<td align="right"><?php echo number_format($row['pallet_tare'], 2, '.', ''); ?></td>
														
															<td align="left"><b>Tare per carton: </b></td>
															<td align="right"><?php echo number_format($row['tare_per_carton'], 2, '.', ''); ?></td>
														
															<td align="left"><b>No of cartons: </b></td>
															<td align="right"><?php echo number_format($row['number_of_cartons'], 2, '.', ''); ?></td>
														
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
	
	setTimeout(function(){
		// print();
	},3000);
 
	
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