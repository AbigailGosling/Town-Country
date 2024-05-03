<?php

use App\Models\Intake;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

require(__DIR__.'/../functions.php');

$user = User::find(Auth::id());
$intake = Intake::find(request()->input('intake_id'));
if ($user->hasPermission("approve_intake") && $intake->approved == false) 
{
    $x = "UPDATE `intake` SET `approved` = 1, `approved_by` = ?, `approved_date` = CURRENT_TIMESTAMP() WHERE id = ?";
    $y = prepareExecuteQuery($x,'ii',[$user->id,$intake->id]);
}
?>
<script>
	window.location = '../intake.php?id=<?php echo $intake->id; ?>';
</script>