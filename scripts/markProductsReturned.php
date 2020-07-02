<?php
	require('../functions.php');
	
	$ids = $_POST['ids'];
	
	
	$ids = explode(',', $ids);
	
	
	foreach($ids as $id){
		
		echo $x = "UPDATE product SET status='0' WHERE id='$id'";
		$y = mysqli_query($conn, $x);
		echo '<br/>';
		
	}
?>
<script>
	window.location = '../returns.php';
</script>
