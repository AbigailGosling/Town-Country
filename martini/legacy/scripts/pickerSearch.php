<?php
	echo $cut = request()->input('cut');
	
	exit();
?><table width="100%">
	<th align="left">Plt ID</th>
	<th align="left">Brand</th>
	<th align="left">Cut</th>
	<th align="left">No.</th>
	<th align="left">Weight</th>
	<th align="left"></th>
<?php
	require(__DIR__.'/../functions.php');
	
	$cut = request()->input('cut');
	$species_id = request()->input('species');
	$temperatureID = request('temperatureID');
	$palletID = request('palletID');
	
	$cutsX = "SELECT * FROM `cuts` WHERE `name` LIKE ? AND species_id= ?";
	$cutsY = prepareExecuteQuery($cutsX,'si',['%'.$cut.'%',$species_id]);
	
	while($cutsRow = mysqli_fetch_array($cutsY)){
		$cut_id = $cutsRow['id'];
		
		$productsX = "SELECT * FROM `product` WHERE cut_id = ? AND cooling_id = ? ORDER BY range_from ASC";
		$productsY = prepareExecuteQuery($productsX,'ss',[$cut_id,$temperatureID]);
		
		$productsCount = mysqli_num_rows($productsY);
		
		while($productsRow = mysqli_fetch_array($productsY)){
		?>
		<tr align="left" style="height:40px;">
			<td align="left"><?php echo $productsRow['pallet_id']; ?></td>
			<td align="left"><?php echo getBrand($productsRow['brand_id']); ?></td>
			<td align="left" style="display:none;"><?php echo getSpecies(getSpeciesFromCut($productsRow['cut_id'])); ?></td>
			<td align="left"><?php echo getCut($productsRow['cut_id']); ?></td>
			<td align="left">
				<?php
					$numOfWeights = countNumProductsForCutOnPallet($productsRow['pallet_id'],$productsRow['cut_id']);
				?>
				<select id="quantity-<?php echo $yKRow['pallet_id']; ?>">
					<?php for($i=1;$i<$numOfWeights+1;$i++){?>
						<option value="<?php echo $i; ?>" selected><?php echo $i; ?></option>
					<?php } ?>
				</select>
			</td>
			<td><?php echo $weightCount; ?>KG</td>
			<td><a href="javascript:;" onclick="addToSheet('<?php echo $productsRow['id']; ?>','<?php echo $productsRow['pallet_id']; ?>','<?php echo $productsRow['cut_id']; ?>');"><i class="fa fa-plus" style="font-size:24px;color:#000;"></i></a></td>
		</tr>
		<tr>
			<td colspan="4">
				<div class="weightsContainer weights<?php echo $pallet_id; ?><?php echo $species_id; ?><?php echo $value; ?>" style="display:none;">
					<div class="weightbox" weight="<?php echo (double) weightOfProduct($rowD['id']); ?>" product_id="<?php echo $rowD['id']; ?>" onclick="toggleWeight(this)">
					12000kg
					</div>
				</div>
			</td>
		</tr>
		<?php
		}
	}	
	?>
</table>
<script type="text/javascript">
	$.ajaxSetup({
		headers: { 'X-CSRF-TOKEN': "<?php echo csrf_token();?>" }
	});
	function getCookie(name) {
		var value = "; " + document.cookie;
		var parts = value.split("; " + name + "=");
		if (parts.length == 2) return parts.pop().split(";").shift();
	}
	
	function addToSheet(product_id, pallet_id, cut_id){
		
		console.log('product_id......:' + product_id);
		console.log('pallet_id......:' + pallet_id);
		console.log('cut_id......:' + cut_id);
		
		var q = $('#quantity-' + pallet_id).val();
		
		var COOKIE_NAME = "PRODUCT-"+pallet_id+"-"+cut_id;
		
		if(getCookie(COOKIE_NAME)){
			
			var alreadyHave = getCookie(COOKIE_NAME);
			var howManyMoreWeWant = q;
			var howManyLeft = q - alreadyHave;
			
			// var howManyLeft = 5;
			howManyLeft++;
			
			// var howManyMoreWeWant2 = howManyMoreWeWant--;
			for(i=howManyLeft; i < 200; i++){
				$("#quantity-" + pallet_id + " option[value='" + i + "']").remove();
			}
			console.log('alreadyHave: ' + alreadyHave);
			console.log('howManyMoreWeWant: ' + howManyMoreWeWant);
			console.log('q: ' + alreadyHave);
			
			document.cookie = COOKIE_NAME+"="+q;
		}else{
			document.cookie = COOKIE_NAME+"="+q;
		}
		
		// console.log('Cookie...' + getCookie(COOKIE_NAME));
		
		
		
		$.get( "scripts/getBasketItem.php?product_id="+product_id+"&pallet_id="+pallet_id+"&cut_id="+cut_id+"&q="+q, function( data ) {
			$('.basketTable').append(data);
		});

	}
	
	function toggleWeight(weightdiv){
		if($(weightdiv).hasClass('activeWeight')){
			var weight = $(weightdiv).attr('weight');
			var product_id = $(weightdiv).attr('product_id');
			calculateWeight(-weight);
			removeFromList(product_id);
			
		}else{
			var weight = $(weightdiv).attr('weight');
			var product_id = $(weightdiv).attr('product_id');
			calculateWeight(weight);
			addToList(product_id);
		}
		
		$(weightdiv).toggleClass('activeWeight');
		
	}
	
	function calculateWeight(value){
		var currentWeight = $('.weightVal').text();
		
		var newWeight = parseFloat(currentWeight) + parseFloat(value);
		
		$('.weightVal').text(newWeight);
		
	}
</script>
<style type="text/css">
	.weightbox{
		padding:10px;
		border:2px solid #cacaca;
		display:inline-block;
		cursor:pointer;
		margin-bottom:5px; 
	}
	.activeWeight { background:#3faddd !important; color:#fff !important}
	.weightbox:hover{
		background:#cacaca;
	}
</style>