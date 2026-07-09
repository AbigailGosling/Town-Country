<?php

use App\Http\Controllers\FileController;

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
    $f = FileController::PROCESS_REQUEST(request(),'dfile');
	$intakeID = request()->input('intakeid');
	$name = request()->input('name');
    $type_id = request()->input('type_id');
    if ($type_id === null || $type_id < 1) $type_id = 3;
	$x = "INSERT INTO `intakeDocs` (`name`,`dfile`,`intakeid`,`type_id`,`file_id`,`new_file_system`) VALUES (?,?,?,?,?,?)";
	$y = prepareExecuteQuery($x,'sssiii',[$name,$file_name,$intakeID,$type_id,$f->id,1]);
?>

<script type="text/javascript">
	window.location.href = '../intake.php?id=<?php echo $intakeID; ?>';
</script>

