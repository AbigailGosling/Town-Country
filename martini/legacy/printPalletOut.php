<?php
	include('includes/frontHeader.php');
	
	$pickersheet_id = request('pickersheet_id');
	$palletsOutID = request('palletsOutID');
	
	
	
	$id = request('intake_id');
	$intake_id = request('intake_id');	
	$pallet_id = request('pallet_id');	
	
	$intake = getIntake($id);
	
	$supplier = getSupplier($intake['supplier_id']);
	
	$x = "SELECT * FROM `pickerSheets` WHERE id =?";
	$y = prepareExecuteQuery($x,'i',[$pickersheet_id]);
	
	$row = mysqli_fetch_array($y);
	
	$customer_id = $row['customer_id'];
	
	
	
	
	$x = "SELECT * FROM `customers` WHERE id =?";
	$y = prepareExecuteQuery($x,'i',[$customer_id]);
	$customer = mysqli_fetch_array($y);
	
	
?>	
<main>
	<a style="position:absolute;vertical-align:top;left:0px;top:20px;" href="viewPickSheet.php?id=<?php echo $pickersheet_id; ?>">Back</a>
	<div style="display:block;float:left;">
		<h1 style="font-family: 'OpenSans_Semibold' !important;font-weight: 700;color: #000;padding-bottom:20px;font-size: 26px;text-align: left;">
		Order Number: #0000<?php echo $pickersheet_id; ?>
		</h1>
	</div>
	
	<div style="display:block;float:right;">
	<h1 style="float:right;vertical-align:top;font-family: 'OpenSans_Semibold' !important;font-weight: 700;color: #000;padding-bottom:20px;font-size: 26px;text-align: left;">
		Outgoing Pallet Note #0000<?php echo $palletsOutID; ?>
	</h1>
	
	</div>
	<div class="overview" style="display:block;">
		<div class="overview_block">
			<label>Customer Name</label>
			<?php echo $customer['businessname']; ?>
		</div>
		
		<div class="overview_block">
			<label>Delivery Address</label>
			<?php echo $customer['deliveryaddress']; ?>
		</div>
		
		<div class="overview_block" style="display:none;">
			<div style="width:50%;display:inline-block;float:left;">
			<label>Vehicle Temp</label>
			<?php echo $intake['vehicle_temperature']; ?>&deg;C
			</div>
			<div style="width:50%;display:inline-block;float:left;">
			<label>Product Temp</label>
			<?php echo $intake['product_temperature']; ?>&deg;C
			</div>
		</div>
		
		<div style="clear:both;"></div>
	</div>
	<div class="clearfix"></div>
	
	<div id="product_list">
		<br/><Br/>
		<?php
			$x = "SELECT * FROM `productsOut` WHERE palletout_id=?";
			$y = prepareExecuteQuery($x,'i',[$palletsOutID]);
			
			while($row = mysqli_fetch_array($y)){
				
				$theProductsID = $row['products'];
				$pallet_id = $row['pallet_id'];
			}
			?>
</main>
<script>
	// print();
	
	// $('#closeAddPalletEditForm').click(function(){
		// $('#editBox').fadeOut();
	// });
	
	// $('#closeAddPallet').click(function(){
		// $('#editBox').fadeOut();
		// $('#box').fadeOut();
		// $('#box2').fadeOut();
	// });
	
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
</style>
</body>
</html>