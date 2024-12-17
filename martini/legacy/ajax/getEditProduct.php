<?php

use App\Models\Location;
use App\Models\Site;

	require(__DIR__.'/../functions.php');

	$intake_id = request()->input('intake_id');
	$pallet_id = request()->input('pallet_id');
	$product_id = request()->input('product_id');
	$weight_id = request()->input('weight_id');

	$x1 = "SELECT * FROM `product` WHERE id = ?";
	$y1 = prepareExecuteQuery($x1,'i',[$product_id]);
	$productRow = mysqli_fetch_array($y1);


	$x2 = "SELECT * FROM `weights` WHERE id = ?";
	$y2 = prepareExecuteQuery($x2,'i',[$weight_id]);


	$weightRow = mysqli_fetch_array($y2);


	$x3 = "SELECT * FROM `pallet` WHERE id = ?";
	$y3 = prepareExecuteQuery($x3,'i',[$pallet_id]);

	$palletRow = mysqli_fetch_array($y3);



	$xtest = "SELECT * FROM `weights` WHERE product_id = ?";
	$ytest = prepareExecuteQuery($xtest,'i',[$product_id]);
	$weightCount = mysqli_num_rows($ytest);

	$x4 = "SELECT * FROM intake WHERE id=?";
	$y4 = prepareExecuteQuery($x4,'i',[$intake_id]);
	$intake = mysqli_fetch_array($y4);

