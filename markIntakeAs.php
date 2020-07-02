<?php
	
	include('functions.php');
	
	if(isset($_POST['state'])){
		
		$value = $_POST['state'];
		$intakeID = $_POST['intakeid'];
		
		$palletX = "SELECT * FROM `pallet` WHERE intake_id='$intakeID'";
		$palletY = mysqli_query($conn, $palletX);
		
		while($palletRow = mysqli_fetch_array($palletY)){
 			
			$palletID = $palletRow['id'];
		
			$productX = "SELECT * FROM `product` WHERE pallet_id='$palletID'";
			$productY = mysqli_query($conn, $productX);
			
			while($productRow = mysqli_fetch_array($productY)){
 				
				$productID = $productRow['id'];
				
				$x = "UPDATE `product` SET status = '$value' WHERE id = '$productID' LIMIT 1";
				$y = mysqli_query($conn, $x);
				
				$weightsX = "SELECT * FROM `weights` WHERE product_id='$productID'";
				$weightsY = mysqli_query($conn, $weightsX);
				
			    while($weightsRow = mysqli_fetch_array($weightsY)){
					
					$weightid = $weightsRow['id'];
					
					$x = "UPDATE weights SET status_id='$value' WHERE id='$weightid'";
					$y = mysqli_query($conn, $x);
					
				}
			}
 		}
		
		?><script> window.location.href = 'intake.php?id=<?php echo $intakeID; ?>'; </script><?php
	}
	
?>