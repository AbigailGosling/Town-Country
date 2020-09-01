<?php
    require('../functions.php');
	
	$product_id = mysqli_real_escape_string($conn, $_GET['product_id']);

    $numOfWeights = numWeightsAvailableFromProductID($product_id);

    echo json_encode($numOfWeights);

?>