<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

require_once(__DIR__.'/../functions.php');
$userC = User::find(Auth::id());
if ($userC->hasPermission('send_picker_notification')) {
    $picksheetid = request()->input("pick_id");
    $message = request()->input("message",'');
    $locked = request()->has("lock_pick")?1:0;
    prepareExecuteQuery("INSERT INTO `tandc_live`.`pickerNotifications` (`user_id`, `pickersheet_id`, `message`, `locked`,`created_at`) VALUES (?,?,?,?,NOW())",'iisi',[$userC->id,$picksheetid,$message,$locked]);
    $lockstatus =  $locked ? "LOCKED":"";
    loggedDataChange("picksheet_notification",$picksheetid,$message." ".$lockstatus);
}
?>
<script>
    window.location.href = '../viewPickSheet.php?type=<?php echo request()->input('pick_type');?>&id=<?php echo request()->input('pick_id');?>';
</script>