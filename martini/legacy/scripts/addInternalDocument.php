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

	$pickersheet_id = request()->input('pickersheet_id');
    $type = request()->input('type');
    $message = request()->input('message');

    $user_id = $_SESSION['USER'];

	$result = prepareExecuteQuery("INSERT INTO `pickersheet_documents` (`user_id`,`pickersheet_id`,`message`,`dfile`,`type`,`pod`) VALUES (?,?,?,?,?,?)",'iisssi',[$user_id,$pickersheet_id,$message,$file_name,$type,request()->has("pod")?1:0]);

    if($type == 'DELIVERY_NOTE'){
        $file = 'deliverynote.php';
    }else if($type == 'INVOICE'){
        $file = 'invoice.php';
    }
?>

<script type="text/javascript">
	window.location.href = '../<?php echo $file; ?>?id=<?php echo $pickersheet_id; ?>';
</script>

