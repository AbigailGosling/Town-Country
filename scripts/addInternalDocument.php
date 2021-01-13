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
    
	$pickersheet_id = mysqli_real_escape_string($conn, $_POST['pickersheet_id']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    
    $user_id = $_SESSION['USER'];

	$result = mysqli_query($conn, "INSERT INTO `pickersheet_documents` (`user_id`,pickersheet_id,`message`,dfile,`type`) VALUES ($user_id,'$pickersheet_id','$message','$file_name','$type')") or die(mysqli_error($conn));

    if($type == 'DELIVERY_NOTE'){
        $file = 'deliverynote.php';
    }else if($type == 'INVOICE'){
        $file = 'invoice.php';
    }
?>

<script type="text/javascript">
	window.location.href = '../<?php echo $file; ?>?id=<?php echo $pickersheet_id; ?>';
</script>

