<?php
	require(__DIR__.'/../functions.php');

	$upload_dir=__DIR__.'/../docs/';
	if(request()->file('dfile'))
	{
		$file_name=time().".".request()->file('dfile')->extension();
		$tmp_name=request()->file('dfile')->path();
		copy($tmp_name,$upload_dir.$file_name);
	}
	
	$intakeID = request()->input('intakeid');
	
	$name = $mysqli->real_escape_string( request()->input('name'));
	
	$x = "INSERT INTO `intakeDocs` (`name`,`dfile`,`intakeid`) VALUES (?,?,?)";
	$y = prepareExecuteQuery($x,'sss',[$name,$file_name,$intakeID]);
?>

<script type="text/javascript">
	window.location.href = '../intake.php?id=<?php echo $intakeID; ?>';
</script>

