<?php
	require('../functions.php');
    

	$upload_dir='../docs/';

	if($_FILES['dfile']['name']!="")
	{
		$file_name=$_FILES['dfile']['name'];
		$explode = explode(".",$file_name);
		$file_name=time().".".$explode[count($explode)-1];
		$tmp_name=$_FILES['dfile']['tmp_name'];
		copy($tmp_name,$upload_dir.$file_name);
	}
	
	$intakeID = $_POST['intakeid'];
	
	$name = mysqli_real_escape_string($conn, $_POST['name']);
	
	$x = "INSERT INTO `intakeDocs` (name,dfile,intakeid) VALUES ('$name','$file_name','$intakeID')";
	$y = mysqli_query($conn, $x);
?>

<script type="text/javascript">
	window.location.href = '../intake.php?id=<?php echo $intakeID; ?>';
</script>

