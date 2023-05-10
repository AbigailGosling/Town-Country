<?php
	include('includes/frontHeader.php');
	
	$id = request()->input('intake_id');
	$intake_id = request()->input('intake_id');	
	$pallet_id = request()->input('pallet_id');	
	
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
			<label>Supplier</label>
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
			<label>Total Pallet Weight</label>
			<div id="intakeTotalWeightA">0</div>
		</div>
		
		<div style="clear:both;"></div>
	</div>
	<div class="clearfix"></div>
	
	<div id="product_list">
 			<?php
				$y_pallets = prepareExecuteQuery("SELECT id FROM `pallet` WHERE intake_id=? && id = ? ORDER BY id ASC",'ii',[$intake_id,$pallet_id]);
				while($pallet = mysqli_fetch_array($y_pallets)){
			?>
				<div class="product" style="padding-bottom:0px;">
				 	<div class="overview" style="display:block;">
						<?php
							$pallet_id = $pallet['id'];
							$product_y = prepareExecuteQuery( "SELECT * FROM `product` WHERE pallet_id=?",'i',[$pallet_id]);

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
								
									$xk = "SELECT * FROM `weights` WHERE product_id=?";
									$yk = prepareExecuteQuery($xk,'i',[$product_id]);
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
									$weightsX = "SELECT * FROM `weights` WHERE product_id=?";
									$weightsY = prepareExecuteQuery($weightsX,'i',[$product_id]);
									
									while($weights = mysqli_fetch_array($weightsY)){
									?>
									<?php
											if($weights['weight_tear'] == $weights['weight_gross']){
												$w = (double)$weights['weight_gross'];
											}else{
												$w = (double)$weights['weight_gross'] - (double)$weights['weight_tear'];
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
	$.ajaxSetup({
		headers: { 'X-CSRF-TOKEN': "<?php echo csrf_token();?>" }
	});
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
		
		
		$.get( "ajax/getEditProduct.php?intake_id=" + intake_id + "&species_id=" + species_id + "&pallet_id=" + pallet_id + "&product_id=" + product_id + "&cut_id=" + cut_id, function( data ) {	
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
		
		$.get( "ajax/editPalletForm.php?intake_id=" + intake_id + "&pallet_id=" + pallet_id, function( data ) {
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
			window.location.href = "scripts/deletePallet.php?intake_id=" + intake_id + "&pallet_id=" + pallet_id;
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