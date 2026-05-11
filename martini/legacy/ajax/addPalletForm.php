<?php

use App\Models\Cut;
use App\Models\IntakeScanningFile;
use App\Models\Nationality;
use App\Models\Product;
use App\Models\Site;
use App\Models\Species;

	require(__DIR__.'/../functions.php');

	$intake_id = request()->input('intake_id');
    $product_id = request()->input('product_id', null);
    $cases = request()->input('cases', null);
	$intakeScanningFileId = request()->input('intake_scanning_file_id', null);
    if ($product_id != null && $cases != null) {
        $productToDupe = Product::find($product_id);
    }
	$x = "SELECT * FROM intake WHERE id=?";
	$y = prepareExecuteQuery($x,'i',[$intake_id]);
	$intake = mysqli_fetch_array($y);

	$temperatureRows = [];
	$temperatureQuery = prepareExecuteQuery("SELECT * FROM temperature");
	while ($row = mysqli_fetch_array($temperatureQuery)) {
		$temperatureRows[] = $row;
	}

	$scanDefaults = [];
	if (is_numeric($intakeScanningFileId)) {
		$scanRecord = IntakeScanningFile::with(['sourceFileRecord', 'responseFileRecord'])
			->find((int) $intakeScanningFileId);

		if ($scanRecord !== null) {
			if ($scanRecord->file_role === IntakeScanningFile::ROLE_JSON) {
				$scanPayload = is_array($scanRecord->json_payload) ? $scanRecord->json_payload : [];
			} else {
				$responseRecord = $scanRecord->responseFileRecord;
				$scanPayload = is_array($responseRecord?->json_payload) ? $responseRecord->json_payload : [];
			}

			$readPayloadValue = static function (array $payload, string $key): ?string {
				$value = trim((string) ($payload[$key] ?? ''));

				return $value !== '' && $value !== '?' ? $value : null;
			};

			$extractNumericValue = static function (?string $value): ?string {
				if ($value === null) {
					return null;
				}

				if (preg_match('/-?[0-9]+(?:\.[0-9]+)?/', $value, $match) === 1) {
					return $match[0];
				}

				return null;
			};

			$resolveTemperatureId = static function (array $rows, ?string $freshFrozen, ?string $storageTemperature): ?int {
				$candidates = array_values(array_filter([$freshFrozen, $storageTemperature]));
				if ($candidates === []) {
					return null;
				}

				$aliases = [
					'fresh' => ['fresh', 'chilled'],
					'chilled' => ['chilled', 'fresh'],
					'frozen' => ['frozen'],
				];

				foreach ($candidates as $candidate) {
					$normalizedCandidate = strtolower(preg_replace('/[^a-z0-9]+/', '', $candidate) ?? '');
					$terms = $aliases[$normalizedCandidate] ?? [$normalizedCandidate];

					foreach ($rows as $row) {
						$rowLabel = strtolower(preg_replace('/[^a-z0-9]+/', '', (string) ($row['temperature'] ?? '')) ?? '');
						foreach ($terms as $term) {
							if ($term !== '' && ($rowLabel === $term || str_contains($rowLabel, $term) || str_contains($term, $rowLabel))) {
								return (int) $row['id'];
							}
						}
					}
				}

				return null;
			};

			$killDate = $readPayloadValue($scanPayload, 'killDate');
			$packDate = $readPayloadValue($scanPayload, 'packDate');
			$bestBeforeDate = $readPayloadValue($scanPayload, 'bestBeforeDate');
			$countryOfOrigin = $readPayloadValue($scanPayload, 'countryOfOrigin');
			$speciesName = $readPayloadValue($scanPayload, 'species');
			$cutName = $readPayloadValue($scanPayload, 'cuts');
			$netWeight = $extractNumericValue($readPayloadValue($scanPayload, 'netWeight'));
			$storageTemperature = $readPayloadValue($scanPayload, 'storageTemperature');
			$freshFrozen = $readPayloadValue($scanPayload, 'freshFrozen');

			$scannedSpecies = null;
			if ($speciesName !== null) {
				$scannedSpecies = Species::query()->where('name', $speciesName)->first();
				if ($scannedSpecies === null) {
					$scannedSpecies = Species::query()->where('name', 'like', '%' . $speciesName . '%')->first();
				}
			}

			$scannedCut = null;
			if ($cutName !== null) {
				$scannedCut = Cut::query()->where('name', $cutName)->first();
				if ($scannedCut === null) {
					$scannedCut = Cut::query()->where('name', 'like', '%' . $cutName . '%')->first();
				}
			}

			$scannedNationality = null;
			if ($countryOfOrigin !== null) {
				$scannedNationality = Nationality::query()->where('name', $countryOfOrigin)->first();
				if ($scannedNationality === null) {
					$scannedNationality = Nationality::query()->where('name', 'like', '%' . $countryOfOrigin . '%')->first();
				}
			}

			if ($killDate !== null) {
				$scanDefaults['kill_date'] = $killDate;
			}
			if ($packDate !== null) {
				$scanDefaults['best_by'] = $packDate;
			}
			if ($bestBeforeDate !== null) {
				$scanDefaults['ubbb'] = '1';
				$scanDefaults['best_by_range_from'] = $bestBeforeDate;
				$scanDefaults['best_by_range_to'] = $bestBeforeDate;
			}
			if ($scannedNationality !== null) {
				$scanDefaults['nationality_id'] = (int) $scannedNationality->id;
			}
			if ($scannedSpecies !== null) {
				$scanDefaults['species_id'] = (int) $scannedSpecies->id;
			}
			if ($scannedCut !== null) {
				$scanDefaults['cut_id'] = (int) $scannedCut->id;
				$scanDefaults['cut_search'] = $scannedCut->name;
			} elseif ($cutName !== null) {
				$scanDefaults['cut_search'] = $cutName;
			}

			$temperatureId = $resolveTemperatureId($temperatureRows, $freshFrozen, $storageTemperature);
			if ($temperatureId !== null) {
				$scanDefaults['temperature_id'] = $temperatureId;
			}

			$productTemp = $extractNumericValue($storageTemperature);
			if ($productTemp !== null) {
				$scanDefaults['product_temp'] = $productTemp;
			}

			if ($netWeight !== null) {
				$scanDefaults['individualweights'] = 'AKG';
				$scanDefaults['akg'] = $netWeight;
				$scanDefaults['quantity'] = '1';
			}
		}
	}

	$killDateValue = $productToDupe->kill_date ?? ($scanDefaults['kill_date'] ?? '');
	$packDateValue = $productToDupe->best_by ?? ($scanDefaults['best_by'] ?? '');
	$selectedUbbb = isset($productToDupe) ? (string) $productToDupe->ubbb : ($scanDefaults['ubbb'] ?? '0');
	$bestByRangeFromValue = $productToDupe->range_from ?? ($scanDefaults['best_by_range_from'] ?? '');
	$bestByRangeToValue = $productToDupe->range_to ?? ($scanDefaults['best_by_range_to'] ?? '');
	$bestByRangeExtensionValue = $productToDupe->range_extension ?? '';
	$selectedTemperatureId = $pallet['temperature_id'] ?? ($scanDefaults['temperature_id'] ?? null);
	$productTempValue = $scanDefaults['product_temp'] ?? '';
	$selectedStorageLocation = $productToDupe->storage_location ?? null;
	$commentsValue = $productToDupe->comments ?? '';
	$selectedNationalityId = $pallet['nationality_id'] ?? ($productToDupe->nationality_id ?? ($scanDefaults['nationality_id'] ?? null));
	$brandSearchValue = isset($productToDupe) && $productToDupe->brand_id != null ? $productToDupe->brand->name : '';
	$brandIdValue = isset($productToDupe) && $productToDupe->brand_id != null ? $productToDupe->brand_id : '';
	$selectedSpeciesId = $pallet['species_id'] ?? (isset($productToDupe) ? $productToDupe->cut->species_id : ($scanDefaults['species_id'] ?? null));
	$cutSearchValue = isset($productToDupe) ? $productToDupe->cut->name : ($scanDefaults['cut_search'] ?? '');
	$cutIdValue = isset($productToDupe) ? $productToDupe->cut_id : ($scanDefaults['cut_id'] ?? '');
	$selectedUnit = isset($productToDupe) ? $productToDupe->unit : 'C';
	$quantityValue = $scanDefaults['quantity'] ?? '';
	$selectedIndividualweights = $scanDefaults['individualweights'] ?? 'C';
	$akgValue = $scanDefaults['akg'] ?? '';
