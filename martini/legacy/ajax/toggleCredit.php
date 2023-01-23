<?php

require(__DIR__.'/../functions.php');

	
$id = request('id');
 

$x = "UPDATE `customers` SET credit_enabled = NOT credit_enabled WHERE id = ?";

$y = prepareExecuteQuery($x,'i',[$id]);

?>