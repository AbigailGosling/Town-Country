<table width="100%" class="slim searchRContent"   style="display:table;">
	<th align="left"></th>
	<th align="left">Intake ID</th>
	<th align="left">Location</th>
	<th align="left">Plt ID</th>
	<th align="left">Unit</th>
	<th align="left">Chilled/Frozen</th>
	<th align="left">Product</th>
	<th align="left">Nationality</th>
	<th align="left" width="20%">Comments</th>
	<th align="left">Brand</th>
	<th align="left">Date Range</th>
	<th align="left">Volume</th>
	<th align="left">Cost</th>
	<th align="left">RRP</th>
	<th align="left"></th>
<?php
	require('../functions.php');
	
	$cut = $_GET['cut'];
	$species_id = $_GET['species'];
	$temperatureID = $_GET['temperatureID'];
	$palletID = $_GET['palletID'];
	
 	if($cut != '' && $species_id != 0){
		$cutsX = "SELECT * FROM `cuts` WHERE name LIKE '%$cut%' AND species_id='$species_id' order by name ASC";
	}elseif($cut != ''){
		$cutsX = "SELECT * FROM `cuts` WHERE name LIKE '%$cut%' order by name ASC";
	}else{
		$cutsX = "SELECT * FROM `cuts` WHERE species_id='$species_id' order by name ASC";
	}

 
	$cutsY = mysqli_query($conn, $cutsX) or die(mysqli_error($conn));
		
		while($cutsRow = mysqli_fetch_array($cutsY)){
			$cut_id = $cutsRow['id'];
			
			
			echo $xk = "SELECT *, product.comments as productcomments, product.id as productid FROM `product` INNER JOIN `pallet` ON product.pallet_id=pallet.id WHERE product.status='0' AND product.cut_id = '$cut_id' GROUP BY pallet.intake_id,product.cut_id,product.nationality_id ORDER BY product.cooling_id ASC";
			exit();
			$productsX = "SELECT * FROM `product` WHERE cut_id = '$cut_id' GROUP BY cut_id,nationality_id ORDER BY cooling_id ASC";
			$productsY = mysqli_query($conn, $xk) or die(mysqli_error($conn));
			$productsCount = mysqli_num_rows($productsY);
			 
			$totalW = 0;
				
			 
			while($productsRow = mysqli_fetch_array($productsY)){
				$class = 'KIS'.rand(1,999999);
				$temp_id = $productsRow['cooling_id'];
				$ubbb = $productsRow['ubbb'];
				$smallestDate = $productsRow['range_from'];
				$largestDate = $productsRow['range_to'];
				
				$nationality_id = $productsRow['nationality_id'];
				
				if($ubbb == 0){
					$ubtext = 'UB';
				}else if($ubbb == 1){
					$ubtext = 'BB';
				}else{
					$ubtext = 'N/A';
				}
				
				$pallet_id = $productsRow['pallet_id'];
				
				$x = "SELECT * FROM pallet WHERE id='$pallet_id'";
				$y = mysqli_query($conn, $x);
				$row = mysqli_fetch_array($y);
				$intake_id = $row['intake_id'];
				
				########## Used to GROUP BY pallet.intake_id
				$productsX2 = "SELECT * , product.id productid FROM `product` INNER JOIN `pallet` ON product.pallet_id=pallet.id WHERE product.status='0' AND pallet.intake_id=$intake_id && product.cut_id = '$cut_id' && product.nationality_id='$nationality_id'";
				$productsY2 = mysqli_query($conn, $productsX2) or die(mysqli_error($conn));
				$productsY22 = mysqli_query($conn, $productsX2) or die(mysqli_error($conn));
				$products2Count = mysqli_num_rows($productsY2);
				
				$totalProducts = 0;
				while($row323 = mysqli_fetch_array($productsY22)){
					$totalW += weightSoldFromProductID($row323['productid']);
					$totalProducts += weightsAvailableOnProduct($row323['productid']);

				}
				##########
				
				
				if($totalProducts > 0){
			
		?>
		<tr class="searchAccordTitle">
			<td width="40" align="center" onclick="toggleRow('<?php echo $class; ?>');"><?php if($products2Count > 0){ ?><i class="fa fa-chevron-down"></i><?php } ?></td>
			<td colspan="1">
				<a href="intake.php?id=<?php echo intakeIDfromPalletID($pallet_id); ?>&ref=salesconfirmationsheet" style="color:#000;text-decoration:underline;">
					<b><?php echo intakeIDfromPalletID($pallet_id); ?></b>
				</a>
			</td>
			<td colspan="1">
				<?php
					$palletx = "SELECT * FROM `pallet` WHERE id='$pallet_id'";
					$pallety = mysqli_query($conn, $palletx);
					$pallet = mysqli_fetch_array($pallety);
				?>				 
			</td>
			<td colspan="1"  onclick="toggleRow('<?php echo $class; ?>');"><?php echo $productsRow['pallet_id']; ?></td>
			<td colspan="1">
				<?php
					$intakeid = intakeIDfromPalletID($productsRow['pallet_id']);
					$cut_id = $productsRow['cut_id'];
                    // echo $totalCount = getTotalNumOfWeights($intakeid, $cut_id);
                    // echo $numOfWeights = countNumProductsForCutOnPalletThatIsntPicked($productsRow['pallet_id'], $productsRow['cut_id']);
                    //echo $totalCount = getTotalNumOfWeights($intakeid, $cut_id);
					echo $totalProducts;
				?>
			</td>
			<td align="left" <?php if($temp_id == 1){ echo 'style="background:#c0392b;color:#fff;padding:5px;"'; }else { echo 'style="background:#2980b9;color:#fff;padding:5px;"'; } ?>><?php echo getTemp($temp_id); ?></td>
			<td colspan="1"><?php echo getCut($productsRow['cut_id']); ?></td>
			<td colspan="1"><?php echo getNationality($productsRow['nationality_id']); ?></td>
			<td colspan="1">
				<form method="post"><?php # $productsRow['productcomments']; ?>
					<textarea name="comments" class="overviewcomment" productid="<?php echo $productsRow['productid']; ?>"><?php echo $productsRow['weightnote']; ?></textarea>
					<input type="text" name="pallet_id" class="pallet" value="<?php echo $pallet_id; ?>" style="display:none;">
				</form>
			</td>
			<td align="left"><?php echo getBrand($productsRow['brand_id']); ?></td>
			<td><?php if($ubbb != 2){ echo $ubtext . ' ' . $smallestDate . ' - ' . $largestDate; }else { echo $ubtext; } ?></td>
			<td><?php 
                
                
                $productid = $productsRow['productid'];
                $xX = "select * from `weights` WHERE product_id ='$productid'";
                $yY = mysqli_query($conn, $xX);

                $weightt = mysqli_fetch_array($yY);

                $original_gross = number_format($weightt['original_gross'], 2, '.', '');
                $num_cartons = number_format($weightt['number_of_cartons'], 2, '.', '');
                $pallet_tare = number_format($weightt['pallet_tare'], 2, '.', '');
                $tare_per_carton = number_format($weightt['tare_per_carton'], 2, '.', '');
                
                $carton_tare = $num_cartons * $tare_per_carton;
                
                $total_tare = $carton_tare + $pallet_tare;
                
                $tare = $original_gross - $total_tare;
                
				if($row['grosspallet'] == 1){
                    
                    echo '[GT] ' . number_format($totalW, 2, '.', '');
					$totalW = 0;
                }else{
                    echo $totalW;
                    $totalW = 0;
                }
                 
				?>kg</td>
			<td><?php  if($productsRow['cost']){ echo '£' . number_format((float)$productsRow['cost'], 2, '.', ''); } ?></td>
			<td><?php  if($productsRow['price']){ echo '£' . number_format((float)$productsRow['price'], 2, '.', ''); } ?></td>
		</tr>
		
		<?php
				}
		if($products2Count > 0){
			while($productsRow2 = mysqli_fetch_array($productsY2)){
				$smallestDate = $productsRow2['range_from'];
				$largestDate = $productsRow2['range_to'];
				$pallet_id = $productsRow2['pallet_id'];
				$product_id = $productsRow2['productid'];
				
				$numOfWeights = countNumProductsForCutOnPallet($productsRow2['pallet_id'], $productsRow2['cut_id']);
				if($numOfWeights > 0){
			?>
			<tr style="background:#d9d9d9;display:none;" class="subrow <?php echo $class; ?>">
				<td></td>
				<td colspan="1"><a href="intake.php?id=<?php echo intakeIDfromPalletID($pallet_id); ?>&ref=salesconfirmationsheet" style="color:#000;text-decoration:underline;"><b><?php echo intakeIDfromPalletID($pallet_id); ?></b></a></td>
				<td colspan="1">
					<?php
						$palletx = "SELECT * FROM `pallet` WHERE id='$pallet_id'";
						$pallety = mysqli_query($conn, $palletx);
						$pallet = mysqli_fetch_array($pallety);
						 
					?>
					<form method="post">
						<input type="text" name="location" class="location" value="<?php echo $pallet['storage_location']; ?>" placeholder="location" style="width:90px;">
						<input type="text" name="pallet_id" class="pallet" value="<?php echo $pallet_id; ?>" style="display:none;">
					</form>
				</td>
				<td colspan="1"><?php echo $productsRow2['pallet_id']; ?></td>
				<td colspan="1">
					<?php
                        echo $numOfWeights = countNumProductsForCutOnPallet($productsRow2['pallet_id'], $productsRow2['cut_id']);
                        
                        // $numOfWeights = countNumProductsForCutOnPalletThatIsntPicked($productsRow2['pallet_id'], $productsRow2['cut_id']);
						
						if($numOfWeights){ 
					?>
					<select class="quantitybox" id="quantity-<?php echo $productsRow2['productid']; ?>-<?php echo $productsRow2['pallet_id']; ?>">
						<?php for($i=1;$i<$numOfWeights+1;$i++){?>
							<option value="<?php echo $i; ?>" selected><?php echo $i; ?></option>
						<?php } ?>
					</select>
						<?php } ?>
				</td>
				<td align="left" <?php if($temp_id == 1){ echo 'style="background:#a02f24;color:#fff;padding:5px;"'; }else { echo 'style="background:#2980b9;color:#fff;padding:5px;"'; } ?>><?php echo getTemp($temp_id); ?></td>
				<td colspan="1"><?php echo getCut($productsRow2['cut_id']); ?></td>
				<td colspan="1"><?php echo getNationality($productsRow2['nationality_id']);?></td>
				<td colspan="1"></td>
				<td align="left"><?php echo getBrand($productsRow2['brand_id']); ?></td>
				<td><?php echo $ubtext . ' ' . $smallestDate . ' - ' . $largestDate; ?></td>
				
				<td><?php 
				$productid = $productsRow['productid'];
                $xX = "select * from `weights` WHERE product_id ='$productid'";
                $yY = mysqli_query($conn, $xX);

                $weightt = mysqli_fetch_array($yY);

                $original_gross = number_format($weightt['original_gross'], 2, '.', '');
                $num_cartons = number_format($weightt['number_of_cartons'], 2, '.', '');
                $pallet_tare = number_format($weightt['pallet_tare'], 2, '.', '');
                $tare_per_carton = number_format($weightt['tare_per_carton'], 2, '.', '');
                
                $carton_tare = $num_cartons * $tare_per_carton;
                
                $total_tare = $carton_tare + $pallet_tare;
                
                $tare = $original_gross - $total_tare;
                
                if($weightt['grosstare'] == 1){
                    echo '(GT) ' . number_format($tare, 3, '.', ''); 
                }else{
                    echo $totalW;
                    $totalW = 0;
                }
				?>kg</td>
				<td></td>
				<td></td>
			</tr>
			<?php
			}
			}
		}

		}
		}
