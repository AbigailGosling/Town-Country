<?php
	require(__DIR__.'/../functions.php');
    

	$upload_dir='../docs/';

	if($_FILES['dfile']['name']!="")
	{
		$file_name=$_FILES['dfile']['name'];
		$explode = explode(".",$file_name);
		$file_name=time().".".$explode[count($explode)-1];
		$tmp_name=$_FILES['dfile']['tmp_name'];
		copy($tmp_name,$upload_dir.$file_name);
	}
	
	$intakeID = request('intakeid');
	
	$name = $mysqli->real_escape_string( request('name'));
	
	$x = "INSERT INTO `intakeDocs` (`name`,`dfile`,`intakeid`) VALUES (?,?,?)";
	$y = prepareExecuteQuery($x,'sss',[$name,$file_name,$intakeID]);
?>

<script type="text/javascript">
	window.location.href = '../intake.php?id=<?php echo $intakeID; ?>';
</script>

