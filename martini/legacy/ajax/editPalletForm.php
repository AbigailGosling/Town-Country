<?php
	require(__DIR__.'/../functions.php');
	
	$intake_id = request()->input('intake_id');
	$pallet_id = request()->input('pallet_id');
	$x = "SELECT * FROM intake WHERE id=?";
	$y = prepareExecuteQuery($x,'i',[$intake_id]);
	$intake = mysqli_fetch_array($y);
?>

<a href="javascript:;" id="closeAddPallet2" class="close closeAddPallet"></a>
<h1 class="int">Add a product to<br/> pallet #0000<?php echo $pallet_id; ?></h1>

<form method="POST" id="addProductToPallet" action="scripts/addProductToPallet.php">
	<div class="float">
		<div id="msgNotice3" style="color:white;"></div>
		<input type="text" style="display:none;" value="<?php echo $intake_id; ?>" name="intake_id">
		<input type="text" style="display:none;" value="<?php echo $pallet_id; ?>" name="pallet_id">
		
		<div style="display:none;">
			<label>status</label>
			<select name="statuses_id">
				<option value="0">Available</option>
				<option value="1">Sold</option>
			</select>
		</div>
			
		<label>Pack Date</label>
		<input name="best_by" id="best_by3" type="text" onfocus="blur()">
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
		
		<label>Fresh/Frozen</label>
		<select name="temperature_id">
			<?php
				$x = "SELECT * FROM temperature";
				$y = prepareExecuteQuery($x);
				while($row = mysqli_fetch_array($y)){
				?><option value="<?php echo $row['id']; ?>" <?php if($row['id'] == $pallet['temperature_id']){ echo 'selected'; } ?>><?php echo $row['temperature']; ?></option><?php
				}
			?>
		</select>
		<label>comments</label>
		<textarea name="comments"></textarea>
		
		
		
		
	</div>
	<div class="float">
		<br/>
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
		<select name="species_id" id="species_id2">
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
			<option value="P" style="display:none;">Gross-Tare &nbsp;&nbsp;&nbsp;&nbsp; Dolav/Cases</option>
		</select>
		
		<div class="quantityWeightContainer">
		<label>HOW MANY UNITS</label>
		<input type="number" class="quantityWeight" onChange="updateForm()" id="quantityWeight" name="quantity">
		</div>

		<div class="hideIfPacket">
			<div class="indiweights" style="display:none;">
			<label>Standard or catch weights?</label>
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
				<input type="number" name="single_weight_val" class="decimalfix" id="single_weight_val">
			</div>
		
			<div id="grossWeightDiv" style="display:none;">
				<label>Gross Weight</label>
				<input type="number" name="gross_weight_val" class="decimalfix" id="gross_weight_val">
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
		</div>

	</div>
	<div id="MultiWeightDiv" style="display:none;"> 
	
	</div>
	<br/>
	<h1 style="padding-left:19px;display:none;color:#FFF;font-size:18px;float:left;" id="totalCatchWeightContainer">Total Catch Weight: <span id="totalCatchWeight"></span></h1>
	<div class="clearfix"></div>
	<input value="Add product" onclick="addPallet();" type="button">
