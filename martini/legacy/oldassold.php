<?php
	include('functions.php');
	
	echo 'Grabbing Intakes ...<br/>';
	$intakeX = "SELECT * FROM `intake` WHERE `date_received` < '2020-04-18'";
	$intakeY = prepareExecuteQuery($intakeX);
	$count = mysqli_num_rows($intakeY);
	
	echo '('. $count . ' intakes)';
	
	while($intakeRow = mysqli_fetch_array($intakeY)){
		echo '<br/><Br/>Intake #' . $intakeRow['id'] . ' @ ' . $intakeRow['date_received'] . ' ...<br/>';
		
		$intakeID = $intakeRow['id'];
		
		$palletX = "SELECT * FROM `pallet` WHERE intake_id='$intakeID'";
		$palletY = prepareExecuteQuery($palletX);
		
		while($palletRow = mysqli_fetch_array($palletY)){
			echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Pallet #' . $palletRow['id'] . ' ' . $palletRow['intake_id'] . ' ...<br/>';
			
			$palletID = $palletRow['id'];
		
			$productX = "SELECT * FROM `product` WHERE pallet_id='$palletID' && status !='1'";
			$productY = prepareExecuteQuery($productX);
			
			while($productRow = mysqli_fetch_array($productY)){
				echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Product #' . $productRow['id'] . ' ...<br/>';
				
				$productID = $productRow['id'];
				
				$x = "UPDATE `product` SET status = '1' WHERE id='$productID'";
				$y = prepareExecuteQuery($x);
				
				$weightsX = "SELECT * FROM `weights` WHERE product_id='$productID'";
				$weightsY = prepareExecuteQuery($weightsX);
				
				while($weightsRow = mysqli_fetch_array($weightsY)){
					
					$weightid = $weightsRow['id'];
					
					$x = "UPDATE weights SET status_id='1' WHERE id='$weightid'";
					$y = prepareExecuteQuery($x);
					
				} 
				
			}
			echo '<br/>';
		}
	}
	
	
	
	
	
	
?>