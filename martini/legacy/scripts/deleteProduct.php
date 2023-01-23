<?php
	require(__DIR__.'/../functions.php');
	
    $productID = request('productid');	
    $intakeID = request('intakeid');	
    
    if($productID != ''){
	    deleteProductEntry($productID);
	    deleteWeightsFor($productID);
    }

	
?>
<script>
	window.location = '../intake.php?id=<?php echo $intakeID; ?>';
</script>