?>
<a href="javascript:;" id="closeAddPalletEditForm" class="close closeAddPalletEditForm"></a>
<h1 class="int">Edit Pallet #<?php echo $pallet_id; ?></h1>
<h1 class="int">Edit Product #<?php echo $product_id; ?></h1>
<form method="POST" id="addPalletForm" action="script_updateProduct.php">
	<div class="float">
		<div id="msgNotice2" style="color:white;"></div>
		<input type="text" style="display:none;" value="<?php echo $intake_id; ?>" name="intake_id">
		<input type="text" style="display:none;" value="<?php echo $pallet_id; ?>" name="pallet_id">
		<input type="text" style="display:none;" value="<?php echo $product_id; ?>" name="product_id">
		<input type="text" style="display:none;" value="<?php echo $weight_id; ?>" name="weight_id">

		<div style="display:none;">
			<label>status</label>
			<select name="statuses_id">
				<option value="0" <?php if($weightRow['status_id'] == '0'){ echo 'selected'; } ?>>Available</option>
				<option value="1" <?php if($weightRow['status_id'] == '1'){ echo 'selected'; } ?>>Sold</option>
			</select>
		</div>

		<label>Pack Date</label>
		<input name="best_by" id="best_by2" type="text" value="<?php echo $productRow['best_by']; ?>" onfocus="blur()">
		<div onclick="bestByNA()" id="bestbyBtn">SET N/A</div>
		<div class="clearfix"></div>

		<label>UB/ BB</label>
		<select name="ubbb" id="ubbb">
			<option value="0" <?php if($productRow['ubbb'] == 0){ echo 'selected'; }?>>UB</option>
			<option value="1" <?php if($productRow['ubbb'] == 1){ echo 'selected'; }?>>BB</option>
			<option value="2" <?php if($productRow['ubbb'] == 2){ echo 'selected'; }else{ echo 'hidden'; }?>>N/A</option>
			<option value="3" <?php if($productRow['ubbb'] == 3){ echo 'selected'; }?>>Process By</option>
			<option value="4" <?php if($productRow['ubbb'] == 4){ echo 'selected'; }?>>Expiry</option>
			<option value="5" <?php if($productRow['ubbb'] == 5){ echo 'selected'; }?>>Open By</option>
		</select>

		<div onclick="ubbbyNA()" id="ubbbBtn">SET N/A</div>
		<div class="clearfix"></div>

		<div id="best_by_range_from_container">
			<label>From</label>
			<input name="best_by_range_from" id="best_by_range_from" value="<?php echo $productRow['range_from']; ?>" type="text" onfocus="blur()">
		</div>

		<div id="best_by_range_to_container">
			<label>To</label>
			<input name="best_by_range_to" id="best_by_range_to" value="<?php echo $productRow['range_to']; ?>" type="text" onfocus="blur()">
		</div>

		<div id="best_by_range_extension_container">
			<label>Extension</label>
			<input name="best_by_range_extension" id="best_by_range_extension" value="<?php echo $productRow['range_extension']; ?>" type="text" onfocus="blur()"><div onclick="clearEx()" id="clearEX">Clear</div>
		</div>

		<label>Fresh/Frozen</label>
		<select name="temperature_id">
			<option disabled></option>
			<?php
				$x = "SELECT * FROM temperature";
				$y = prepareExecuteQuery($x);
				while($row = mysqli_fetch_array($y)){
				?><option value="<?php echo $row['id']; ?>" <?php if($row['id'] == $productRow['cooling_id']){ echo 'selected'; } ?>><?php echo $row['temperature']; ?></option><?php
				}
			?>
		</select>
		<div>
		<label>Product Temp (°C)</label>
		<input name="product_temp" id="product_temp" type="text" required value="<?php echo $productRow['product_temp'];?>"></input>
		</div>
		<label>Location</label>
		<?php

			$selected = Location::find($palletRow['storage_location'])->name;

		?>
		<select name="storage_location" id ="storage_location">
			<option></option>
			<?php echo Site::generateOldHTMLList($selected);?>
		</select>

		<label>comments</label>
		<textarea name="comments" ><?php echo $productRow['comments']; ?></textarea>




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
			?><option value="<?php echo $row['id']; ?>" <?php if($row['id'] == $productRow['nationality_id']){ echo 'selected'; } ?>><?php echo $row['name']; ?></option><?php
			}
		?>
		</select>


		<?php
			$brandid = $productRow['brand_id'];
			$xxx = "SELECT * FROM `brands` where id=?";
			$yyy = prepareExecuteQuery($xxx,'i',[$brandid]);
			$brand = mysqli_fetch_array($yyy);
		?>
		<label>Brand Search</label>
		<input name="brand_search" id="brand_search" value="<?php echo $brand['name']; ?>" type="text">
		<div id="brand_search_results">
			asdf
		</div>
		<input name="brand_id" id="brand_id" value="<?php echo $productRow['brand_id']; ?>" type="text" style="display:none;">


		<label>species</label>
		<select name="species_id" id="species_id">
			<option value="--">--</option>
			<?php
				$species_id = getSpeciesFromCut($productRow['cut_id']);

				$x = "SELECT * FROM species ORDER BY `name` ASC";
				$y = prepareExecuteQuery($x);
				while($row = mysqli_fetch_array($y)){
				?><option value="<?php echo $row['id']; ?>" <?php if($row['id'] == $species_id){ echo 'selected'; } ?>><?php echo $row['name']; ?></option><?php
				}
			?>
		</select>

		<label>cuts</label>
		<input name="cut_search" id="cut_search" value="<?php echo getCut($productRow['cut_id']); ?>" type="text" autocomplete="off">
		<div id="cut_search_results">
			asdf
		</div>
		<input name="cut_id" id="cut_id" type="text" value="<?php echo $productRow['cut_id']; ?>" style="display:none;">

        <label>Health Mark</label>
		<select name="health_id" id="health_id">
			<option value="--">--</option>
			<?php
				$x = "SELECT * FROM `health_mark` WHERE `disabled` = 0 OR id = '".$productRow['health_id']."' ORDER BY `name` ASC";
				$y = prepareExecuteQuery($x);
				while($row = mysqli_fetch_array($y)){
				?><option value="<?php echo $row['id']; ?>" <?php if($row['id'] == $productRow['health_id']){ echo 'selected'; } ?>><?php echo $row['name']; ?></option><?php
				}
			?>
		</select>

		<label>Units of measurement</label>
		<select name="unit" id="unit">
			<option value="--">--</option>
			<option value="C" <?php if($productRow['unit'] == 'C'){ echo 'selected'; } ?>>Case</option>
			<option value="PPC" <?php if($productRow['unit'] == 'PPC'){ echo 'selected'; } ?>>Purchase Per Case</option>
			<option value="P" <?php if($productRow['unit'] == 'P'){ echo 'selected'; } ?>>Gross-Tare &nbsp;&nbsp;&nbsp;&nbsp; Dolav/Cases</option>
 			<option value="DS" <?php if($productRow['unit'] == 'DS'){ echo 'selected'; } ?>>Direct to store/customer</option>
		</select>

		<?php if($weightCount == 1){ ?>
		<div id="SingleWeightDiv">
			<label>Weight <?php if($productRow['akg'] != ''){ echo ' ['. $productRow['quantity'] . '  Cases Advised KG] ';  } ?> </label>
			<input type="number" name="single_weight_val" value="<?php echo $weightRow['weight_gross']; ?>" id="single_weight_val">
		</div>
		<?php } ?>
		<?php if($intake['returned'] == 1){ ?>
		<div>
			<label>Original Intake ID</label>
			<input type="number" name="original_intake_id" id="original_intake_id" value="<?php echo $productRow['original_intake_id']; ?>">
		</div>

		<div>
			<label>Original Pallet ID</label>
			<input type="number" name="original_pallet_id" id="original_pallet_id" value="<?php echo $productRow['original_pallet_id']; ?>">
		</div>
		<?php } ?>

	</div>

	<br/>

	<div id="MultiWeightDiv">
	<?php
		if($weightCount > 1){

			$i = 0;
			while($row = mysqli_fetch_array($ytest)){
			$i++;
			?>
			<div class="weightEditWhiteBox" id="<?php echo $row['id']; ?>" style="position:relative;<?php if($row['status_id'] == 1){ echo 'border:4px solid red;'; }else{ echo 'border:4px solid green;'; } ?>">
				<input type="number" name="weight<?php echo $row['id']; ?>" value="<?php echo $row['weight_gross']; ?>">
				<a href="javascript:;" onclick="deleteWeight('<?php echo $row['id']; ?>','<?php echo $row['weight_gross']; ?>','<?php echo $intake_id; ?>')">
					<i class="fa fa-trash" aria-hidden="true" style="font-size:18px;color:#000;position:absolute;top: 11px;right: -41px;"></i>
				</a>
			</div>
			<?php
			}

		}
	?>
	</div>

	<h1 style="padding-left:19px;display:none;color:#FFF;font-size:18px;float:left;" id="totalCatchWeightContainer">Total Catch Weight: <span id="totalCatchWeight"></span></h1>
	<div class="clearfix"></div>

	<div class="btnContainer">
		<input value="Update Product" onclick="updatePallet();" type="button">
 	</div>
