<?php
	require(__DIR__.'/../functions.php');
	
	$ids = request('ids');
	
	
	$ids = explode(',', $ids);
	
	
	foreach($ids as $id){
		
		echo $x = "UPDATE product SET `status`='0' WHERE id=?";
		$y = prepareExecuteQuery($x,'i',[$id]);
		echo '<br/>';
		
	}
?>
<script>
	window.location = '../returns.php';
</script>
