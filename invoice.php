<?php
	include_once('includes/frontHeader.php');
	
	$pickersheet_id = $_GET['id'];
	$adv = array_key_exists("adv",$_GET);
	$x = "SELECT * FROM `pickerSheets` WHERE id='$pickersheet_id'";
	$y = mysqli_query($conn, $x);
	$pickSheetRow = mysqli_fetch_array($y);
	
	$customer_id = $pickSheetRow['customer_id'];
	
	$x2 = "SELECT * FROM `customers` WHERE id='$customer_id'";
	$y2 = mysqli_query($conn, $x2);
	
	$customerRow = mysqli_fetch_array($y2); 
	
	if($_GET['deleteInternalDocument'] != '' && $user['user_type'] == 'A'){
		$internal_doc_id = mysqli_real_escape_string($conn, $_GET['deleteInternalDocument']);
		$pickersheet_id = mysqli_real_escape_string($conn, $_GET['id']);

		mysqli_query($conn, "DELETE FROM `pickersheet_documents` WHERE id=$internal_doc_id LIMIT 1") or die(mysqli_error($conn));

		header('Location: invoice.php?id=' . $pickersheet_id);
	}
	if ($adv == false)
	{
?>
<div>
	<div id="top">
		<a href="menu.php" id="menu">MENU</a>
		<a href="logout.php" id="logout">LOGOUT</a>
	</div>
	<?php
	}
	?>
	<?php
	if ($adv == false)
	{
	?>
	<a href="<?php echo $domain; ?>invoiceList.php" class="backbtn" onclick="goBack()">
		< Back</a> <?php
	}
	?> <main class="int" style="padding: 0 0 0 0; border-bottom: 1px solid black">
			<?php
		if ($adv == false)
		{
	?>
			<div class="formBackButton formBackButton--invoice" style="float:right;font-size:22px;">
				<a href="viewCompletedPickSheet.php?id=<?php echo $pickersheet_id; ?>">Pick Note</a> |
				<a href="javascript:;" onclick="printStuff()">Print</a>
			</div>
			<?php
			}
			?>
			<div class="printme" id="print" style="padding-top:0px;>
				<div class="topheading">
					<table width="100%">
						<thead>
							<tr>
								<th width="33%"></th>
								<th width="33%">
									<img class="logo" style="width: 290px; display:block"
										src="data:image/jpeg;base64,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"><br />
									<br>
									<div class="mainaddress" style="font-size: 10px;">
										13-17 Landport Ind. Est. Landport Road<br />
										Wolverhampton WV2 2QJ<br />
										<span>Vat. No: 701 075 285</span><br />
										<span>Company Reg. No. 12192223</span><br />
										<b>01902457924</b><br />
									</div>
								</th>
								<th style="backgound-color: #8c8c8c" width="33%">
									<div class="delivery" style="margin-left: auto; margin-right: 0;">
										<?php if(($customer['accounts_email'] != '' && $customer['accounts_email'] != null) && $adv == false){ ?>
										<div class="resend-invoice printhide" onclick="resendInvoice()">
											Resend Invoice
										</div>
										<?php } ?>
										<div class="deliverybox" style="border:0px; background-color: #D5D5D5;">
											<div class="po" style="background-color: #D5D5D5;">Invoice No:
												<span><?php echo $pickersheet_id; ?></span>
											</div>
											<h2>Invoice</h2>
										</div>
										<br />
										<div class="deliverydate" style="background-color: #D5D5D5;">Delivery Date:
											<span
												class="date"><?php echo $pickSheetRow['estimated_delivery_date']; ?></span>
										</div>
										<div class="deliverydate">P.O. Number:
											<span><?php echo $pickSheetRow['orderReferenceNumber']; ?></span></div>
										<?php
                    $date_completed = str_replace('/', '-', $pickSheetRow['date_completed']);
                    $date_completed2 = date('d/m/Y', strtotime($date_completed));
                    $assemblydate = date('d/m/Y G:i A', strtotime($date_completed));

                    $date = DateTime::createFromFormat('d/m/Y', ''.$date_completed2);

                    $paydayDelay = $customerRow['credit_terms'];

                    $date->modify('+'. $paydayDelay .' day');
                    $payByDate = $date->format('d/m/Y');
                       ?>
										<div class="po">Assembled: <span><?php echo $assemblydate; ?></span></div>

									</div>
								</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									<div class="invoice">

										<b style="font-size:10px;color:#8c8c8c;">Invoice address</b>
										<div class="invoicebox">
											<?php
                        $customer_id = $pickSheetRow['customer_id'];
                        $x = "SELECT * FROM `customers` WHERE id='$customer_id'";
                        $y = mysqli_query($conn, $x);
                        $customer = mysqli_fetch_array($y);

                        ?>
											<p>
												<?php echo $customer['businessname']; ?><br />
												t/a <?php echo $customer['tradingas']; ?><br />
												<?php echo $customer['accounts_address_1']; ?><br />
												<?php echo $customer['accounts_address_2']; ?><br />
												<?php echo $customer['accounts_address_3']; ?><br />
												<?php echo $customer['accounts_address_4']; ?><br />
												Customer ID:
												<?php echo str_pad($customer['id'], 4, '0', STR_PAD_LEFT); ?><br />
											</p>
											<span style="display:none;">Account No: 1123ml</span>
										</div>
									</div>
								</td>
								<td></td>
								<td>
									<b style="color: #8c8c8c;font-size: 12px;">Delivery address</b>
									<div class="deliverybox" style="background-color: #D5D5D5;">
										<p>
											<?php echo $customer['businessname']; ?><br />
											t/a <?php echo $customer['tradingas']; ?><br />
											<?php
    
                            if($pickSheetRow['addressid'] == ''){ $pickSheetRow['addressid'] = 1; }

                            if($pickSheetRow['addressid'] == 1){
                            
                                echo $customer['address1_1'] . '<br/>';
                                echo $customer['address1_2'] . '<br/>';
                                echo $customer['address1_3'] . '<br/>';
                                echo $customer['postcode_1'] . '<br/>';
                            
                            }
                        
                            if($pickSheetRow['addressid'] == 2){
                            
                                echo $customer['address2_1'] . '<br/>';
                                echo $customer['address2_2'] . '<br/>';
                                echo $customer['address2_3'] . '<br/>';
                                echo $customer['postcode_2'] . '<br/>';
                            
                            }
                        
                            if($pickSheetRow['addressid'] == 3){
                            
                                echo $customer['address3_1'] . '<br/>';
                                echo $customer['address3_2'] . '<br/>';
                                echo $customer['address3_3'] . '<br/>';
                                echo $customer['postcode_3'] . '<br/>';
                            
                            }
    
                            ?>
										</p>
									</div>
								</td>
							</tr>
						</tbody>
					</table>


				</div>
				<?php if($user['user_type'] == 'A'){ ?>
				<br />
				<form class="printhide" method="POST" action="scripts/addInternalDocument.php"
					enctype="multipart/form-data" style="padding:10px;background: #f9f9f9;border: 1px solid #333;">
					<input type="hidden" name="type" value="INVOICE">
					<input type="hidden" name="pickersheet_id" value="<?php echo $pickersheet_id; ?>">

					<table>
						<tr>
							<td colspan="4" align="left">
								<h3 style="margin:0;">Add a document/message</h3>
								<br />
							</td>
						</tr>
						<tr>
							<td>
								<label>Note</label><br />
								<input type="text" name="message">
							</td>
							<td style="padding-left:10px;">
								<label>Document</label><br />
								<input type="file" name="dfile">
							</td>
							<td><br />
								<input type="submit">
							</td>
						</tr>
					</table>
					<?php
			$internalDocResult = mysqli_query($conn, "SELECT * FROM `pickersheet_documents` WHERE type='INVOICE' && pickersheet_id='$pickersheet_id' ORDER BY id DESC");
			$internalDocCount = mysqli_num_rows($internalDocResult);

			if($internalDocCount > 0){
		?>
					<br />
					<table width="100%" border="0">
						<tr class="productsHeading" style="background-color: #7fabce9e;">
							<th align="left">Message</th>
							<th align="left">User</th>
							<th align="right">Action</th>
						</tr>
						<?php
				while($internalDoc = mysqli_fetch_array($internalDocResult)){
				?>
						<tr style="height:30px;">
							<td>
								<?php
							echo $internalDoc['message'];

							if($internalDoc['dfile'] != ''){
							?> <a href="/docs/<?php echo $internalDoc['dfile']; ?>" target="_blank">(View Document)</a><?php
							}
						?>
							</td>
							<td><?php echo getUsername($internalDoc['user_id']); ?></td>
							<td align="right">
								<a
									href="?id=<?php echo $pickersheet_id; ?>&deleteInternalDocument=<?php echo $internalDoc['id']; ?>">Delete</a>
							</td>
						</tr>
						<?php
				}
			?>
					</table>
					<?php } ?>
				</form>
				<br /><br />
				<?php } ?>
				<br>
				<table width="100%" border="0">
					<tr class="productsHeading" style="background-color: #7fabce9e;">
						<th align="left">Intake ID</th>
						<th align="left">Plt ID</th>
						<th align="left" colspan="5"></th>
						<th align="center">Qty</th>
						<th align="left">Unit</th>
						<th align="right">Weight</th>
						<th align="right" class="price">Price</th>
						<th align="right" class="price">Total</th>
					</tr>

					<?php

				$numOfRows = 0;
                $outpalletQuery = "SELECT * FROM `palletsOut` WHERE pickersheet_id='$pickersheet_id'";
                $outpalletResult2 = mysqli_query($conn, $outpalletQuery);
                $outpalletCount = mysqli_num_rows($outpalletResult2);
				$total_qty_count = 0;
				$total_weight_count = 0;
				$total_case_count = 0;

                while($outpallet = mysqli_fetch_array($outpalletResult2)){
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
                         
                        $k = 0;

                        while($weight = mysqli_fetch_array($y2)){
                            
                            if($weight['weight_tear'] == $weight['weight_gross']){
                                $w = $weight['weight_gross'];
                            }else{
                                $w = $weight['weight_gross'] - $weight['weight_tear'];
                            }

                            $k = $k + $w;
						}

						$total_qty_count += $count;
						?>
					<tr class="productsRow">
						<?php $numOfRows++; ?>
						<td align="left"><span
								class="palletid"><?php echo intakeIDfromPalletID($product['pallet_id']); ?></span></td>
						<td align="left"><span class="palletid"><?php echo $product['pallet_id']; ?></span></td>
						<td align="left"><span
								class="palletid"><?php echo getNationality($product['nationality_id']); ?></span></td>
						<td align="left"><span class="chilled"><?php echo getTemp($product['cooling_id']); ?></span>
						</td>
						<td align="left"><b class="species"><?php echo getSpeciesFromCutID($product['cut_id']); ?></b>
						</td>
						<td align="left"><b class="cut"><?php echo getCut($product['cut_id']); ?></b></td>
						<td align="left"><b class="brand"><?php echo getBrand($product['brand_id']); ?></b></td>
						<?php
                            $productID = $product['id'];
                            $howManyX = "SELECT * FROM `pickerItems` WHERE pickersheet_id='$pickersheet_id' AND product_id='$productID'";
                            $howManyY = mysqli_query($conn, $howManyX);
                            $pickerItem = mysqli_fetch_array($howManyY);
                            $howMany = mysqli_num_rows($howManyY);
                        ?>
						<td align="left"><b class="quantity"><?php echo $count; ?></b></td>
						<td align="left">
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
						<td align="left">
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
                                    
                                    $kg = number_format($kg, 3, '.', '');
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
						<td align="right" class="price">
							£<?php echo number_format((float)$pickerItem['price'], 2, '.', ''); ?></td>
						<td align="right" class="price">£<?php echo $totalPriceRow; ?></td>
					</tr>
					<?php
                        }
                    }
                ?>

					<?php

		$target = 13 - $numOfRows;
	 
		for($i=0;$i<$target;$i++){ ?>
					<tr class="productsRow">
						<td align="left"><span class="palletid">.</span></td>
						<td align="left"><b class="species"></b></td>
						<td align="left"><b class="cut"></b></td>
						<td align="left"><b class="brand"></b></td>
						<td align="left"><b class="quantity"></b></td>
						<td align="left">
							<b class="unit"></b>
						</td>
						<td align="left">
							<b class="weight"></b>
						</td>
						<td align="left" class="price"></td>
						<td align="right" class="price"></td>
					</tr>
					<?php } ?>

					<tr class="productsHeading" style="background-color: #7fabce9e;">
						<th align="left" colspan="7">Total:</th>
						<th align="center"><?php echo $total_qty_count; ?></th>
						<th align="left"></th>
						<th align="right"><?php echo $total_weight_count; ?>kg (+ <?php echo $total_case_count; ?>
							cases)</th>
						<th align="price" colspan="2" class="price"></th>
					</tr>
				</table>
				<!-- Bank Details Table with (1) Bank Detail (2) Circle Icon (3) Total Payables -->
				<div class="bankdetails">
					<table width="100%" border="0" style="background-color: #B0CAE1;">
						<tr>
							<td width="33%">Bank Details:</td>
							<td width="33%"></td>
							<td width="33%"></td>
						</tr>
						<tr style="background-color: #B0CAE1;">
							<td class="bankdetails" width="33%">
								<div class="bankbox" style="background-color: #B0CAE1;">
									<div class="col1">
										<p style="font-size: 12px;line-height: 12px">Town and Country Meats Group Ltd.<br />
											Bank: HSBC<br />
											Sort Code: 40 47 11<br />
											Account No: 23951332</p>
									</div>
							</td>
							<td align="centre">
								<div class="col2" align="center">
									<div style="text-align:center; align-items:center;">
										<img
											src="data:image/jpeg;base64,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" />
									</div>
								</div>
							</td>
							<td style="text-align: right;">
								<div class="col3">
									<div class="totalPayable"><b>Total Payable:</b> <span
											class="payvalue"><b>£<?php echo number_format((float)$totalPrice, 2, '.', ''); ?></b></span>
									</div>
									<div class="paymentDue">Payment due by: <span
											class="payvalue"><?php echo $payByDate; ?></span></div>
								</div>
							</td>
						</tr>
					</table>
				</div>

				<table>
					<tr>
						<td style="width: 30%">
							<div class="col footerlogo">
								<img style="height: 45px;"
									src="data:image/png;base64,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" />
								<img src="data:image/png;base64,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"
									style="height: 45px;">
								<img style="height: 45px;"
									src="data:image/png;base64,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" />
								<img style="height: 45px;"
									src="data:image/jpeg;base64,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" />
							</div>
						</td>
						<td style="width: 40%; font-size: 10px; text-align:center">
							<div class="col">
								<p>All goods remain the property of Town and Country Meats Group Ltd until paid for in
									full.</p>
								<p>Any claims must be notified within 24 hours of delivery by e-mail to:</p>
								<p>gemma@townandcountrymeats.co.uk</p>
								<p>office@townandcountrymeats.co.uk</p>
							</div>
						</td>
						<td>
							<div class="col">
								<div class="signbox">
								</div>
							</div>
						</td>
					</tr>
				</table>
			</div>
