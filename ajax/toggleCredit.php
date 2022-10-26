<?php

require('../functions.php');

	
$id = $_POST['id'];
 

$x = "UPDATE `customers` SET credit_enabled = NOT credit_enabled WHERE id = $id";

$y = mysqli_query($conn, $x) or die(mysqli_error($conn));

?>