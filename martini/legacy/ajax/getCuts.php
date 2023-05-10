<?php
	require(__DIR__.'/../functions.php');
	
	$species = request()->input('id');
	
	$cuts = getCutsFor($species);
	
	$cutList = '';
	
	while($row = mysqli_fetch_array($cuts)){
		$cutList .= '<option value="'.$row['id'].'">'.$row['name'].'</option>';
	}
	
	echo $cutList;
	
?>