<?php
	include('includes/frontHeader.php');
	require_once("ajax/customer_soa_results_function.php");
	require_once("scripts/SLabsEmailer.php");
	use InternalScripts\SLabsEmailer;
	use InternalScripts\SLabsEmailerType;
	$pickersheet_id = request()->input('id');
	
	$x = "SELECT * FROM `pickerSheets` WHERE id=?";
	$y = prepareExecuteQuery($x,'i',[$pickersheet_id]);
	$pickSheetRow = $y->fetch_assoc();
	
	$customer_id = $pickSheetRow['customer_id'];
	
	$x2 = "SELECT * FROM `customers` WHERE id=?";
	$y2 = prepareExecuteQuery($x2,'i',[$customer_id]);
	
	$customerRow = $y2->fetch_assoc(); 
	

	if(request()->input('deleteInternalDocument') !== null && $user['user_type'] == 'A'){
		$internal_doc_id = request()->input('deleteInternalDocument');
		$pickersheet_id = request()->input('id');

		prepareExecuteQuery("DELETE FROM `pickersheet_documents` WHERE id=? LIMIT 1",'i',[$internal_doc_id]);

		header('Location: deliverynote.php?id=' . $pickersheet_id);
	}

	$creditCheck = precredit_check($customer_id);
?>
<div id="top">
	<a href="menu.php" id="menu">MENU</a>
	<a href="logout" id="logout">LOGOUT</a>
</div>
<script type="text/javascript">

</script>
	<a href="<?php echo $domain; ?>deliverynoteList.php" class="backbtn">< Back</a>
<main class="int int--extra-padding">	
<?php
	if ($creditCheck['overcredit'] && !$customerRow['allowPrint']) {
		$admin_email = prepareExecuteQuery("SELECT * FROM `mail_tracking` WHERE document_id = ? AND `type` = ?",'is',[$pickersheet_id,SLabsEmailerType::CrdtAlert]);
		if ($admin_email->num_rows == 0)
		{
			$admin_email = prepareExecuteQuery("SELECT `key_value` FROM `system_settings` WHERE `key_name` = 'CREDIT_ALERT_EMAIL'");
			
			$admin_email = $admin_email->fetch_assoc();
			$admin_email = $admin_email['key_value'];
			$subject = "CREDIT ALERT: ".$customerRow['businessname']." cannot progress with delivery $pickersheet_id.";
			$htmlBody= $customerRow['businessname']." has passed their credit limit and a block has been placed on delivery $pickersheet_id";
			SLabsEmailer::send_email($customer_id,SLabsEmailerType::CrdtAlert,array($admin_email),$subject,$htmlBody,'','',$pickersheet_id);
		}
?>
	<div class="row custom-warning-box" id="warning" style="background:#ff6666; border: 2px solid #ff0000">
		<?php echo $creditCheck['message']; ?>
	</div>
<?php
	} else {
?>
	<div class="formBackButton" style="float:right;font-size:22px;">
		<a href="viewCompletedPickSheet.php?id=<?php echo $pickersheet_id; ?>">Pick Note</a>|
		<a href="javascript:;" onclick="printStuff()">Print &nbsp;</a>|
		<a href="javascript:;" onclick="generatePDF()">PDF Copy</a>
	</div>
	<?php
	}
