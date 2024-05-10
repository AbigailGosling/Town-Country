<?php
	require(__DIR__.'/../functions.php');
	
    $productID = request()->input('productid');	
    $intakeID = request()->input('intakeid');	
    
    if($productID != '' && $productID != null && $productID != -1 && $productID != "-1"){
        loggedDataChange('product_force_delete',$pallet_id,'User Deleted Product');
	    deleteProductEntry($productID);
	    deleteWeightsFor($productID);
    }

	
?>
<script>
	window.location = '../intake.php?id=<?php echo $intakeID; ?>';
</script>