</div>
</main>
<div id="box" style="display:none;"></div>
<div id="editBox" style="display:none;"></div>
</div>
</body>
</html>
<script>
	$(document).ready(function () {
		var totalIntakeWeight = 0.0;

		$('.aWeight').each(function () {
			totalIntakeWeight = parseFloat(totalIntakeWeight) + parseFloat($(this).val());

			// var xxD = parseFloat(totalIntakeWeight).toFixed(2);

		});

		var xxD = parseFloat(totalIntakeWeight).toFixed(3);

		$('#intakeTotalWeightA').text(xxD + ' KG');

	});

	function resendInvoice() {

		if (confirm('Are you sure you want to send an email copy of this invoice?')) {

			$.get("<?php echo $domain; ?>ajax/generatePDFinvoice.php?id=<?php echo $_GET['id']; ?>", function (data,
				status) {

				var name = data.replace(/\s+/g, '');
				var link = 'PDF/' + name;

				window.location.href = "/email_invoice.php?id=<?php echo $pickersheet_id; ?>&link=" + link;
			});
		} else {

		}
	}
	<?php
	if ($_GET['msg'] != '') {
	?>
		alert('<?php echo $_GET['
			msg ']; ?>'); <?php
	} ?>
	function togglePrices() {
		$('.price').toggle('');
	}


	function editWeight(intake_id, pallet_id, product_id, weight_id) {
		console.log('intake_id ' + intake_id);
		console.log('pallet_id ' + pallet_id);
		console.log('product_id ' + product_id);
		console.log('weight_id ' + weight_id);


		$.get("/ajax/getEditProduct.php?intake_id=" + intake_id + "&pallet_id=" + pallet_id + "&product_id=" + product_id +
			"&weight_id=" + weight_id,
			function (data) {
				$('#editBox').html(data);
				$('#editBox').fadeIn();
			});


	}

	$('#updateIntakeButton').click(function () {

		var supplier_id = $('#supplier_id').val();
		var vehicle_reg = $('#vehicle_reg').val();
		var date_received = $('#date_received').val();
		var vehicle_temperature = $('#vehicle_temp').val();
		var delivery_note_number = $('#delivery_note_number').val();

		var good = 1;
		var msg = "";

		if (vehicle_reg == '') {
			msg = "The highlighted fields cannot be blank!";
			$('#vehicle_reg').css('border', '2px solid red');
			good = 0;
		} else {
			$('#vehicle_reg').css('border', '1px solid grey');
		}

		if (date_received == '') {
			msg = "The highlighted fields cannot be blank!";
			$('#date_received').css('border', '2px solid red');
			good = 0;
		} else {
			$('#date_received').css('border', '1px solid grey');
		}

		if (vehicle_temperature == '') {
			msg = "The highlighted fields cannot be blank!";
			$('#vehicle_temp').css('border', '2px solid red');
			good = 0;
		} else {
			$('#vehicle_temperature').css('border', '1px solid grey');
		}

		if (delivery_note_number == '') {
			msg = "The highlighted fields cannot be blank!";
			$('#delivery_note_number').css('border', '2px solid red');
			good = 0;
		} else {
			$('#delivery_note_number').css('border', '1px solid grey');
		}

		$('#msgNotice').html(msg);

		if (good == 1) {
			$('#updateIntakeInfo').submit();
		}
	});

	function deleteProduct(product_id, cut_id) {
		console.log(product_id);
		console.log(cut_id);
	}

	function palletDetail(id) {

		$('.palletDetail-' + id).toggle();
	}

	function printPallet(intake_id, pallet_id) {
		window.location.href = "http://tandc.phenixdevelopment.co.uk/printContent.php?intake_id=" + intake_id +
			"&pallet_id=" + pallet_id;
	}

	function printIntake(intake_id) {
		$.ajax({
			type: "POST",
			url: 'printIntake.php?intake_id=' + intake_id,
			type: 'get',
			success: function (response) {

				var contents = response;
				var idname = name;

				var frame1 = document.createElement('iframe');
				frame1.name = "frame1";
				frame1.style.position = "absolute";
				frame1.style.top = "-1000000px";
				document.body.appendChild(frame1);

				var frameDoc = frame1.contentWindow ? frame1.contentWindow : frame1.contentDocument.document ?
					frame1.contentDocument.document : frame1.contentDocument;

				frameDoc.document.open();
				frameDoc.document.write(
					'<html><head><meta http-equiv="Content-Type" content="text/html; charset=euc-kr"><title></title>'
				);




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

	function printContent(el) {
		var restorepage = $('body').html();
		var printcontent = $('#' + el).clone();
		$('body').empty().html(printcontent);
		window.print();
		// $('body').html(restorepage);

		setTimeout(
			function () {
				window.location.reload(1);
			}, 10000);
	}

	function palletDetail(id) {

		$('.palletDetail-' + id).toggle();
	}

	function openAddPallet(intake_id) {

		$.get("/ajax/addPalletForm.php?intake_id=" + intake_id, function (data) {
			// console.log(data);
			// $('#cut_id').html('<option></option>');
			$('#box').html(data);
		});

		// $('#add_to_pallet_id').val(pallet_id);
		// $('.add_to_pallet_id').html('0000' + pallet_id);
		$('#box').fadeIn();
	}


	function openAddtoPallet(intake_id, pallet_id) {

		$.get("/ajax/editPalletForm.php?intake_id=" + intake_id + "&pallet_id=" + pallet_id, function (data) {
			// console.log(data);
			// $('#cut_id').html('<option></option>');
			$('#box').html(data);
		});

		// $('#add_to_pallet_id').val(pallet_id);
		// $('.add_to_pallet_id').html('0000' + pallet_id);
		$('#box').fadeIn();
	}

	function deleteRow(intake_id, pallet_id) {
		if (confirm('Are you sure you want to delete this?')) {
			window.location.href = "/scripts/deletePallet.php?intake_id=" + intake_id + "&pallet_id=" + pallet_id;
			// console.log(intake_id + '  ' + pallet_id);
		}
	}

	function printStuff() {

		$.get("<?php echo $domain; ?>ajax/markInvoiceAsPrinted.php?id=<?php echo $_GET['id']; ?>", function (data,
			status) {
			console.log(data);
			$('#top').hide();
			$('.printhide').hide();
			$('.formBackButton').hide();
			$('.backbtn').hide();
			$('main').css('padding', '0px')

			window.print();
		});

	}

	function printCompleted() {
		$('#top').show();
		$('.printhide').show();
		$('.formBackButton').show();
		$('.backbtn').show();
		$('main').removeAttr("style")
	}

	// printContent(1);

	function printContent(id) {
		$.ajax({
			type: "POST",
			url: 'printContent.php?id=' + id,
			type: 'get',
			success: function (response) {

				var contents = response;
				var idname = name;

				var frame1 = document.createElement('iframe');
				frame1.name = "frame1";
				frame1.style.position = "absolute";
				frame1.style.top = "-1000000px";
				document.body.appendChild(frame1);

				var frameDoc = frame1.contentWindow ? frame1.contentWindow : frame1.contentDocument.document ?
					frame1.contentDocument.document : frame1.contentDocument;

				frameDoc.document.open();
				frameDoc.document.write('<html><head><title></title>');

				frameDoc.document.write(
					'<style>table {  border-collapse: collapse;  border-spacing: 0; width:100%; margin-top:20px;} .table td, .table > tbody > tr > td, .table > tbody > tr > th, .table > tfoot > tr > td, .table > tfoot > tr > th, .table > thead > tr > td, .table > thead > tr > th{ padding:8px 18px;  } .table-bordered, .table-bordered > tbody > tr > td, .table-bordered > tbody > tr > th, .table-bordered > tfoot > tr > td, .table-bordered > tfoot > tr > th, .table-bordered > thead > tr > td, .table-bordered > thead > tr > th {     border: 1px solid #e2e2e2;} </style>'
				);

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
<?php
if($customerRow['pricedefault'] == '0'){
	?><script>
	//$('.price').hide(); 
</script> <?php 
	}
?>
<style type="text/css">
	.printICON span {
		font-size: 18px;
		text-transform: uppercase;
		font-weight: 700;
		padding-left: 10px;
	}

	.printICON {
		font-size: 24px !important;
	}

	.printICON:active {
		color: #3faddd;
	}

	a {
		text-decoration: none !important;
	}
</style>