?>
	<div id="print">
	<div class="topheading">
		<a href="#" id="sample" style="display:none;">test</a>
		 
		 <div class="logocontainer">
			<img class="logo" src="https:<?php echo $domain; ?>images/tandclogo.jpg">
			13-17 Landport Ind. Est. Landport Road<br/>
			Wolverhampton WV2 2QJ<br/>
			<span>Vat. No: 701 075 285</span><br/>
			<span>Company Reg. No. 12192223</span><br/>
			<b>01902457924</b><br/>
				
		</div>
		
		<div class="invoice">
			 
			<b style="font-size:10px;color:#8c8c8c;">Invoice address</b>
			<div class="invoicebox">
				<?php
					$customer_id = $pickSheetRow['customer_id'];
					$x = "SELECT * FROM `customers` WHERE id=?";
					$y = prepareExecuteQuery($x,'i',[$customer_id]);
					$customer = $y->fetch_assoc();
					
				?>
				<p>
					<?php echo $customer['businessname']; ?><br/>
					t/a <?php echo $customer['tradingas']; ?><br/>
					<?php echo $customer['accounts_address_1']; ?><br/>
					<?php echo $customer['accounts_address_2']; ?><br/>
					<?php echo $customer['accounts_address_3']; ?><br/>
                    <?php echo $customer['accounts_address_4']; ?><br/>
                    Customer ID: <?php echo str_pad($customer['id'], 4, '0', STR_PAD_LEFT); ?><br/>
				</p>
				<span style="display:none;">Account No: 1123ml</span>
			</div>
		</div>
		
 		
		<div class="delivery">
			<div class="deliverybox" style="border:0px;">
				<div class="po">Delivery No: <span>000<?php echo $pickersheet_id; ?></span></div>
				<h2>Delivery note</h2>
			</div>
			<br/>
			<div class="deliverydate">Delivery Date: <span class="date"><?php echo $pickSheetRow['estimated_delivery_date']; ?></span></div>
			<div class="deliverydate">P.O. Number: <span><?php echo $pickSheetRow['orderReferenceNumber']; ?></span></div>
			<?php
				$date = str_replace('/', '-', $pickSheetRow['date_completed']);
				$assemblydate = date('d/m/Y', strtotime($date));
				
				$date = DateTime::createFromFormat('d/m/Y', ''.$assemblydate);
				
				$paydayDelay = $customerRow['credit_terms'];
				
				$date->modify('+'. $paydayDelay .' day');
				$payByDate = $date->format('d/m/Y');
   			?>
 			<div class="po">Assembled: <span><?php echo $assemblydate; ?></span></div>
			<b style="color: #8c8c8c;font-size: 12px;">Delivery address</b>
			<div class="deliverybox">
				<p>
					<?php echo $customer['businessname']; ?><br/>
					t/a <?php echo $customer['tradingas']; ?><br/>
					<?php
						
						if($pickSheetRow['addressid'] == ''){ $pickSheetRow['addressid'] = 1; }

						echo $customer['address'.$pickSheetRow['addressid'].'_1'] . '<br/>';
						echo $customer['address'.$pickSheetRow['addressid'].'_2'] . '<br/>';
						echo $customer['address'.$pickSheetRow['addressid'].'_3'] . '<br/>';
						echo $customer['postcode_'.$pickSheetRow['addressid'].''] . '<br/>';

						
					?>
				</p>
				<span><?php echo $pickSheetRow['comments']; ?></span>
			</div>
		</div>
	</div>
	<?php if($user['user_type'] == 'A'){ ?>
	<br/>
	<form id="mainForm" class="printhide" method="POST" action="scripts/addInternalDocument.php" enctype="multipart/form-data" style="padding:10px;background: #f9f9f9;border: 1px solid #333;">
		<input type="hidden" name="type" value="DELIVERY_NOTE">
		<input type="hidden" name="pickersheet_id" value="<?php echo $pickersheet_id; ?>">

		<table>
			<tr>
				<td colspan="4" align="left">
					<h3 style="margin:0;">Add a document/message</h3>
					<br/>
				</td>
			</tr>
			<tr>
				<td>
					<label>Note</label><br/>
					<input type="text" name="message">
				</td>
				<td style="padding-left:10px;">
					<label>Document</label><br/>
					<input type="file" name="dfile">
				</td>
				<td><br/>
					<input type="button" onclick="mainForm()" value ="Submit"></input>
				</td>
			</tr>
		</table>
		<?php
			$internalDocResult = prepareExecuteQuery("SELECT * FROM `pickersheet_documents` WHERE type='DELIVERY_NOTE' && pickersheet_id=? ORDER BY id DESC",'s',[$pickersheet_id]);
			$internalDocCount = $internalDocResult->num_rows;

			if($internalDocCount > 0){
		?>
		<br/>
		<table width="100%" border="0">
			<tr class="productsHeading">
				<th align="left">Message</th>
				<th align="left">User</th>
				<th align="right">Action</th>
			</tr>
			<?php
				while($internalDoc = $internalDocResult->fetch_assoc()){
				?>
				<tr style="height:30px;">
					<td>
						<?php
							echo $internalDoc['message'];

							if($internalDoc['dfile'] != ''){
							?> <a href="docs/<?php echo $internalDoc['dfile']; ?>" target="_blank">(View Document)</a><?php
							}
						?>
					</td>
					<td><?php echo getUsername($internalDoc['user_id']); ?></td>
					<td align="right">
						<a href="?id=<?php echo $pickersheet_id; ?>&deleteInternalDocument=<?php echo $internalDoc['id']; ?>">Delete</a>
					</td>
				</tr>
				<?php
				}
			?>
		</table>
		<?php } ?>
 	</form>
	<br/><br/>
	<?php } ?>

	<table width="100%" border="0">
		<tr class="productsHeading">
			<th align="left">Intake ID</th>
			<th align="left">Plt ID</th>
 			<th align="left" colspan="5"></th>
			<th align="center">Qty</th>
			<th align="left">Unit</th>
            <th align="right">Weight</th>
            <?php if($customerRow['pricedefault'] == 1){ ?>
			    <th align="price" class="price">Price</th>
                <th align="right" class="price">Total</th>
            <?php } ?>
         </tr>
          
         <?php
		 		$numOfRows = 0;
                $outpalletQuery = "SELECT * FROM `palletsOut` WHERE pickersheet_id=?";
                $outpalletResult2 = prepareExecuteQuery($outpalletQuery,'i',[$pickersheet_id]);
                
                $outpalletCount = $outpalletResult2->num_rows;
				
				$total_qty_count = 0;
				$total_weight_count = 0;
				$total_case_count = 0;

                while($outpallet = $outpalletResult2->fetch_assoc()){
                    $weightids = explode(',', $outpallet['weight_ids']);
					
                    $productIDArray = array();
						
                    foreach($weightids as $weightid){
                        $x = "SELECT * FROM `weights` WHERE id=?";
                        $y = prepareExecuteQuery($x,'i',[$weightid]);
                        $weight = $y->fetch_assoc();
                       
                        if(!in_array($weight['product_id'], $productIDArray)){
                            array_push($productIDArray, $weight['product_id']);
                        }

                        $queryBits .= ' id = ' . $weightid . ' || ';
                    }
 
                    foreach($productIDArray as $productID){

                        $x1 = "SELECT * FROM `product` WHERE id=?";
                        $y1 = prepareExecuteQuery($x1,'i',[$productID]);
                        $product = $y1->fetch_assoc();
                         

                        if($product['unit'] == 'PPC'){
                            $ext = ' Cases';
                        }else{
                            $ext = ' kg';
                        }
						$queryVars = $weightids;
						array_unshift($queryVars,$productID);
                        $x2 = "SELECT * FROM `weights` WHERE product_id=? AND id IN (".implode(",",array_fill(0,count($weightids),"?")).")";

                        $y2 = prepareExecuteQuery($x2,str_repeat('i',count($weightids)+1),$queryVars);
                        $count = $y2->num_rows;

                        ${"globalProductCount" . $product['id']} += $count;
                         
                        $k = 0;

                        while($weight = $y2->fetch_assoc()){
                            
                            if($weight['weight_tear'] == $weight['weight_gross']){
                                (double)$w = (double)$weight['weight_gross'];
                            }else{
                                (double)$w = (double)$weight['weight_gross'] - (double)$weight['weight_tear'];
                            }

                            $k = $k + $w;
                        }

						$total_qty_count += $count;
                        ?>
                        <tr class="productsRow">
						<?php $numOfRows++; ?>
					<td align="center"><span class="palletid"><?php echo intakeIDfromPalletID($product['pallet_id']); ?></span></td>
					<td align="center"><span class="palletid"><?php echo $product['pallet_id']; ?></span></td>
					<td align="center"><span class="palletid"><?php echo getNationality($product['nationality_id']); ?></span></td>
					<td align="center"><span class="chilled"><?php echo getTemp($product['cooling_id']); ?></span></td>
					<td align="left"><b class="species"><?php echo getSpeciesFromCutID($product['cut_id']); ?></b></td>
					<td align="left"><b class="cut"><?php echo getCut($product['cut_id']); ?></b></td>
					<td align="center"><b class="brand"><?php echo getBrand($product['brand_id']); ?></b></td>
                    <?php
                        $productID = $product['id'];
                        $howManyX = "SELECT * FROM `pickerItems` WHERE pickersheet_id=? AND product_id=?";
                        $howManyY = prepareExecuteQuery($howManyX,'ii',[$pickersheet_id,$productID]);
                        $pickerItem = $howManyY->fetch_assoc();
                        $howMany = $howManyY->num_rows;
                    ?>
					<td align="center"><b class="quantity"><?php echo $count; ?></b></td>
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
					<td align="right">
					<b class="weight">
                      <?php
					  
						$qBit = '';
						
						$kg = 0;
						$queryVars = $weightids;
						array_unshift($queryVars,$productID);
						$xxWeight = "SELECT * FROM `weights` WHERE  product_id=? AND id IN (".implode(",",array_fill(0,count($weightids),"?")).")";
						$yyWeight = prepareExecuteQuery($xxWeight,str_repeat('i',count($weightids)+1),$queryVars);
						
						while($weightRow = mysqli_fetch_array($yyWeight)){
							
							if($weightRow['weight_tear'] == $weightRow['weight_gross']){
								(double)$tw = (double)$weightRow['weight_gross'];
							}else{
								(double)$tw = (double)$weightRow['weight_gross'] - (double)$weightRow['weight_tear'];
							}
							
							$kg = $kg + $tw;
							
							$kg = number_format($kg, 3, '.', '');
							
						}
						 
						
						if($product['unit'] == 'PPC'){
							echo $count . ' Cases';
							$total_case_count += $count;
						}else{
							echo $kg . ' kg';
							$total_weight_count += $kg;
						}
						
					  ?>
					  </b>
                    </td>
                    <?php if($customerRow['pricedefault'] == 1){ ?>
                        <td align="right" class="price">£<?php echo number_format((double)$pickerItem['price'], 2, '.', ''); ?></td>
						<?php if($product['unit'] == 'PPC'){
								$totalPrice += number_format((double)$count * $pickerItem['price'], 2, '.', '');
							?>
							<td align="right" class="price">£<?php echo number_format((double)$count * $pickerItem['price'], 2, '.', ''); ?></td>
						<?php }else{
								$totalPrice += number_format((double)$kg * $pickerItem['price'], 2, '.', '');
							?>
							<td align="right" class="price">£<?php echo number_format((double)$kg * $pickerItem['price'], 2, '.', ''); ?></td>
						<?php } ?>
                    <?php } ?>
				</tr>
                <?php
                    }
                }
            ?>

 
		
		<?php
		
		$target = 15 - $numOfRows;
	
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
 				</tr>
		<?php } ?>

		<tr class="productsHeading">
			<th align="left" colspan="7">Total:</th>
 			<th align="center"><?php echo $total_qty_count; ?></th>
			<th align="left"></th>
            <th align="right"><?php echo $total_weight_count; ?>kg (+ <?php echo $total_case_count; ?> cases)</th>
            <?php if($customerRow['pricedefault'] == 1){ ?>
			    <th align="price" colspan="2" class="price"></th>
            <?php } ?>
		</tr>
	</table>
  	 
		
		<div class="bankdetails">
			<b style="font-size: 10px;">Bank Details:</b>
			<div class="bankbox">
				<div class="col1">
					<p>Town and Country Meats Group Ltd.<br/>
					Bank: HSBC<br/>
					Sort Code: 40 47 11<br/>
					Account No: 23951332</p>
				</div>
				<div class="col2" align="center">
					<div>
                        <div class="bankcircle" style="margin-right:10px;">
                            UK<br/>WN070<br/>EC
                        </div>
                    </div>
				</div>
				<div class="col3">
                <?php if($customerRow['pricedefault'] == 1){ ?>    
                    <div class="totalPayable"><b>Total Payable:</b> <span class="payvalue"><b>£<?php echo number_format((double)$totalPrice, 2, '.', ''); ?></b></span></div>
                <?php } ?>
                    <div class="paymentDue">Payment due by: <span class="payvalue"><?php echo $payByDate; ?></span></div>
                     
				</div>
			</div>
		</div>
		
		<div class="bottom">
			<div class="col footerlogo">
 				<img class="one" src="https:<?php echo $domain; ?>images/footer1.png">
 				<img class="one" src="https:<?php echo $domain; ?>images/footer2.png" style="margin-left:5px;margin-right:5px;"><br/><br/>
				<img class="two" src="https:<?php echo $domain; ?>images/AIMS_LOGO_2008_002.gif">
				<img class="two" src="https:<?php echo $domain; ?>images/the-food-awards-england-2017-winner.jpg">
 			</div>
			
			<div class="col">
				<p>All goods remain the property of Town and Country Meats Group Ltd until paid for in full.</p>
				<p>Any claims must be notified within 24 hours of delivery by e-mail to:</p>
				<p>gemma@townandcountrymeats.co.uk</p>
				<p>office@townandcountrymeats.co.uk</p>
			</div>
			
			<div class="col">
				<div class="signbox">
					<span>Sign ..................................</span>
					<span>Print ..................................</span>
				</div>
			</div>
		</div>
	</div>
	
