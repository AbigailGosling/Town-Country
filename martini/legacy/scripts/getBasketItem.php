<?php

use App\Models\ContainerProduct;
use App\Models\InboundContainer;

	require(__DIR__.'/../functions.php');
	$product_id = request()->input('product_id');
	$pallet_id = request()->input('pallet_id');
	$species_id = request()->input('species_id');
	$cut_id = request()->input('cut_id');
	$dateParsed = request()->input('date');
	$q = request()->input('q');
	$comment = request()->input('comment');
	$container = request()->has('container');
	$x = "SELECT * FROM `product` WHERE id=?";
	// exit();
	$y = prepareExecuteQuery($x,'i',[$product_id]);
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


	$smallestDate = ($row['range_extension']!= null && $row['range_extension']!= '')?$row['range_extension']:$row['range_from'];
	$largestDate = ($row['range_extension']!= null && $row['range_extension']!= '')?$row['range_extension']:$row['range_to'];


	$brand_id = $row['brand_id'];
	$nationality_id = $row['nationality_id'];
	$temp_id = $row['cooling_id'];

	$randID = rand(100,2953);

	$xProduct = "SELECT * FROM `product` WHERE id=?";
	$yProduct = prepareExecuteQuery($xProduct,'i',[$product_id]);
	$rowProduct = mysqli_fetch_array($yProduct);

	$pallet = "SELECT * FROM pallet WHERE id = ?";
	$pallet = prepareExecuteQuery($pallet,'i',[$pallet_id]);
	$pallet = mysqli_fetch_assoc($pallet);

	if (NULL != $pallet)$siteid = prepareExecuteQuery("SELECT site_id FROM `location` WHERE id = ".$pallet['storage_location'])->fetch_assoc()['site_id'];
    else $siteid = 1;
	$site = prepareExecuteQuery("SELECT * FROM `site` WHERE id = ".$siteid)->fetch_assoc();
?>
<tr class="product<?php echo $product_id; ?> basketRow-<?php echo $pallet_id . $randID; ?> siteid<?php echo $siteid; ?>">
	<?php
        if ($container == false) { ?>
    <td><?php echo intakeIDfromPalletID($pallet_id); ?></td>
	<td><?php echo $pallet_id; ?></td>
	<td><?php echo $site['abbreviation']; ?></td>
        <?php } else {
            $containerP = ContainerProduct::where("product_id",$product_id)->first();
            $containerInfo = InboundContainer::find($containerP->container_id);
            ?>
    <td><?php echo $containerInfo->internal_number; ?></td>
            <?php }
    ?>
	<td><?php echo getSpecies(getSpeciesFromCut($cut_id)); ?> <?php echo getCut($cut_id); ?></td>
	<td><?php echo getNationality($nationality_id); ?></td>
	<td><?php echo getBrand($brand_id); ?></td>
	<td id="ubDate"><?php echo $smallestDate; ?></td>
	<td><?php echo $q; ?></td>
	<td>
		<input type="number" value="" name="target_weight_<?php echo $product_id; ?>" class="weightnote overviewcomment" style="border:1px solid #f2f2f2;">
	</td>
	<td><input type="number" step="0.01" cost="<?php echo $rowProduct['cost']; ?>" class="price"   name="price_<?php echo $product_id; ?>" minvalue="0" style="width:50px;text-align:center;height:30px;"></td>
	<td>

	</td>
	<td id=temp_id style="display:none;"><?php echo $temp_id; ?></td>
	<td style="display:none;">
		<?php $val = $product_id . "-" . $q . "-" . $cut_id . "-" . $rowProduct['weightnote']; ?>
		<input type="text" value="<?php echo $comment; ?>" name="commentsRow[]">
		<input type="text" value="<?php echo $val; ?>" name="basketRow[]">
	</td>

	<td align="right"><a href="javascript:;" onclick="removeFromList('<?php echo $pallet_id . $randID; ?>', '<?php echo $pallet_id; ?>','<?php echo $product_id; ?>');"><i class="fa fa-close" style="font-size:24px"></i></a></td>
</tr>
