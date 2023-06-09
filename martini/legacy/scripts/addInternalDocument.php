<?php
	require(__DIR__.'/../functions.php');
    
    
    $upload_dir=__DIR__.'/../docs/';
	if(request()->file('dfile'))
	{
		$file_name=time().".".request()->file('dfile')->extension();
		$tmp_name=request()->file('dfile')->path();
		copy($tmp_name,$upload_dir.$file_name);
	}
	
    
	$pickersheet_id = $mysqli->real_escape_string( request()->input('pickersheet_id'));
    $type = $mysqli->real_escape_string( request()->input('type'));
    $message = $mysqli->real_escape_string( request()->input('message'));
    
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