</main>
<div id="btm"></div>
<div id="box" style="display:none;">

</div>
<div id="editBox" style="display:none;">

</div>
<script>
	$.ajaxSetup({
		headers: { 'X-CSRF-TOKEN': "<?php echo csrf_token();?>" }
	});
	$(document).ready(function(){
		var totalIntakeWeight = 0.0;
		
		$('.aWeight').each(function() {
			totalIntakeWeight = parseFloat(totalIntakeWeight) + parseFloat($(this).val());
			
			// var xxD = parseFloat(totalIntakeWeight).toFixed(2);
		
		});
		
		var xxD = parseFloat(totalIntakeWeight).toFixed(3);
		
		$('#intakeTotalWeightA').text(xxD + ' KG');
	
	});
	
	function togglePrices(){
		$('.price').toggle('');
	}
	function mainForm(){
		$('#mainForm').ajaxSubmit({headers:{'X-CSRF-TOKEN': "<?php echo csrf_token();?>"},success:mainFormSucess});
	}
	function mainFormSucess(){
		location.reload();
	}
	function editWeight(intake_id, pallet_id, product_id, weight_id){
	
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
			xhttp.open("POST", $(formName).attr('action'));
			xhttp.setRequestHeader('X-CSRF-TOKEN', "<?php echo csrf_token();?>");
			xhttp.send($(formName).serialize());
		}
	});
	
	function deleteProduct(product_id, cut_id){
	}
	
	function palletDetail(id){
		
		$('.palletDetail-' + id).toggle();
	}
	
	function printIntake(intake_id){
		$.ajax({
			headers:{'X-CSRF-TOKEN': "<?php echo csrf_token();?>"},
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
			$('#box').html(data);
		});
		
		// $('#add_to_pallet_id').val(pallet_id);
		// $('.add_to_pallet_id').html('0000' + pallet_id);
		$('#box').fadeIn();
	}
	
	
	function openAddtoPallet(intake_id, pallet_id){
		
		$.get( "ajax/editPalletForm.php?intake_id=" + intake_id + "&pallet_id=" + pallet_id, function( data ) {
			$('#box').html(data);
		});
		
		// $('#add_to_pallet_id').val(pallet_id);
		// $('.add_to_pallet_id').html('0000' + pallet_id);
		$('#box').fadeIn();
	}
	
	function deleteRow(intake_id, pallet_id){
		if(confirm('Are you sure you want to delete this?')){
			window.location.href = "scripts/deletePallet.php?intake_id=" + intake_id + "&pallet_id=" + pallet_id;
		}
	}
	
	function hideItemsPrint(){
		$('#top').hide();
		$('.printhide').hide();
		$('.formBackButton').hide();
		$('.backbtn').hide();
		$('main').css('padding','0px');
	}

	function printStuff(){ // Print btn on menu

		$.get("ajax/markPickAsPrinted.php?id=<?php echo request()->input('id'); ?>", function(data, status){
			hideItemsPrint();
			window.print();
		});

	}

	function beforePrint(){ // CTRL + P
		
		$.get("ajax/markPickAsPrinted.php?id=<?php echo request()->input('id'); ?>", function(data, status){
			hideItemsPrint();
		});
	
	}

	function printCompleted() {
		$('#top').show();
		$('.printhide').show();
		$('.formBackButton').show();
		$('.backbtn').show();
		$('main').removeAttr("style")
	}

	
	function generatePDF(){
		$.get("ajax/generatePDFdeliveryNote.php?id=<?php echo request()->input('id'); ?>", function(data, status){
			
			var name = data.replace(/\s+/g, '');
			
			downloadURI('<?php echo $domain; ?>PDF/' + name, name);
			
		});
	}
	
	function downloadURI(uri, name) 
	{
		var link = document.createElement("a");
		link.download = name;
		link.href = uri;
		link.click();
	}
	
	// printContent(1);
	
	function printContent(id){
	   $.ajax({
		headers:{'X-CSRF-TOKEN': "<?php echo csrf_token();?>"},
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
<?php
if($customerRow['pricedefault'] == '0'){
	?><script> $('.price').hide(); </script> <?php 
	}
?>
<style type="text/css">
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
</body>
</html>