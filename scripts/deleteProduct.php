<?php
	require('../functions.php');
	
    $productID = $_GET['productid'];	
    $intakeID = $_GET['intakeid'];	
    
    if($productID != ''){
	    deleteProductEntry($productID);
	    deleteWeightsFor($productID);
    }

	
?>
<script>
	window.location = '../intake.php?id=<?php echo $intakeID; ?>';
</script>
