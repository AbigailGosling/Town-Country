<?php
	require(__DIR__.'/../functions.php');
	
	$intake_id = request()->input('intake_id');
	$pallet_id = request()->input('pallet_id');
	
	
	$x = "SELECT * FROM `intake` WHERE id= ?";
	$y = prepareExecuteQuery($x,'i',[$intake_id]);
	$intake = mysqli_fetch_array($y);
?>
<div class="title">
	<div  onselectstart="return false" style="font-size:12px;display:block;width:100%;float:left; cursor:pointer;padding-left: 4px;">Intake No. 0000<?php echo $intake_id; ?></div> 
</div>
<div class="overview">
	<div style="display:block;float:left;padding:5px;margin:5px;text-align:center;border:1px solid grey">
		<label style="font-size:12px;border-bottom:1px solid grey;">Supplier ID</label><br/>
		<?php echo $intake['supplier_id']; ?>
	</div>
	
	<div style="display:block;float:left;padding:5px;margin:5px;text-align:center;border:1px solid grey">
		<label style="font-size:12px;border-bottom:1px solid grey;">Vehicle Registration</label><br/>
		<?php echo $intake['vehicle_reg']; ?>
	</div>
	
	<div style="display:block;float:left;padding:5px;margin:5px;text-align:center;border:1px solid grey">
		<label style="font-size:12px;border-bottom:1px solid grey;">Date Recieved</label><br/>
		<?php echo $intake['date_received']; ?>
	</div>
	
	<div style="display:block;float:left;padding:5px;margin:5px;text-align:center;border:1px solid grey">
		<label style="font-size:12px;border-bottom:1px solid grey;">Vehicle Temp</label><br/>
		<?php echo $intake['vehicle_temperature']; ?>
	</div>
	
	<div style="display:block;float:left;padding:5px;margin:5px;text-align:center;border:1px solid grey">
		<label style="font-size:12px;border-bottom:1px solid grey;">Delivery Note Number</label><br/>
		<?php echo $intake['delivery_note_number']; ?>
	</div>
	
	<div style="display:block;float:left;padding:5px;margin:5px;text-align:center;border:1px solid grey">
		<label style="font-size:12px;border-bottom:1px solid grey;">Staff Name</label><br/>
		<?php echo getUsername($intake['user_id']); ?>
	</div>
	
	<div style="clear:both;"></div>
</div>
<br/><br/>

<div class="title">
	<div onselectstart="return false" style="font-size:12px;display:block;width:50%;float:left; cursor:pointer;padding-left: 4px;">Pallet No. <span>0000<?php echo $pallet_id; ?></span></div> 
</div>
<div class="overview">
	<?php
		$x2 = "SELECT * FROM species";
		$y2 = prepareExecuteQuery($x2);
		
		while($species = mysqli_fetch_array($y2)){
			
			# Vars
			$species_id = $species['id'];
			$species_name = $species['name'];
			
			
			$x3 = "SELECT * FROM products where pallet_id = ? AND species_id = ?";
			$y3 = prepareExecuteQuery($x3,'ii',[$pallet_id,$species_id]);
			
			$products = mysqli_fetch_array($y3);
			
			$product_id = $products['id'];
			$num_of_products = mysqli_num_rows($y3);
			
			$boxes = getBoxesFor($product_id);
			
			$unit = $boxes['unit'];
			
			if($num_of_products > 0){
				
				if($unit == 'PP'){
					$unittext = 'Packet';
				}else if ($unit == 'C'){
					$unittext = 'Case';
				}else if($unit == 'P'){
					$unittext = 'Pallet';
				}else{
					$unittext = $unit;
				}
				
				$weight = getWeightFor($product_id);
				$weight = '';
				
				$products2X = "SELECT * FROM `products` WHERE pallet_id = ? AND species_id = ?";
				$products2Y = prepareExecuteQuery($products2X,'ii',[$pallet_id,$species_id]);
				
				$products2Count = mysqli_num_rows($products2Y);
				$weightCount = 0.0;
				$CutIDS = array();
				while($products2Row = mysqli_fetch_array($products2Y)){
					$weightCount += weightOfProduct($products2Row['id']);
					$overallWeight += weightOfProduct($products2Row['id']);
					if(!in_array($products2Row['cut_id'], $CutIDS)) {
						array_push($CutIDS, $products2Row['cut_id']);
					}
					
				}
				
				foreach ($CutIDS as &$value) {
					// $cutText .= getCut($value) . ", ";
					$cutText = getCut($value);
					
					$xK = "SELECT * FROM `products` WHERE pallet_id=? AND species_id=? AND cut_id=?";
					$yK = prepareExecuteQuery($xK,'iis',[$pallet_id,$species_id,$value]);
					
					$numOfProducts = mysqli_num_rows($yK);
					
					echo "<div style='font-size:14px;padding-top:10px;float:left;width:100%;padding-left: 7px;'>x$numOfProducts $unittext of $species_name - $cutText [$weightCount kg]</div>";
					// echo '<br/>';
					$counter = 0;
					while($rowD = mysqli_fetch_array($yK)){
						 
						
						if($counter == 10){
							echo "<div style='clear:both;'></div>";
							$counter = 0;
						}
						$counter++;
						?>
						<div style="font-size:18px;display:block;float:left;border:1px solid grey;padding:5px;margin:5px;">
						<?php echo weightOfProduct($rowD['id']); ?>
						</div> 
						
						<?php
					}
				}
				
				
				unset($CutIDS);
				$CutIDS = array();
				$cutText = "";
			}
	
		}	
			?>
			<script type="text/javascript">
				$(document).ready(function(){
					
					$('#overallWeight-<?php echo $pallet_id; ?>').html('Pallet Weight: <?php echo $overallWeight; ?>kg');
				});
			</script>
			<?php	
			$overallWeight = 0;
	?>
	<div style="clear:both;"></div>
</div>