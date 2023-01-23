<?php
	require(__DIR__.'/../functions.php');
	
	$intake_id = $mysqli->real_escape_string( request('intake_id'));
	
	$x = "SELECT * FROM intake WHERE id=?";
	$y = prepareExecuteQuery($x,'i',[$intake_id]);
	$intake = mysqli_fetch_array($y);
?>	
<a href="javascript:;" class="close closeAddPallet"></a>
<h1 class="int">Add a Pallet</h1>
<form method="POST" id="addPalletForm" action="script_addPallet.php" autocomplete="off">
<input autocomplete="off" name="hidden" type="text" style="display:none;">
    <div id="msgNotice2" style="color:white;padding: 0 0 0 20px;"></div>
	<div class="float">
	 
		<input type="text" style="display:none;" value="<?php echo $intake_id; ?>" name="intake_id">
		<div style="display:none;">
			<label>status</label>
			<select name="statuses_idq">
				<option value="0">Available</option>
				<option value="1">Sold</option>
			</select>
		</div>
		
	 
		
		<label>Pack Date</label>
		<input name="best_by" id="best_by" type="text" onfocus="blur()">
		<div onclick="bestByNA()" id="bestbyBtn">SET N/A</div>
		<div class="clearfix"></div>
		
		<label>UB/ BB</label>
		<select name="ubbb" id="ubbb">
			<option value="0">UB</option>
			<option value="1">BB</option>
			<option value="2" hidden>N/A</option>
			<option value="3">Process By</option>
			<option value="4">Expiry</option>
			<option value="5">Open By</option>
		</select>
		
		<div onclick="ubbbyNA()" id="ubbbBtn">SET N/A</div>
		<div class="clearfix"></div>
		
		<div id="best_by_range_from_container">
		<label>From</label>
		<input name="best_by_range_from" id="best_by_range_from" type="text" onfocus="blur()">
		</div>
		
		<div id="best_by_range_to_container">
		<label>To</label>
		<input name="best_by_range_to" id="best_by_range_to" type="text" onfocus="blur()">
		</div>
		 
		<label>Chilled/Frozen</label>
		<select name="temperature_id">
			<option selected="true" disabled></option>
			<?php
				$x = "SELECT * FROM temperature";
				$y = prepareExecuteQuery($x);
				while($row = mysqli_fetch_array($y)){
				?><option value="<?php echo $row['id']; ?>" <?php if($row['id'] == $pallet['temperature_id']){ echo 'selected'; } ?>><?php echo $row['temperature']; ?></option><?php
				}
			?>
		</select>
		<div>
		<label>Product Temp (°C)</label>
		<input name="product_temp" id="product_temp" type="text" required>
		</div>
		<label>Location</label>
		<select name="storage_location" id="storage_location">
				<option selected="true" disabled></option>
				<option value="Unit 11">Unit 11</option>
				<option value="Unit 13 - 14">Unit 13 - 14</option>
				<option value="Unit 23">Unit 23</option>
				<option value="Gatwick">Gatwick</option>
				<option value="Dry Store">Dry Store</option>
				<option value="Unit 15 - 17">Unit 15 - 17</option>
				<option value="Direct Drop">Direct Drop</option>
				<option value="Coldstore">Coldstore</option>
				<option value="Other">Other</option>
		</select>
		
		<label>comments</label>
		<textarea name="comments"></textarea>
		
		
		
	</div>
	<div class="float">
		<label>Nationality</label>
		<select name="nationality_id" id="nationality_id">
		<option>--</option>
		<?php
			$x = "SELECT * FROM nationality ORDER BY `name` ASC";
			$y = prepareExecuteQuery($x);
			while($row = mysqli_fetch_array($y)){
			?><option value="<?php echo $row['id']; ?>" <?php if($row['id'] == $pallet['nationality_id']){ echo 'selected'; } ?>><?php echo $row['name']; ?></option><?php
			}
		?>
		</select>
		
		<label>Brand Search</label>
		<input name="brand_search" id="brand_search" type="text">
		<div id="brand_search_results">
			asdf
		</div>
		<input name="brand_id" id="brand_id" type="text" style="display:none;">
		
		<label>species</label>
		<select name="species_id" id="species_id">
			<option value="--">--</option>
			<?php
				$x = "SELECT * FROM species ORDER BY `name` ASC";
				$y = prepareExecuteQuery($x);
				while($row = mysqli_fetch_array($y)){
				?><option value="<?php echo $row['id']; ?>" <?php if($row['id'] == $pallet['species_id']){ echo 'selected'; } ?>><?php echo $row['name']; ?></option><?php
				}
			?>
		</select>
		
 
		
		
		<label>cuts</label>
		<input name="cut_search" id="cut_search" type="text">
		<div id="cut_search_results">
			asdf
		</div>
		
		<input name="cut_id" id="cut_id" type="text" style="display:none;">
		<div style="display:none;">
			<select name="cut_ids" id="cut_id">
			<option value="--">--</option>
			</select>
		</div>
		
		<label>Units of measurement</label>
		<select name="unit" id="unit">
 			<option value="C">Case</option>
			<option value="PPC">Purchase Per Case</option>
			<option value="P">Gross-Tare &nbsp;&nbsp;&nbsp;&nbsp; Dolav/Cases</option>
			<option value="DS">Direct to store/customer</option>
 		</select>
		
		
		<div class="quantityWeightContainer">
		<label class="howManyUnitsDiv">HOW MANY UNITS</label>
		<input type="number" class="quantityWeight" onChange="updateForm()" id="quantityWeight" name="quantity">
		</div>
		
		<?php if($intake['returned'] == 1){ ?>
		<div>
			<label>Original Intake ID</label>
			<input type="number" name="original_intake_id">
		</div>

		<div>
			<label>Original Pallet ID</label>
			<input type="number" name="original_pallet_id">
		</div>
		<?php } ?>
		
		<div class="hideIfPacket">
			<div class="indiweights">
			<label class="standardcatchtext">Standard or catch weights?</label>
			<select name="individualweights" id="individualweights">
 				<option value="C">Catch Weights</option>
				<option value="S">Standard Weight</option>
				<option value="D">Dolav/Cases</option>
 				<option value="AKG">Advised kg</option>
			</select>
			</div>

			<div id="akgDiv" style="display:none;">
				<label>Net Weight</label>
				<input type="number" name="akg" id="akg">
			</div>
		
			<div id="SingleWeightDiv" style="display:none;">
				<label>Weight</label>
				<input type="number" name="single_weight_val" id="single_weight_val">
			</div>
		
			<div id="grossWeightDiv" style="display:none;">
				<label>Gross Weight</label>
				<input type="number" name="gross_weight_val" id="gross_weight_val">
			</div>
		
			<div id="tearWeightDiv" style="display:none;">
				<label>Pallet Tare</label>
				<input type="number" name="pallet_tare" id="pallet_tare">
				
				<label>Tare per carton</label>
				<input type="number" name="tare_per_carton" id="tare_per_carton">
				
				<label>Number of cartons</label>
				<input type="number" name="number_of_cartons" id="number_of_cartons">
				
				<br/><br/>
				<label>Net Weight</label>
				<input type="number" name="net_weight" id="net_weight">
				
				
				<div style="display:none;">
					<label>Tear Weight</label>
					<input type="number" name="tear_weight_val" id="tear_weight_val">
				</div>
			</div>
			
			<div id="directtostore" style="display:none;">
				<label>How Many Units</label>
				<input type="number" name="note_units" id="note_units">
				
				<label>Total Weight</label>
				<input type="number" name="note_weight" id="note_weight">
				
			</div>
			
			<?php if($intake['returned'] == 1){ ?>
			<div>
				<div class="binContainer"><input type="checkbox" name="bin" id="bin" value="1"><label style="display:inline-block;" for="bin">Bin</label></div>
			</div>
			<?php } ?>
		</div>
		
	</div>
	<div id="MultiWeightDiv" style="display:none;"> 
		
	</div>
	<br/>
	<h1 style="padding-left:19px;display:none;color:#FFF;font-size:18px;float:left;" id="totalCatchWeightContainer">Total Catch Weight: <span id="totalCatchWeight"></span></h1>
	<div class="clearfix"></div>
	
 	<input value="Add Pallet" onclick="addPallet();" type="button">
	
 
	<input value="Add Pallet & Duplicate " onclick="addPalletDuplicate();" type="button">
