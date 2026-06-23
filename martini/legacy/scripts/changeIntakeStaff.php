<?php

use Illuminate\Support\Facades\Log;

	require(__DIR__.'/../functions.php');

    Log::error("",request()->all());
	$intake_id = request()->input('intake_id');
	$staff_name = request()->input('staff_name');
    $userID = prepareExecuteQuery("SELECT `user_id` FROM `intake` WHERE `id` = ?",'i',[$intake_id])->fetch_assoc()["user_id"];

    if ($userID == "UNKNOWN")$y = prepareExecuteQuery("UPDATE `intake` SET `user_id` = ? WHERE `id` = ? LIMIT 1",'ii',[$staff_name,$intake_id]);
?>
<script> window.location.href = '/intake.php?id=<?php echo $intake_id; ?>'; </script>
