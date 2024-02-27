<?php

use App\Models\Intake;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

require(__DIR__.'/../functions.php');

$user = User::find(Auth::id());
$intake = Intake::find(request()->input('intake_id'));
if ($user->hasPermission("approve_intake") && $intake->approved == false) 
{
    $intake->approved = true;
    $intake->approved_by = $user->id;
    $intake->approved_date = new DateTime();
    $intake->save();
}
?>
<script>
	window.location = '../intake.php?id=<?php echo $intake->id; ?>';
</script>