?>
<a href="javascript:;" class="close closeAddPallet"></a>
<h1 class="int">Add a Pallet</h1>
<form method="POST" id="addPalletForm" action="script_addPallet.php" autocomplete="off">
<input autocomplete="off" name="hidden" type="text" style="display:none;">
<?php if(isset($productToDupe)){ ?>
    <input type="hidden" name="original_product_id" value="<?php echo $productToDupe->id; ?>">
<?php } ?>
<?php if (is_numeric($intakeScanningFileId)) { ?>
	<input type="hidden" name="intake_scanning_file_id" value="<?php echo (int) $intakeScanningFileId; ?>">
<?php } ?>
    <div id="msgNotice2" style="color:white;padding: 0 0 0 20px;"></div>
	<div class="float">

		<input type="text" style="display:none;" value="<?php echo $intake_id; ?>" name="intake_id">
		<div style="display:none;">
			<label>status</label>
			<select name="statuses_id">
				<option value="0">Available</option>
				<option value="1">Sold</option>
			</select>
		</div>

        <label>Kill Date</label>
		<input name="kill_date" id="kill_date" type="text" onfocus="blur()" value="<?php echo htmlspecialchars((string) $killDateValue); ?>">
		<div onclick="killDateNA()" id="bestbyBtn">SET N/A</div>
		<div class="clearfix"></div>

		<label>Pack Date</label>
		<input name="best_by" id="best_by" type="text" onfocus="blur()" value="<?php echo htmlspecialchars((string) $packDateValue); ?>">
		<div onclick="bestByNA()" id="bestbyBtn">SET N/A</div>
		<div class="clearfix"></div>

		<label>UB/ BB</label>
		<select name="ubbb" id="ubbb">
			<option value="0" <?php if($selectedUbbb === '0'){ echo 'selected'; } ?>>UB</option>
			<option value="1" <?php if($selectedUbbb === '1'){ echo 'selected'; } ?>>BB</option>
			<option value="2" hidden <?php if($selectedUbbb === '2'){ echo 'selected'; } ?>>N/A</option>
			<option value="3" <?php if($selectedUbbb === '3'){ echo 'selected'; } ?>>Process By</option>
			<option value="4" <?php if($selectedUbbb === '4'){ echo 'selected'; } ?>>Expiry</option>
			<option value="5" <?php if($selectedUbbb === '5'){ echo 'selected'; } ?>>Open By</option>
		</select>

		<div onclick="ubbbyNA()" id="ubbbBtn">SET N/A</div>
		<div class="clearfix"></div>

		<div id="best_by_range_from_container">
		<label>From</label>
		<input name="best_by_range_from" id="best_by_range_from" type="text" onfocus="blur()" value="<?php echo htmlspecialchars((string) $bestByRangeFromValue); ?>">
		</div>

		<div id="best_by_range_to_container">
		<label>To</label>
		<input name="best_by_range_to" id="best_by_range_to" type="text" onfocus="blur()" value="<?php echo htmlspecialchars((string) $bestByRangeToValue); ?>">
		</div>
		<div id="best_by_range_extension_container">
			<label>Extension</label>
			<input name="best_by_range_extension" id="best_by_range_extension" type="text" onfocus="blur()" value="<?php echo htmlspecialchars((string) $bestByRangeExtensionValue); ?>"><div onclick="clearEx()" id="bestbyBtn">Clear</div>
		</div>
		<label>Chilled/Frozen</label>
		<select name="temperature_id">
			<option selected="true" disabled></option>
			<?php
				foreach ($temperatureRows as $row) {
				?><option value="<?php echo $row['id']; ?>" <?php if((int) $row['id'] === (int) $selectedTemperatureId){ echo 'selected'; } ?>><?php echo $row['temperature']; ?></option><?php
				}
			?>
		</select>
		<div>
		<label>Product Temp (°C)</label>
		<input name="product_temp" id="product_temp" type="text" required value="<?php echo htmlspecialchars((string) $productTempValue); ?>">
		</div>
		<label>Location</label>
		<select name="storage_location" id="storage_location">
				<option selected="true" disabled></option>
				<?php echo Site::generateOldHTMLList($selectedStorageLocation);?>
		</select>

		<label>comments</label>
		<textarea name="comments"><?php echo htmlspecialchars((string) $commentsValue); ?></textarea>



	</div>
	<div class="float">
		<label>Nationality</label>
		<select name="nationality_id" id="nationality_id">
		<option>--</option>
		<?php
			$x = "SELECT * FROM nationality ORDER BY `name` ASC";
			$y = prepareExecuteQuery($x);
			while($row = mysqli_fetch_array($y)){
			?><option value="<?php echo $row['id']; ?>" <?php if((int) $row['id'] === (int) $selectedNationalityId){ echo 'selected'; } ?>><?php echo $row['name']; ?></option><?php
			}
		?>
		</select>

		<label>Brand Search</label>
		<input name="brand_search" id="brand_search" type="text" value="<?php echo htmlspecialchars((string) $brandSearchValue); ?>">
		<div id="brand_search_results">
			asdf
		</div>
		<input name="brand_id" id="brand_id" type="text" style="display:none;" value="<?php echo htmlspecialchars((string) $brandIdValue); ?>">

		<label>species</label>
		<select name="species_id" id="species_id">
			<option value="--">--</option>
			<?php
				$x = "SELECT * FROM species ORDER BY `name` ASC";
				$y = prepareExecuteQuery($x);
				while($row = mysqli_fetch_array($y)){
				?><option value="<?php echo $row['id']; ?>" <?php if((int) $row['id'] === (int) $selectedSpeciesId){ echo 'selected'; } ?>><?php echo $row['name']; ?></option><?php
				}
			?>
		</select>




		<label>cuts</label>
		<input name="cut_search" id="cut_search" type="text" value="<?php echo htmlspecialchars((string) $cutSearchValue); ?>">
		<div id="cut_search_results">
			asdf
		</div>

		<input name="cut_id" id="cut_id" type="text" style="display:none;" value="<?php echo htmlspecialchars((string) $cutIdValue); ?>">
		<div style="display:none;">
			<select name="cut_ids" id="cut_id">
			<option value="--">--</option>
			</select>
		</div>

		<label>Units of measurement</label>
		<select name="unit" id="unit">
	 		<option value="C" <?php if($selectedUnit == 'C'){ echo 'selected'; } ?>>Case</option>
			<option value="PPC" <?php if($selectedUnit == 'PPC'){ echo 'selected'; } ?>>Purchase Per Case</option>
			<option value="P" <?php if($selectedUnit == 'P'){ echo 'selected'; } ?>>Gross-Tare &nbsp;&nbsp;&nbsp;&nbsp; Dolav/Cases</option>
			<option value="DS" <?php if($selectedUnit == 'DS'){ echo 'selected'; } ?>>Direct to store/customer</option>
 		</select>


		<div class="quantityWeightContainer">
		<label class="howManyUnitsDiv">HOW MANY UNITS</label>
		<input type="number" class="quantityWeight" onChange="updateForm()" id="quantityWeight" name="quantity" max="<?php echo $cases; ?>" value="<?php echo htmlspecialchars((string) $quantityValue); ?>">
		</div>

		<?php if($intake['returned'] == 1){ ?>
		<div>
			<label>Original Intake ID</label>
			<input type="number" name="original_intake_id" value="<?php echo $productToDupe->pallet->intake_id; ?>">
		</div>

		<div>
			<label>Original Pallet ID</label>
			<input type="number" name="original_pallet_id" value="<?php echo $productToDupe->pallet_id; ?>">
		</div>
		<?php } ?>

		<div class="hideIfPacket">
			<div class="indiweights">
			<label class="standardcatchtext">Standard or catch weights?</label>
			<select name="individualweights" id="individualweights">
	 			<option value="C" <?php if($selectedIndividualweights === 'C'){ echo 'selected'; } ?>>Catch Weights</option>
				<option value="S" <?php if($selectedIndividualweights === 'S'){ echo 'selected'; } ?>>Standard Weight</option>
				<option value="D" <?php if($selectedIndividualweights === 'D'){ echo 'selected'; } ?>>Dolav/Cases</option>
	 			<option value="AKG" <?php if($selectedIndividualweights === 'AKG'){ echo 'selected'; } ?>>Advised kg</option>
			</select>
			</div>

			<div id="akgDiv" style="display:none;">
				<label>Net Weight</label>
				<input type="number" name="akg" id="akg" value="<?php echo htmlspecialchars((string) $akgValue); ?>">
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

		$( "#best_by" ).datepicker({
			dateFormat: 'dd/mm/yy',
			changeYear: true,
			yearRange: "<?php echo $start; ?>:<?php echo $end; ?>"
		});

        $( "#kill_date" ).datepicker({
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

		updateForm();

	});

	window.setInterval(function(){
		countWeights();
	}, 1000);

	function addPalletDuplicate(){
		var good = validateForm();

		if(good == 1){


			 $.ajax({ // make an AJAX request
				type: "POST",
				headers: { 'X-CSRF-TOKEN': "<?php echo csrf_token();?>" },
				url: "script_addPallet.php?dupe=true", // it's the URL of your component B
				data: $('#addPalletForm').serialize(), // serializes the form's elements
				error: function(xhr, error){
					 console.log(xhr);
					 console.log(error);

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

					//cleard dates
					$('#best_by').val('');
					$('#best_by_range_from').val('');
					$('#best_by_range_to').val('');

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
	function validateForm() {
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
		return good;
	}
	function addPallet(){

		var good = validateForm();

		if(good == 1){
			$.ajax({ // make an AJAX request
				type: "POST",
				headers: { 'X-CSRF-TOKEN': "<?php echo csrf_token();?>" },
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
            $('#best_by_range_extension_container').fadeOut();
		}else{
			$('#best_by_range_from_container').fadeIn();
			$('#best_by_range_to_container').fadeIn();
            $('#best_by_range_extension_container').fadeIn();
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
    function killDateNA(){
		$('#kill_date').val('');
	}
	function bestByNA(){
		$('#best_by').val('N/A');
	}

	function ubbbyNA(){
		$('#ubbb').val('2');
		$('#best_by_range_from_container').fadeOut();
		$('#best_by_range_to_container').fadeOut();
        $('#best_by_range_extension_container').fadeOut();
	}

    function clearEx(){
		$('#best_by_range_extension').val("");
	}
</script>
