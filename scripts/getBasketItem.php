<?php
	require('../functions.php');
	
	$product_id = mysqli_real_escape_string($conn, $_GET['product_id']);
	$pallet_id = mysqli_real_escape_string($conn, $_GET['pallet_id']);
	$species_id = mysqli_real_escape_string($conn, $_GET['species_id']);
	$cut_id = mysqli_real_escape_string($conn, $_GET['cut_id']);
	$q = $_GET['q'];
	$comment = $_GET['comment'];
	
	$x = "SELECT * FROM `product` WHERE pallet_id='$pallet_id'";
	// exit();
	$y = mysqli_query($conn, $x) or die(mysqli_error($conn));
	$row = mysqli_fetch_array($y);
	
	$ubbb = $row['ubbb'];
	
	if($ubbb == 0){
		$ubtext = 'UB';
	}else{
		$ubtext = 'BB';
	}
	
	$unit = $row['unit'];
	
	if($unit == 'C'){
		$unitText = 'Cases';
	}else if ($unit == 'P'){
		$unitText = 'Pallet';
	}else if ($unit == 'PPC'){ // Packet now cases
		$unitText = 'Purchase Per Case';
	}else if ($unit == 'KG'){
		$unitText = 'Kilo';
	}else{
		$unitText = 'Cases';
	}
	
	
	$smallestDate = $row['range_from'];
	$largestDate = $row['range_to'];
	
	
	$brand_id = $row['brand_id'];
	$nationality_id = $row['nationality_id'];
	$temp_id = $row['cooling_id'];
	
	$randID = rand(100,2953);
	
	$xProduct = "SELECT * FROM `product` WHERE id='$product_id'";
	$yProduct = mysqli_query($conn, $xProduct);
	$rowProduct = mysqli_fetch_array($yProduct);
?>
<tr class="product<?php echo $product_id; ?> basketRow-<?php echo $pallet_id . $randID; ?>">
	<td><?php echo intakeIDfromPalletID($pallet_id); ?></td>
	<td><?php echo $pallet_id; ?></td>
	<td><?php echo getSpecies(getSpeciesFromCut($cut_id)); ?> <?php echo getCut($cut_id); ?></td>
	<td><?php echo getNationality($nationality_id); ?></td>
	<td><?php echo getBrand($brand_id); ?></td>
	 
	<td><?php echo $q; ?></td>
	<td>
		<input type="number" value="" name="target_weight_<?php echo $product_id; ?>" class="weightnote overviewcomment" style="border:1px solid #f2f2f2;">
	</td>
	<td><input type="number" step="0.01" cost="<?php echo $rowProduct['cost']; ?>" class="price"   name="price_<?php echo $product_id; ?>" minvalue="0" style="width:50px;text-align:center;height:30px;"></td>
	<td>
		
	</td>
	
	<td style="display:none;">	
		<?php $val = $product_id . "-" . $q . "-" . $cut_id . "-" . $rowProduct['weightnote']; ?>
		<input type="text" value="<?php echo $comment; ?>" name="commentsRow[]">
		<input type="text" value="<?php echo $val; ?>" name="basketRow[]">
	</td>
	
	<td align="right"><a href="javascript:;" onclick="removeFromList('<?php echo $pallet_id . $randID; ?>', '<?php echo $pallet_id; ?>','<?php echo $product_id; ?>');"><i class="fa fa-close" style="font-size:24px"></i></a></td>
</tr>