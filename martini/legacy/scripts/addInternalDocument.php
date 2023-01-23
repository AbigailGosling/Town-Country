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
    
	$pickersheet_id = $mysqli->real_escape_string( request('pickersheet_id'));
    $type = $mysqli->real_escape_string( request('type'));
    $message = $mysqli->real_escape_string( request('message'));
    
    $user_id = $_SESSION['USER'];

	$result = prepareExecuteQuery("INSERT INTO `pickersheet_documents` (`user_id`,`pickersheet_id`,`message`,`dfile`,`type`) VALUES (?,?,?,?,?)",'iisss',[$user_id,$pickersheet_id,$message,$file_name,$type]);

    if($type == 'DELIVERY_NOTE'){
        $file = 'deliverynote.php';
    }else if($type == 'INVOICE'){
        $file = 'invoice.php';
    }
?>

<script type="text/javascript">
	window.location.href = '../<?php echo $file; ?>?id=<?php echo $pickersheet_id; ?>';
</script>