</form>


<script type="text/javascript">

	function calculateTare(){
		
		var gross_weight_val = $('#gross_weight_val').val();
		var pallet_tare = $('#pallet_tare').val();
		var tear_per_carton = $('#tare_per_carton').val();
		var number_of_cartons = $('#number_of_cartons').val();
		
		var tareWeight = (parseFloat(tear_per_carton) * parseFloat(number_of_cartons)) + parseFloat(pallet_tare);
		
		var grossWeight = parseFloat(gross_weight_val);
		
		$('#tear_weight_val').val(tareWeight);
		$('#net_weight').val(grossWeight - tareWeight);
	}
	
	$(document).ready(function(){
		
		$('#pallet_tare').keyup(function(){ calculateTare(); });
		$('#tare_per_carton').keyup(function(){ calculateTare(); });
		$('#number_of_cartons').keyup(function(){ calculateTare(); });
		
		<?php
			$start = date('Y', strtotime('-5 year'));
			$end = date('Y', strtotime('+5 year'));
		?>
		
		$( "#best_by" ).datepicker({
			dateFormat: 'dd/mm/yy',
			changeYear: true,
			yearRange: "<?php echo $start; ?>:<?php echo $end; ?>"
		});
		
		$( "#best_by_range_from" ).datepicker({
			dateFormat: 'dd/mm/yy',
			changeYear: true,
			yearRange: "<?php echo $start; ?>:<?php echo $end; ?>"
		});
		
		$( "#best_by_range_to" ).datepicker({
			dateFormat: 'dd/mm/yy',
			changeYear: true,
			yearRange: "<?php echo $start; ?>:<?php echo $end; ?>"
		});
		
		
		$('#cut_search').keyup(function(){
			var val = $('#cut_search').val();
			
			if(val != ''){
				$('#cut_search_results').fadeIn();
			}else{
				$('#cut_search_results').fadeOut();
			}
			
			var species = $('#species_id').val();
			
			var xhttp = new XMLHttpRequest();
			xhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
				
			 
				$('#cut_search_results').html(this.responseText);
 
			}
			};
			xhttp.open("POST", "ajax/getCutDropdown.php", true);
			xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xhttp.setRequestHeader('X-CSRF-TOKEN', "<?php echo csrf_token();?>");
			xhttp.send("searchterm=" + val + "&species_id=" + species);
		
		});

		$('#brand_search').keyup(function(){
			var val = $('#brand_search').val();
			
			if(val != ''){
				$('#brand_search_results').fadeIn();
			}else{
				$('#brand_search_results').fadeOut();
			}
			
			var xhttp = new XMLHttpRequest();
			xhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
				$('#brand_search_results').html(this.responseText);
			}
			};
			xhttp.open("POST", "ajax/getBrandDropdown.php", true);
			xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xhttp.setRequestHeader('X-CSRF-TOKEN', "<?php echo csrf_token();?>");
			xhttp.send("searchterm=" + val);
		
		});
		
	});
		
	window.setInterval(function(){
		countWeights();	
	}, 1000);
	
	function addPalletDuplicate(){
		var best_by = $('#best_by').val();
		var best_by_range_from = $('#best_by_range_from').val();
		var best_by_range_to = $('#best_by_range_to').val();
		var quantityWeight = $('#quantityWeight').val();
		
		var ubbb = $('#ubbb').val();
		
		var brand_id = $('#brand_id').val();
		if(brand_id == '--'){
			brand_id = '';
		}
		
		var species_id = $('#species_id').val();
		if(species_id == '--'){
			species_id = '';
		}
		
		var cut_id = $('#cut_id').val();
		if(cut_id == '--'){
			cut_id = '';
		}
		
		var unit = $('#unit').val();
		if(unit == '--'){
			unit = '';
		}
		
		
		var good = 1;
		var msg = "";
		
		if(best_by == ''){
			msg = "The highlighted fields cannot be blank!";
			$('#best_by').css('border','2px solid red');
			good = 0;
		}else{
			$('#best_by').css('border','1px solid grey');
		}
		
		if(ubbb != 2){
		
			if(best_by_range_from == ''){
				msg = "The highlighted fields cannot be blank!";
				$('#best_by_range_from').css('border','2px solid red');
				good = 0;
			}else{
				$('#best_by_range_from').css('border','1px solid grey');
			}
			
			if(best_by_range_to == ''){
				msg = "The highlighted fields cannot be blank!";
				$('#best_by_range_to').css('border','2px solid red');
				good = 0;
			}else{
				$('#best_by_range_to').css('border','1px solid grey');
			}
			
		}
		
		if(brand_id == ''){
			msg = "The highlighted fields cannot be blank!";
			$('#brand_id').css('border','2px solid red');
			good = 0;
		}else{
			$('#brand_id').css('border','1px solid grey');
		}
		
		if(species_id == ''){
			msg = "The highlighted fields cannot be blank!";
			$('#species_id').css('border','2px solid red');
			good = 0;
		}else{
			$('#species_id').css('border','1px solid grey');
		}
		
		if(cut_id == ''){
			msg = "The highlighted fields cannot be blank!";
			$('#cut_id').css('border','2px solid red');
			good = 0;
		}else{
			$('#cut_id').css('border','1px solid grey');
		}
		
		if(unit == ''){
			msg = "The highlighted fields cannot be blank!";
			$('#unit').css('border','2px solid red');
			good = 0;
		}else{
			$('#unit').css('border','1px solid grey');
		}
		
		
		if(quantityWeight == ''){
			msg = "The highlighted fields cannot be blank!";
			$('#quantityWeight').css('border','2px solid red');
			good = 0;
		}else{
			$('#quantityWeight').css('border','1px solid grey');
		}
		
		if($('#individualweights').val() == 'C'){
			$('.multiweight').each(function(){
				if($(this).val() == ''){
					good = 0;
					$(this).css('border','2px solid red');
					msg = "The highlighted fields cannot be blank!";
				}else{
					$(this).css('border','1px solid grey');
				}
			});
		}
		if($('#storage_location').val() == undefined || $('#storage_location').val() == ''){
			msg = "The highlighted fields cannot be blank!";
			$('#storage_location').css('border','2px solid red');
			good = 0;
		}else{
			$('#storage_location').css('border','1px solid grey');
		}
		if($('[name="temperature_id"]').val() == undefined || $('[name="temperature_id"]').val() == ''){
			msg = "The highlighted fields cannot be blank!";
			$('[name="temperature_id"]').css('border','2px solid red');
			good = 0;
		}else{
			$('[name="temperature_id"]').css('border','1px solid grey');
		}
		$('#msgNotice2').html(msg);
		
		if(good == 1){
			
			
			 $.ajax({ // make an AJAX request
				type: "POST",
				url: "script_addPallet.php?dupe=true", // it's the URL of your component B
				data: $('#addPalletForm').serialize(), // serializes the form's elements
				error: function(xhr, error){
					// console.log(xhr);
					// console.log(error);
					
					$('#networkError').fadeIn();
				},
				success: function(data)
				{	
					// units of measurement
					$('#unit').prop('selectedIndex',0);

					// how many units
					$('#quantityWeight').val('');
					
					// Standard or catch weights 
					$("#individualweights option[value=C]").show();
					$("#individualweights option[value=S]").show();
					$('#individualweights').prop('selectedIndex',0);

					// hide weight field
					$('#SingleWeightDiv').hide();

					// reset weight fields
					$('#tear_weight_val').val('');
					$('#gross_weight_val').val('');
					$('#single_weight_val').val('');
					$('#akg').val('');
					$('#MultiWeightDiv').html(''); 
					
					$('.palletidpopup').html(data);
					$('.palletnotepopup').fadeIn();


					// gross tare fields
					$('#gross_weight_val').val('');
					$('#pallet_tare').val('');
					$('#tare_per_carton').val('');
					$('#number_of_cartons').val('');
					$('#net_weight').val('');
					$('#tearWeightDiv').hide();
					$('#grossWeightDiv').hide();

					
					$('.quantityWeightContainer').show();
				}
			  });
		 
		}
		
		
		// $('#single_weight_val').val("");
		// $('#individualweights').val("");
		// $('#quantityWeight').val("");
		// $('#unit').val(""); #####
		// $('#best_by_range_from').val("");
		// $('#best_by_range_to').val("");
		// $('#best_by').val("");
		// $('#tear_weight_val').val("");
		// $('#gross_weight_val').val("");
		

	}
	
	function addPallet(){
		
		var best_by = $('#best_by').val();
		var best_by_range_from = $('#best_by_range_from').val();
		var best_by_range_to = $('#best_by_range_to').val();
		var quantityWeight = $('#quantityWeight').val();
		var ubbb = $('#ubbb').val();
 		
		
		var brand_id = $('#brand_id').val();
		if(brand_id == '--'){
			brand_id = '';
		}
		
		var product_temp = $('#product_temp').val();
		if(product_temp == ''){
			product_temp = '';
		}
		
		var species_id = $('#species_id').val();
		if(species_id == '--'){
			species_id = '';
		}
		
		var cut_id = $('#cut_id').val();
		if(cut_id == '--'){
			cut_id = '';
		}
		
		var unit = $('#unit').val();
		if(unit == '--'){
			unit = '';
		}
		
		
		var good = 1;
		var msg = "";
		
		if(best_by == ''){
			msg = "The highlighted fields cannot be blank!";
			$('#best_by').css('border','2px solid red');
			good = 0;
		}else{
			$('#best_by').css('border','1px solid grey');
		}
		
		if(ubbb != 2){
		
			if(best_by_range_from == ''){
				msg = "The highlighted fields cannot be blank!";
				$('#best_by_range_from').css('border','2px solid red');
				good = 0;
			}else{
				$('#best_by_range_from').css('border','1px solid grey');
			}
			
			if(best_by_range_to == ''){
				msg = "The highlighted fields cannot be blank!";
				$('#best_by_range_to').css('border','2px solid red');
				good = 0;
			}else{
				$('#best_by_range_to').css('border','1px solid grey');
			}
			
		}
		
		if(brand_id == ''){
			msg = "The highlighted fields cannot be blank!";
			$('#brand_id').css('border','2px solid red');
			good = 0;
		}else{
			$('#brand_id').css('border','1px solid grey');
		}
		
		
		if(product_temp == ''){
			msg = "The highlighted fields cannot be blank!";
			$('#product_temp').css('border','2px solid red');
			good = 0;
		}else{
			$('#product_temp').css('border','1px solid grey');
		}
		
		if(species_id == ''){
			msg = "The highlighted fields cannot be blank!";
			$('#species_id').css('border','2px solid red');
			good = 0;
		}else{
			$('#species_id').css('border','1px solid grey');
		}
		
		if(cut_id == ''){
			msg = "The highlighted fields cannot be blank!";
			$('#cut_id').css('border','2px solid red');
			good = 0;
		}else{
			$('#cut_id').css('border','1px solid grey');
		}
		
		if(unit == ''){
			msg = "The highlighted fields cannot be blank!";
			$('#unit').css('border','2px solid red');
			good = 0;
		}else{
			$('#unit').css('border','1px solid grey');
		}
		
		
		if(quantityWeight == ''){
			msg = "The highlighted fields cannot be blank!";
			$('#quantityWeight').css('border','2px solid red');
			good = 0;
		}else{
			$('#quantityWeight').css('border','1px solid grey');
		}
		
		if($('#individualweights').val() == 'S'){
			if($('#single_weight_val').val() > 0){
				$('#single_weight_val').css('border','1px solid grey');
			}else{
				msg = "The highlighted fields cannot be blank!";
				$('#single_weight_val').css('border','2px solid red');
				good = 0;
			}
		}
		
		if($('#individualweights').val() == 'D'){
			if($('#gross_weight_val').val() > 0){
				$('#gross_weight_val').css('border','1px solid grey');
			}else{
				msg = "The highlighted fields cannot be blank!";
				$('#gross_weight_val').css('border','2px solid red');
				good = 0;
			}
			
			if($('#tear_weight_val').val() > 0){
				$('#tear_weight_val').css('border','1px solid grey');
			}else{
				msg = "The highlighted fields cannot be blank!";
				$('#tear_weight_val').css('border','2px solid red');
				good = 0;
			}
		}
		
		if($('#individualweights').val() == 'C'){
			$('.multiweight').each(function(){
				if($(this).val() == ''){
					good = 0;
					$(this).css('border','2px solid red');
					msg = "The highlighted fields cannot be blank!";
				}else{
					$(this).css('border','1px solid grey');
				}
			});
		}

		var nationality = $('#nationality_id').val();
		if(nationality == '--'){
			nationality = '';
		}
		if(nationality == ''){
			msg = "The highlighted fields cannot be blank!";
			$('#nationality').css('border','2px solid red');
			good = 0;
		}else{
			$('#nationality').css('border','1px solid grey');
		}
			
		if($('#storage_location').val() == undefined || $('#storage_location').val() == ''){
			msg = "The highlighted fields cannot be blank!";
			$('#storage_location').css('border','2px solid red');
			good = 0;
		}else{
			$('#storage_location').css('border','1px solid grey');
		}
		$('#msgNotice2').html(msg);

		if($('[name="temperature_id"]').val() == undefined || $('[name="temperature_id"]').val() == ''){
			msg = "The highlighted fields cannot be blank!";
			$('[name="temperature_id"]').css('border','2px solid red');
			good = 0;
		}else{
			$('[name="temperature_id"]').css('border','1px solid grey');
		}
		$('#msgNotice2').html(msg);
		
		if(good == 1){
			$.ajax({ // make an AJAX request
				type: "POST",
				url: "script_addPallet.php", // it's the URL of your component B
				data: $('#addPalletForm').serialize(), // serializes the form's elements
				error: function(xhr, error){
					console.log(xhr);
					console.log(error);
					
					$('#networkError').fadeIn();
				},
				success: function(data)
				{	
					console.log(data);
					$('.palletidpopup').html(data);
					// $('.palletnotepopup').fadeIn();
				}
			});
		}
		
		// console.log(msg);
		
	}
	
	// $( "#datepicker" ).datepicker();
	 
	// THIS IS FOR HANDLING WHEN A SPECIES IS CHANGED AND YOU WANT THE CUTS LIST TO UPDATE. 
	
	$('#networkError').click(function(){
		$(this).fadeOut();
	});
	
	$('#species_id').change(function(){
		var species = $(this).val();
		
		$.get( "ajax/getCuts.php?id=" + species, function( data ) {
			// console.log(data);
			$('#cut_id').html('<option></option>');
			$('#cut_id').html(data);
		});
		
		console.log('Species changed..' + species);
	});
	
	
	
	$('#ubbb').change(function(){
		var val = $(this).val();
 
		if(val == 2){
			$('#best_by_range_from_container').fadeOut();
			$('#best_by_range_to_container').fadeOut();
		}else{
			$('#best_by_range_from_container').fadeIn();
			$('#best_by_range_to_container').fadeIn();
		}
		
	});
	
	
	
	
	var boxCount = 0;
	
	function updateForm(){
		
		
		console.log('Updating form..');
	
		if($("#unit").val() == 'LB' || $("#unit").val() == 'KG'){
			console.log('Single Weight Selected....' + $("#unit").val());
			$('#SingleWeightDiv').fadeIn();
			$('.indiweights').fadeOut();
		}else if($("#unit").val() == 'PPC'){
			$('.indiweights').fadeOut();
			$('#MultiWeightDiv').fadeOut();
			
			$('.howManyUnitsDiv').text('HOW MANY CASES');
			$('.indiweights').fadeOut();
 			
			$('#grossWeightDiv').fadeOut();
			$('#tearWeightDiv').fadeOut();

			$('#individualweights').val('C');
			$('#SingleWeightDiv').fadeOut();

		}else if($('#unit').val() == 'P'){
			$("#individualweights option[value=C]").hide();
			$("#individualweights option[value=S]").hide();
			// $('#individualweights').val('D');
			// alert('doiung it?');
		}else if($('#unit').val() == 'DS'){
			$('#directtostore').fadeIn();
			$('.indiweights').fadeOut();
			$('#individualweights').prop('selectedIndex',1);
			$('#quantityWeight').val(1);
			$('.quantityWeightContainer').fadeOut();
			$('#SingleWeightDiv').fadeOut();
		}else if($('#individualweights').val() == 'D'){
			$('#grossWeightDiv').fadeIn();
			$('#tearWeightDiv').fadeIn();
			$('#akgDiv').fadeOut();
		}else if($('#unit').val() == 'PP'){ // if packet
			
			$('.howManyUnitsDiv').text('HOW MANY PACKETS PER CASE');
			$('.individualweights').val('S');
			$('#individualweights > option').eq(2).attr('selected','selected');
			
			if($('#individualweights').val() == 'S'){
				$('.standardcatchtext').text('PACKETS PER CASE');
			}else{
				$('.standardcatchtext').text('STANDARD OR CATCH WEIGHTS?');
			}				
			
			var quantityVal = $('.quantityWeight').val();
			
			$('#single_weight_val').val(quantityVal);
			$('.hideIfPacket').hide();
			
		}else{
			$("#individualweights option[value=C]").show();
			$("#individualweights option[value=S]").show();

			$('#directtostore').fadeOut();
			$('.quantityWeightContainer').fadeIn();
			$('.indiweights').fadeIn();
			$('#SingleWeightDiv').fadeOut();

			$('.hideIfPacket').show();
			$('.howManyUnitsDiv').text('HOW MANY UNITS');
			$('.standardcatchtext').text('STANDARD OR CATCH WEIGHTS?');
 		}
		
		
		if($('#individualweights').val() == 'C'){
			if($('#unit').val() != 'PPC'){
				
			console.log('Individual Weights...' + $('#individualweights').val());
			
			var amount = $('.quantityWeight').val();
			
			generateWeightBoxes(amount);
			hideSingleWeight();
			
			$('#grossWeightDiv').fadeOut();
			$('#tearWeightDiv').fadeOut();
			$('#akgDiv').fadeOut();
			}
			
		}else if($('#individualweights').val() == 'AKG'){
			 
			// var amount = $('.quantityWeight').val();
			
			// generateWeightBoxes(amount);
			hideSingleWeight();
			
			$('#grossWeightDiv').fadeOut();
			$('#tearWeightDiv').fadeOut();
			$('#akgDiv').fadeIn();
			
			$('.multiweight').val(0);
			
		}else if($('#individualweights').val() == 'D'){
			$('#akgDiv').fadeOut();

			$('#grossWeightDiv').fadeIn();
			$('#tearWeightDiv').fadeIn();
		}else{
			showSingleWeight();
			removeWeightBoxes();
			
			$('#grossWeightDiv').fadeOut();
			$('#akgDiv').fadeOut();
			$('#tearWeightDiv').fadeOut();
		}
		
	  
		
	}
	 
	function showSingleWeight(){
		$('#SingleWeightDiv').fadeIn();
	}
	
	function hideSingleWeight(){
		$('#SingleWeightDiv').fadeOut();
	}	

	// window.setInterval(function(){
		// updateForm();
	// }, 1000);
 
	$('#unit').change(function(){ 
		updateForm();
		
		// if($('#unit').val() == 'P'){
			
			// $('#quantityWeight').val('1');
			
		// }
		
		
		if($('#unit').val() == 'P'){
			
			$('#quantityWeight').val('1');
			$('.quantityWeightContainer').css('display','none');
			$("#individualweights option[value=C]").hide();
			$("#individualweights option[value=S]").hide();
			$('#individualweights').prop('selectedIndex', 2);
			hideSingleWeight();
			$('#grossWeightDiv').fadeIn();
			$('#tearWeightDiv').fadeIn();
		}else{
			$('.quantityWeightContainer').css('display','block');
		}
		
	});
	
	$('#individualweights').change(function(){ updateForm(); });
	$('#quantityWeight').change(function(){ updateForm(); });
	
	
	function removeWeightBoxes(){
		
		$('#MultiWeightDiv').html('');
	}
	
	function multiWeight(){
	
	}
	
	function countWeights(){
		var totalWeights = 0;
		for(var x = 1; x < 100; x++){
			var tig = $('.weights' + x).val();
			
			if(tig > 0){
				totalWeights += parseFloat(tig); 
			}
		}
		
		var totalWeightRounded = round(totalWeights, 5)
		$('#totalCatchWeight').html(totalWeightRounded + 'kg');
	}
	
	function round(value, precision) {
		var multiplier = Math.pow(10, precision || 0);
		return Math.round(value * multiplier) / multiplier;
	}
	
	function generateWeightBoxes(i){
		
		$('#MultiWeightDiv').fadeIn();
		$('#totalCatchWeightContainer').fadeIn();
		$('#MultiWeightDiv').html('');
		
		i++;
		
		boxCount = i;
		
		for(var x = 1; x < i; x++){
			$('#MultiWeightDiv').append('<div><br/><input type="number" class="multiweight weights' + x + '" name="weights' + x + '"></div>');
		}
	}
	
	function newintake(){
		var formName = '#newintake';
		var xhttp = new XMLHttpRequest();
		xhttp.open("POST", $(formName).attr('action'), true);
		xhttp.setRequestHeader('X-CSRF-TOKEN', "<?php echo csrf_token();?>");
		xhttp.send($(formName).serialize());
	}
		
		
	
	
	$('#species_id2').change(function(){
		var species = $(this).val();
		
		$.get( "ajax/getCuts.php?id=" + species, function( data ) {
			// console.log(data);
			$('#cut_id2').html('<option></option>');
			$('#cut_id2').html(data);
		});
		
		console.log('Species changed..' + species);
	});
	
	$('.closeAddPallet').click(function(){
		$('#box').fadeOut();
	});
	
	function bestByNA(){
		$('#best_by').val('N/A');
	}
	
	function ubbbyNA(){
		$('#ubbb').val('2');
		$('#best_by_range_from_container').fadeOut();
		$('#best_by_range_to_container').fadeOut();
	}
	
	
</script>