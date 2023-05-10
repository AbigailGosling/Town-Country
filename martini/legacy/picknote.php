<?php
	include('includes/frontHeader.php');
	
	$pickersheet_id = request()->input('id');
	
	$x = "SELECT * FROM `pickerSheets` WHERE id=?";
	$y = prepareExecuteQuery($x,'i',[$pickersheet_id]);
	$pickSheetRow = mysqli_fetch_array($y);
	
	$customer_id = $pickSheetRow['customer_id'];
	
	$x2 = "SELECT * FROM `customers` WHERE id='$customer_id'";
	$y2 = prepareExecuteQuery($x2);
	
	$customerRow = mysqli_fetch_array($y2);
	
?>
<div id="top">
	<a href="menu.php" id="menu">MENU</a>
	<a href="logout" id="logout">LOGOUT</a>
</div>
<script type="text/javascript">
	
</script>
<main class="int">
	
	<a href="<?php echo $domain; ?>viewPickSheet.php?id=<?php echo request()->input('id'); ?>"class="backbtn"  onclick="goBack()">< Back</a>

	<div class="formBackButton" style="float:right;font-size:22px;">
		<a href="viewPickSheet.php?id=<?php echo $pickersheet_id; ?>">Back to Picksheet</a>|
		<a href="javascript:;" onclick="print()">Print</a>
	</div>
	<h2 style="float:right;font-size:22px;">Pick note 000<?php echo $pickersheet_id; ?></h2>
	<div class="overview">
		<div class="overview_block" style="width:50%;text-align:left;">
			<label style="">Company Address</label>
			11/13-17 Landport Ind. Est.<br/>
			Landport Road<br/>
			Wolverhampton WV2 2QJ<br/>
			01902457924<br/>
			BRC 167017
		</div>
		
		<div class="overview_block" style="width:50%;text-align:left;">
			<label style="">Delivery Date</label>
			<?php echo $pickSheetRow['estimated_delivery_date']; ?>
		</div>
		
		<div class="overview_block" style="width:50%;text-align:left;">
			<label>Invoice Address</label>
			<div style="width:120px;"><?php echo $customerRow['billingaddress']; ?></div>
		</div>
		
		<div class="overview_block" style="width:50%;text-align:left;">
			<label>Delivery Address</label>
			<div style="width:120px;"><?php echo $customerRow['deliveryaddress']; ?></div>
		</div>
		
		<div style="clear:both;"></div>
	</div>
	<br/><br/>
	
	<table width="100%" border="0">
		<tr>
			<th align="left">Plt ID</th>
			<th align="left">Species</th>
			<th align="left">Product</th>
			<th align="left">Brand</th>
			<th align="left">Quantity</th>
			<th align="left">Unit</th>
			<th align="left">Weight</th>
        </tr>
        



        <?php
                $outpalletQuery = "SELECT * FROM `palletsOut` WHERE pickersheet_id=?";
                $outpalletResult2 = prepareExecuteQuery($outpalletQuery,'i',[$pickersheet_id]);
                
                $outpalletCount = mysqli_num_rows($outpalletResult2);

                while($outpallet = mysqli_fetch_array($outpalletResult2)){
                    $weightids = explode(',', $outpallet['weight_ids']);
 
                    $productIDArray = array();
						
                    foreach($weightids as $weightid){
                        $x = "SELECT * FROM `weights` WHERE id=?";
                        $y = prepareExecuteQuery($x,'i',[$weightid]);
                        $weight = mysqli_fetch_array($y);
                       
                        if(!in_array($weight['product_id'], $productIDArray)){
                            array_push($productIDArray, $weight['product_id']);
                        }

                        $queryBits .= ' id = ' . $weightid . ' || ';
                    }
 
                    foreach($productIDArray as $productID){

                        $x1 = "SELECT * FROM `product` WHERE id=?";
                        $y1 = prepareExecuteQuery($x1,'i',[$productID]);
                        $product = mysqli_fetch_array($y1);
                         

                        if($product['unit'] == 'PPC'){
                            $ext = ' Cases';
                        }else{
                            $ext = ' kg';
                        }

                        $x2 = "SELECT * FROM `weights` WHERE product_id=? AND id IN (".implode(",",$weightids).")";

                        $y2 = prepareExecuteQuery($x2,'i',[$productID]);
                        $count = mysqli_num_rows($y2);

                        ${"globalProductCount" . $product['id']} += $count;
                         
                        $k = 0;

                        while($weight = mysqli_fetch_array($y2)){
                            
                            if($weight['weight_tear'] == $weight['weight_gross']){
                                $w = (double)$weight['weight_gross'];
                            }else{
                                $w = (double)$weight['weight_gross'] - (double)$weight['weight_tear'];
                            }

                            $k = $k + $w;
                        }
                        ?>
                        <tr class="productsRow">
					<td align="left"><span class="palletid"><?php echo $product['pallet_id']; ?></span></td>
 					<td align="left"><b class="species"><?php echo getSpeciesFromCutID($product['cut_id']); ?></b></td>
					<td align="left"><b class="cut"><?php echo getCut($product['cut_id']); ?></b></td>
					<td align="left"><b class="brand"><?php echo getBrand($product['brand_id']); ?></b></td>
				 
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

						$xxWeight = "SELECT * FROM `weights` WHERE product_id=? AND id IN (".implode(",",$weightids).")";
						$yyWeight = prepareExecuteQuery($xxWeight,'i',[$productID]);
						
						while($weightRow = mysqli_fetch_array($yyWeight)){
							
							if($weightRow['weight_tear'] == $weightRow['weight_gross']){
								$tw = (double)$weightRow['weight_gross'];
							}else{
								$tw = (double)$weightRow['weight_gross'] - (double)$weightRow['weight_tear'];
							}
							
							$kg = $kg + $tw;
							
							$kg = number_format($kg, 2, '.', '');
							
						}
						 
						
						if($product['unit'] == 'PPC'){
							echo $kg . ' Cases';
						}else{
							echo $kg . ' kg';
						}
						
					  ?>
					  </b>
					</td>
  					<?php $totalPrice += number_format((double)$kg * $pickerItem['price'], 2, '.', ''); ?>
				</tr>
                <?php
                    }
                }
            ?>


 
	</table>
	<br/><br/>
	
	<div class="overview">
		<div class="overview_block" style="width:50%;text-align:left;">
			<label>Bank Details</label>
			Town and Country Meats<br/>
			Sort Code: 40 10 39<br/>
			Account No: 40057924<br/>
			HSBC, High Street, Bilston, WV14 OEH
		</div>
		<div class="overview_block" style="width:50%;text-align:left;border:0px;">
	 
 			
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
	
	
	function editWeight(intake_id, pallet_id, product_id, weight_id){
		console.log('intake_id ' + intake_id);
		console.log('pallet_id ' + pallet_id);
		console.log('product_id ' + product_id);
		console.log('weight_id ' + weight_id);
		
		
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
	
	function printPallet(intake_id, pallet_id){
		window.location.href = "http://tandc.phenixdevelopment.co.uk/printContent.php?intake_id=" + intake_id + "&pallet_id=" + pallet_id;
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