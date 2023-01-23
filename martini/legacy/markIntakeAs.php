<?php
	
	include('functions.php');
	
	if(isset(request('state'))){
		
		$value = request('state');
		$intakeID = request('intakeid');
		
		$palletX = "SELECT * FROM `pallet` WHERE intake_id=?";
		$palletY = prepareExecuteQuery($palletX,'i',[$intakeID]);
		
		while($palletRow = mysqli_fetch_array($palletY)){
 			
			$palletID = $palletRow['id'];
		
			$productX = "SELECT * FROM `product` WHERE pallet_id=?";
			$productY = prepareExecuteQuery($productX,'i',[$palletID]);
			
			while($productRow = mysqli_fetch_array($productY)){
 				
				$productID = $productRow['id'];
				
				$x = "UPDATE `product` SET status = ? WHERE id = ? LIMIT 1";
				$y = prepareExecuteQuery($x,'ii',[$value,$productID]);
				
				$weightsX = "SELECT * FROM `weights` WHERE product_id=?";
				$weightsY = prepareExecuteQuery($weightsX,'i',[$productID]);
				
			    while($weightsRow = mysqli_fetch_array($weightsY)){
					
					$weightid = $weightsRow['id'];
					
					$x = "UPDATE weights SET status_id=? WHERE id=?";
					$y = prepareExecuteQuery($x,'si',[$value,$weightid]);
					
				}
			}
 		}
		
		?><script> window.location.href = 'intake.php?id=<?php echo $intakeID; ?>'; </script><?php
	}
	
?>