</form>
<script>
$.ajaxSetup({
		headers: { 'X-CSRF-TOKEN': "<?php echo csrf_token();?>" }
	});
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

		$('.decimalfix').keypress(function(key) {
			var val = $(this).val();
			if(key.charCode == 46) {
			  if(val.indexOf('.') > -1) {
				return false;
			  }
			}
			return true;
		}); 
		
		$( "#best_by3" ).datepicker({
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
		
		// $('#closeAddPallet').click(function(){
			// $('#editBox').fadeOut();
		// });
		
		
		$('#cut_search').keyup(function(){
			var val = $('#cut_search').val();
			console.log(val);
			if(val != ''){
				$('#cut_search_results').fadeIn();
			}else{
				// $('#cut_search_results').fadeOut();
			}
			
			var species = $('#species_id2').val();
			
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
		
		updateForm();
	});
	
	window.setInterval(function(){
		countWeights();
	}, 1000);
	
	
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
		
		var species_id = $('#species_id2').val();
		if(species_id == '--'){
			species_id = '';
		}
		
		var cut_id = $('#cut_id2').val();
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
			var from = best_by_range_from.split("/");
			from = new Date(from[2],from[1],from[0]);
			var to = best_by_range_to.split("/");
			to = new Date(to[2],to[1],to[0]);
			if (from > to)
			{
				$('#best_by_range_from').css('border','2px solid red');
				$('#best_by_range_to').css('border','2px solid red');
				msg = "From date cannot be after To date!";
				good = 0;
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
			$('#species_id2').css('border','2px solid red');
			good = 0;
		}else{
			$('#species_id2').css('border','1px solid grey');
		}
		
		if(cut_id == ''){
			msg = "The highlighted fields cannot be blank!";
			$('#cut_id2').css('border','2px solid red');
			good = 0;
		}else{
			$('#cut_id2').css('border','1px solid grey');
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
			
			$('#MultiWeightDiv input[type="number"]').each(function( index ) {
			  
			  if($(this).val() > 0){
					$(this).css('border','1px solid grey');
				}else{
					msg = "The highlighted fields cannot be blank!";
					$(this).css('border','2px solid red');
					good = 0;
				}
				
				
			});

		}
		
		$('#msgNotice3').html(msg);
		
		if(good == 1){			
			var formName = '#addProductToPallet';
			$.ajax({ // make an AJAX request
				type: "POST",
				headers: { 'X-CSRF-TOKEN': "<?php echo csrf_token();?>" },
				url:  $(formName).attr('action'), // it's the URL of your component B
				data: $(formName).serialize(), // serializes the form's elements
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
			
			var xhttp = new XMLHttpRequest();
			xhttp.open("POST", $(formName).attr('action'), true);
			xhttp.setRequestHeader('X-CSRF-TOKEN', "<?php echo csrf_token();?>");
			xhttp.send($(formName).serialize());
		}
		
		// console.log(msg);
		
	}
	
	
	// $( "#datepicker" ).datepicker();
	 
	// THIS IS FOR HANDLING WHEN A SPECIES IS CHANGED AND YOU WANT THE CUTS LIST TO UPDATE. 
	
	$('#species_id').change(function(){
		var species = $(this).val();
		
		$.get( "ajax/getCuts.php?id=" + species, function( data ) {
			// console.log(data);
			$('#cut_id').html('<option></option>');
			$('#cut_id').html(data);
		});
		
		console.log('Species changed..' + species);
	});

	function countWeights(){
		var totalWeights = 0;
		for(var x = 1; x < 100; x++){
			var tig = $('.weights' + x).val();
			
			if(tig > 0){
				totalWeights += parseFloat(tig); 
			}
		}
		
		console.log(totalWeights);
		var totalWeightRounded = round(totalWeights, 5)
		$('#totalCatchWeight').html(totalWeightRounded + 'kg');
	}
	
	function round(value, precision) {
		var multiplier = Math.pow(10, precision || 0);
		return Math.round(value * multiplier) / multiplier;
	}
	
	
	var boxCount = 0;
	
	function updateForm(){
		// HERE WE WILL HANDLE ALL THE FORM LOGIC
		console.log('Updating form..');
	
		if($("#unit").val() == 'LB' || $("#unit").val() == 'KG'){
			console.log('Single Weight Selected....' + $("#unit").val());
			$('#SingleWeightDiv').show();
			$('.indiweights').hide();
		}else{
			$('.indiweights').show();
			$('#SingleWeightDiv').hide();
		}
		
		if($('#unit').val() == 'P'){
			$("#individualweights option[value=C]").hide();
			$("#individualweights option[value=S]").hide();
		}else{
			$("#individualweights option[value=C]").show();
			$("#individualweights option[value=S]").show();
		}
		
		
		if($('#individualweights').val() == 'C'){
			if($('#unit').val() != 'PPC'){
				console.log('Individual Weights...' + $('#individualweights').val());
				
				var amount = $('.quantityWeight').val();
				
				generateWeightBoxes(amount);
				hideSingleWeight();
				
				$('#grossWeightDiv').hide();
				$('#tearWeightDiv').hide();
				$('#akgDiv').fadeOut();
			}
			
		}else if($('#individualweights').val() == 'AKG'){
			hideSingleWeight();
			 
			$('#grossWeightDiv').fadeOut();
			$('#tearWeightDiv').fadeOut();
			$('#akgDiv').fadeIn();
			 
			$('.multiweight').val(0);
			 
		 }else if($('#individualweights').val() == 'D'){
			$('#grossWeightDiv').show();
			$('#tearWeightDiv').show();
			$('#akgDiv').fadeOut();
		}else{
			showSingleWeight();
			removeWeightBoxes();
			
			$('#grossWeightDiv').hide();
			$('#akgDiv').fadeOut();
			$('#tearWeightDiv').hide();
			 
		}
		
		
		if($('#unit').val() == 'PP'){ // if packet
			
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
			
		}else if($("#unit").val() == 'PPC'){
  
			
			$('.howManyUnitsDiv').text('HOW MANY CASES');
			$('.indiweights').fadeOut();
			$('#SingleWeightDiv').fadeOut();
			
			$('#grossWeightDiv').fadeOut();
			$('#tearWeightDiv').fadeOut();
			
		}else{
			$('.hideIfPacket').show();
			$('.howManyUnitsDiv').text('HOW MANY UNITS');
			$('.standardcatchtext').text('STANDARD OR CATCH WEIGHTS?');
		}
		
	}
	 
	function showSingleWeight(){
		$('#SingleWeightDiv').show();
	}
	
	function hideSingleWeight(){
		$('#SingleWeightDiv').hide();
	}
	
	$('#unit').change(function(){ 
		updateForm();
		
		if($('#unit').val() == 'P'){
			
			$('#quantityWeight').val('1');
			$('.quantityWeightContainer').css('display','none');
			$('#individualweights').prop('selectedIndex', 3);
			hideSingleWeight();
			$('#grossWeightDiv').show();
			$('#tearWeightDiv').show();
		}else{
			$('.quantityWeightContainer').css('display','block');
		}
		
	});
	
	$('#individualweights').change(function(){ updateForm(); });
	$('#quantityWeight').change(function(){ updateForm(); });
	
	
	function removeWeightBoxes(){
		
		$('#MultiWeightDiv').html('');
	}
	
	
	function generateWeightBoxes(i){
		
		$('#MultiWeightDiv').show();
		$('#totalCatchWeightContainer').show();
		$('#MultiWeightDiv').html('');
		
		i++;
		
		for(var x = 1; x < i; x++){
			$('#MultiWeightDiv').append('<div><br/><input type="number" class="decimalfix weights' + x + '" name="weights' + x + '"></div>');
		}
	}
	
	function newintake(){
		var formName = '#newintake';
		var xhttp = new XMLHttpRequest();
		xhttp.open("POST", $(formName).attr('action'), true);
		xhttp.setRequestHeader('X-CSRF-TOKEN', "<?php echo csrf_token();?>");
		xhttp.send($(formName).serialize());
	}
		
		
	
	$('#closeAddPallet2').click(function(){
		$('#box').hide();
	});
	
	$('#species_id2').change(function(){
		var species = $(this).val();
		
		$.get( "ajax/getCuts.php?id=" + species, function( data ) {
			// console.log(data);
			$('#cut_id2').html('<option></option>');
			$('#cut_id2').html(data);
		});
		
		console.log('Species changed..' + species);
	});
	
	// $('.closeAddtoPallet').click(function(){
		// $('#box').fadeOut();
	// });
	
	
	function bestByNA(){
		$('#best_by3').val('N/A');
	}
	
	function ubbbyNA(){
		$('#ubbb').val('2');
		$('#best_by_range_from_container').hide();
		$('#best_by_range_to_container').hide();
	}
	
</script>