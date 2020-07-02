<?php
	include('functions.php');
	
	echo 'Grabbing Intakes ...<br/>';
	$intakeX = "SELECT * FROM `intake` WHERE `date_received` < '2020-04-18'";
	$intakeY = mysqli_query($conn, $intakeX);
	$count = mysqli_num_rows($intakeY);
	
	echo '('. $count . ' intakes)';
	
	while($intakeRow = mysqli_fetch_array($intakeY)){
		echo '<br/><Br/>Intake #' . $intakeRow['id'] . ' @ ' . $intakeRow['date_received'] . ' ...<br/>';
		
		$intakeID = $intakeRow['id'];
		
		$palletX = "SELECT * FROM `pallet` WHERE intake_id='$intakeID'";
		$palletY = mysqli_query($conn, $palletX);
		
		while($palletRow = mysqli_fetch_array($palletY)){
			echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Pallet #' . $palletRow['id'] . ' ' . $palletRow['intake_id'] . ' ...<br/>';
			
			$palletID = $palletRow['id'];
		
			$productX = "SELECT * FROM `product` WHERE pallet_id='$palletID' && status !='1'";
			$productY = mysqli_query($conn, $productX);
			
			while($productRow = mysqli_fetch_array($productY)){
				echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Product #' . $productRow['id'] . ' ...<br/>';
				
				$productID = $productRow['id'];
				
				$x = "UPDATE `product` SET status = '1' WHERE id='$productID'";
				$y = mysqli_query($conn, $x);
				
				$weightsX = "SELECT * FROM `weights` WHERE product_id='$productID'";
				$weightsY = mysqli_query($conn, $weightsX);
				
				while($weightsRow = mysqli_fetch_array($weightsY)){
					
					$weightid = $weightsRow['id'];
					
					$x = "UPDATE weights SET status_id='1' WHERE id='$weightid'";
					$y = mysqli_query($conn, $x);
					
				} 
				
			}
			echo '<br/>';
		}
	}
	
	
	
	
	
	
?>