?>
 

<script type="text/javascript">
	
	
	function toggleRow(classS){
		$('.' + classS).toggle();
	}
	
	function getCookie(name) {
		var value = "; " + document.cookie;
		var parts = value.split("; " + name + "=");
		if (parts.length == 2) return parts.pop().split(";").shift();
	}
	
	$('.searchRHeading').click(function(){
		$(this).next('.searchRContent').toggle();
	});
	
	function addToSheet(product_id, pallet_id, cut_id, theClass){
		
		var q = $('#quantity-' + product_id + '-' + pallet_id).val();
		var comment = $('#comment-' + product_id + '-' + pallet_id).val();
	
 	
		// console.log(comment);
		
		var COOKIE_NAME = "quantity-"+product_id+"-"+pallet_id;
		// console.log('Looking for cookie......:' + COOKIE_NAME);
		
		
		if(getCookie(COOKIE_NAME)){
			// console.log('we got cookie');
			
			var howMany = getCookie(COOKIE_NAME);
			
			var x = Number(howMany)+Number(q);
			document.cookie = COOKIE_NAME + "=" + x;
			// console.log(howMany);
			
		}else{
			// console.log('setting cookie!');
			document.cookie = COOKIE_NAME + "=" + q;
		}
			 
		var howManyBefore = $('#quantity-' + product_id + '-' + pallet_id).children('option').length;
		if(howManyBefore > q){
			for(i=0; i < q; i++){
				$("#quantity-" + product_id + "-" + pallet_id + " option:last").remove();
			}
		}else{
			for(i=0; i < q; i++){
				$("#quantity-" + product_id + "-" + pallet_id + " option:last").remove();
				$("#quantity-" + product_id + "-" + pallet_id).parent().parent().css('opacity','0.3');
				$("#quantity-" + product_id + "-" + pallet_id).parent().parent().css('pointer-events','none');
			}
		}
		
		var howManyAfter = $('#quantity-' + product_id + '-' + pallet_id).children('option').length;
  
		$('#quantity-' + product_id + '-' + pallet_id).val($('#quantity-' + product_id + '-' + pallet_id + ' option:last').val());

		$.get( "/scripts/getBasketItem.php?product_id="+product_id+"&pallet_id="+pallet_id+"&cut_id="+cut_id+"&q="+q+"&comment="+comment, function( data ) {
			$('.basketTable').append(data);
		});
		
		$('#loadResults').html('');
		// $('#KIS428319').toggle();
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
	
	$(document).ready(function(){
		$.each(document.cookie.split(/; */), function()  {
		  var splitCookie = this.split('=');

			
			if(splitCookie[0].includes('quantity-')){
				console.log(splitCookie[0]);
				var q = splitCookie[1];
				
				var howManyBefore = $('#' + splitCookie[0]).children('option').length;
				
				if(howManyBefore > q){
					for(i=0; i < q; i++){
						$('#' + splitCookie[0] + " option:last").remove();
					}
				}else{
					for(i=0; i < q; i++){
						$('#' + splitCookie[0] + " option:last").remove();
						$('#' + splitCookie[0]).parent().parent().css('opacity','0.3');
						$('#' + splitCookie[0]).parent().parent().css('pointer-events','none');
					}
				}	
			}
		});
		
		$('#saveLocation').click(function(){
			var location = $(this).parent().find('.location').val();
			var pallet = $(this).parent().find('.pallet').val();
			
			$.get("<?php echo $domain; ?>ajax/saveLocation.php?location="+location+'&pallet='+pallet, function(data, status){
				// console.log(data);
			});
		});
		
		$('.location').each(function(){
			$(this).on('keypress',function(e) {
				if(e.which == 13) {
					var location = $(this).parent().find('.location').val();
					var pallet = $(this).parent().find('.pallet').val();
					
					$.get("<?php echo $domain; ?>ajax/saveLocation.php?location="+location+'&pallet='+pallet, function(data, status){
						// console.log(data);
					});
				}
			});
		});
		
		$('.overviewcomment').each(function(){
			$(this).on('keypress', function(e){
				if(e.which == 13){
					var currentComment = $(this).val();
					//currentComment += "#";
					$(this).val(currentComment);
					
					console.log('CurrentComment: ' + currentComment);
 					// var pallet = $(this).parent().find('.pallet').val();
					
					var productid = $(this).attr('productid');
					// var productid = 10;
					
					// $.get("<?php echo $domain; ?>ajax/saveCommentPicker.php?comment="+currentComment+'&productid=1'+productid, function(data, status){
						// console.log(data);
					// });
					
					$.ajax({
						method: "POST",
						url: "<?php echo $domain; ?>ajax/saveCommentPicker.php",
						data: {
							comment:currentComment,
							productid:productid
						},
					}).done(function( result ) {
						console.log(result);
					});
					

				}
			});
		});
		
		$('.quantitybox').change(function(){
			 			
 			$('.subrow').removeClass('activeRedRow');
			$(this).parent().parent().addClass('activeRedRow');
 		});
	});
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