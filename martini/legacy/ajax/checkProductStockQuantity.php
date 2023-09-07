<?php
    require(__DIR__.'/../functions.php');
	
	$product_id = request()->input('product_id');

    $numOfWeights = numWeightsAvailableFromProductID($product_id);

    echo json_encode($numOfWeights);

?>