</form>

<form method="POST" action="<?php echo $domain; ?>scripts/markWeightAsSold.php" id="markWeightForm">
	<input type="hidden" name="weightid" id="weightToMark">
	<input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
	<input type="text" name="intakeid" value="<?php echo $intake_id; ?>" style="display:none;">
</form>


<script type="text/javascript">
	$.ajaxSetup({
		headers: { 'X-CSRF-TOKEN': "<?php echo csrf_token();?>" }
	});
	$(document).ready(function(){


		updateForm();

		<?php
			$start = date('Y', strtotime('-5 year'));
			$end = date('Y', strtotime('+5 year'));
		?>

		$( "#best_by2" ).datepicker({
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
		$( "#best_by_range_extension" ).datepicker({
			dateFormat: 'dd/mm/yy',
			changeYear: true,
			yearRange: "<?php echo $start; ?>:<?php echo $end; ?>"
		});
		$('#cut_search').keyup(function(){
			var val = $('#cut_search').val();
			// $('#test2d').text(val);
			if(val != ''){
				$('#cut_search_results').fadeIn();
			}else{
				$('#cut_search_results').fadeOut();
			}

			var species = $('#species_id').val();

			var xhttp = new XMLHttpRequest();
			xhttp.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200) {
				  // document.getElementById("demo").innerHTML = this.responseText;
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

	function deleteWeight(weight_id, weight_val, intake_id){
		if (confirm('Are you sure you want to delete this weight? (' +  weight_val + 'kg)')) {
			window.location.href = 'scripts/deleteWeight.php?id=' + weight_id + '&intakeid=' + intake_id;
		}
	}

	function updatePallet(){

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

		var nationality = $('#nationality_id').val();
		if(nationality == '--'){
			nationality = '';
		}

		var good = 1;
		var msg = "";

		if(ubbb != 2){

			if(best_by_range_from == ''){
				msg = "The highlighted fields cannot be blank!9";
				$('#best_by_range_from').css('border','2px solid red');
				good = 0;
			}else{
				$('#best_by_range_from').css('border','1px solid grey');
			}

			if(best_by_range_to == ''){
				msg = "The highlighted fields cannot be blank!8";
				$('#best_by_range_to').css('border','2px solid red');
				good = 0;
			}else{
				$('#best_by_range_to').css('border','1px solid grey');
			}

		}
		var product_temp = species_id = $('#product_temp').val();
		if(product_temp == ''){
			msg = "The highlighted fields cannot be blank!7";
			$('#product_temp').css('border','2px solid red');
			good = 0;
		}else{
			$('#product_temp').css('border','1px solid grey');
		}
		if(brand_id == ''){
			msg = "The highlighted fields cannot be blank!7";
			$('#brand_id').css('border','2px solid red');
			good = 0;
		}else{
			$('#brand_id').css('border','1px solid grey');
		}

		if(species_id == ''){
			msg = "The highlighted fields cannot be blank!6";
			$('#species_id').css('border','2px solid red');
			good = 0;
		}else{
			$('#species_id').css('border','1px solid grey');
		}

		if(cut_id == ''){
			msg = "The highlighted fields cannot be blank!5";
			$('#cut_id').css('border','2px solid red');
			good = 0;
		}else{
			$('#cut_id').css('border','1px solid grey');
		}

		if(unit == ''){
			msg = "The highlighted fields cannot be blank!4";
			$('#unit').css('border','2px solid red');
			good = 0;
		}else{
			$('#unit').css('border','1px solid grey');
		}


		if(quantityWeight == ''){
			msg = "The highlighted fields cannot be blank!3";
			$('#quantityWeight').css('border','2px solid red');
			good = 0;
		}else{
			$('#quantityWeight').css('border','1px solid grey');
		}

		if(nationality == ''){
			msg = "The highlighted fields cannot be blank!2";
			$('#nationality').css('border','2px solid red');
			good = 0;
		}else{
			$('#nationality').css('border','1px solid grey');
		}
		if($('#storage_location').val() == ''){
			msg = "The highlighted fields cannot be blank!1";
			$('#storage_location').css('border','2px solid red');
			good = 0;
		}else{
			$('#storage_location').css('border','1px solid grey');
		}
		$('#msgNotice2').html(msg);

		if(good == 1){
			var formName = '#addPalletForm';
			$(formName).ajaxSubmit({headers:{'X-CSRF-TOKEN': "<?php echo csrf_token();?>"},success:function(){	location.reload();}});
		}

		// console.log(msg);

	}

	// $( "#datepicker" ).datepicker();

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

	var boxCount = 0;

	function updateForm(){
		// HERE WE WILL HANDLE ALL THE FORM LOGIC
		console.log('Updating form..');

		if($('#howManyWeights').val() > 1){

		}else{

		}


		// if($("#unit").val() == 'LB' || $("#unit").val() == 'KG'){
			// console.log('Single Weight Selected....' + $("#unit").val());
			// $('#SingleWeightDiv').fadeIn();
			// $('.indiweights').fadeOut();
		// }else{
			// $('.indiweights').fadeIn();
			// $('#SingleWeightDiv').fadeOut();
		// }



		if($('#individualweights').val() == 'C'){
			console.log('Individual Weights...' + $('#individualweights').val());

			var amount = $('.quantityWeight').val();

			generateWeightBoxes(amount);
			hideSingleWeight();

			$('#grossWeightDiv').fadeOut();
			$('#tearWeightDiv').fadeOut();

		}else if($('#individualweights').val() == 'D'){
			$('#grossWeightDiv').fadeIn();
			$('#tearWeightDiv').fadeIn();
		}else{
			showSingleWeight();
			removeWeightBoxes();

			$('#grossWeightDiv').fadeOut();
			$('#tearWeightDiv').fadeOut();

		}

	}

	function showSingleWeight(){
		$('#SingleWeightDiv').fadeIn();
	}

	function hideSingleWeight(){
		$('#SingleWeightDiv').fadeOut();
	}

	$('#unit').change(function(){
		updateForm();

		if($('#unit').val() == 'P'){

			$('#individualweights').prop('selectedIndex', 3);
			hideSingleWeight();
			$('#grossWeightDiv').fadeIn();
			$('#tearWeightDiv').fadeIn();

		}

	});
	$('#individualweights').change(function(){ updateForm(); });
	$('#quantityWeight').change(function(){ updateForm(); });


	function removeWeightBoxes(){

		// $('#MultiWeightDiv').html('');
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

		// $('#MultiWeightDiv').fadeIn();
		// $('#totalCatchWeightContainer').fadeIn();
		// $('#MultiWeightDiv').html('');

		i++;

		boxCount = i;

		for(var x = 1; x < i; x++){
			$('#MultiWeightDiv').append('<div><br/><input type="number" class="weights' + x + '" name="weights' + x + '"></div>');
		}
	}

	function newintake(){
		var formName = '#newintake';
		var xhttp = new XMLHttpRequest();
		xhttp.open("POST", $(formName).attr('action'), true);
		xhttp.setRequestHeader('X-CSRF-TOKEN', "<?php echo csrf_token();?>");
		xhttp.send($(formName).serialize());
	}


	$('#closeAddPalletEditForm').click(function(){
		$('#editBox').fadeOut();
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

	$('.closeAddPallet').click(function(){
		$('#box').fadeOut();
	});

	function bestByNA(){
		$('#best_by2').val('N/A');
	}


	function ubbbyNA(){
		$('#ubbb').val('2');
		$('#best_by_range_from_container').fadeOut();
		$('#best_by_range_to_container').fadeOut();
	}

    function clearEx(){
		$('#best_by_range_extension').val("");
	}
</script>
