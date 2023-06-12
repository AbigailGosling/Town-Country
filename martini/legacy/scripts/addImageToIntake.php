<?php
	require(__DIR__.'/../functions.php');

	$upload_dir=__DIR__.'/../docs/';
	if(request()->hasFile('dfile'))
	{
		$file_name=time().".".request()->file('dfile')->extension();
		$tmp_name=request()->file('dfile')->path();
		copy($tmp_name,$upload_dir.$file_name);
	}
	else{
		throw new \Exception("dfile not found: ".json_encode(request()->all()));
		die();exit;
	}
	
	$intakeID = request()->input('intakeid');
	
	$name = $mysqli->real_escape_string( request()->input('name'));
	
	$x = "INSERT INTO `intakeDocs` (`name`,`dfile`,`intakeid`) VALUES (?,?,?)";
	$y = prepareExecuteQuery($x,'sss',[$name,$file_name,$intakeID]);
?>

<script type="text/javascript">
	window.location.href = '../intake.php?id=<?php echo $intakeID; ?>';
</script>

