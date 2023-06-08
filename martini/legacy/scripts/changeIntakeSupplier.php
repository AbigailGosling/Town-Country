<?php
	require(__DIR__.'/../functions.php');

	
	$intake_id = request()->input('intake_id');
	$supplier_id = request()->input('supplier_id');
    
    
    $y = prepareExecuteQuery("UPDATE `intake` SET supplier_id=? WHERE id=? LIMIT 1",'ii',[$supplier_id,$intake_id]);
?>
<script> window.location.href = '/intake.php?id=<?php echo $intake_id; ?>'; </script>