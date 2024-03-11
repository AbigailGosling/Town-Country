<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

require_once(__DIR__.'/../functions.php');
$userC = User::find(Auth::id());
if ($userC->hasPermission('send_picker_notification') || $userC->hasPermission('admin')) {
    $picksheetid = request()->input("pick_id");
    $message = request()->input("message");
    $locked = request()->has("lock_pick")?1:0;
    prepareExecuteQuery("UPDATE `tandc_live`.`pickerNotifications` SET `updated_at` = NOW(), lock_release = 1 WHERE pickersheet_id = ?",'i',[request()->input('pick_id')]);
    loggedDataChange("picksheet_notification",$picksheetid,$message." RELEASE LOCK");
}
?>
<script>
    window.location.href = '../viewPickSheet.php?type=<?php echo request()->input('pick_type');?>&id=<?php echo request()->input('pick_id');?>';
</script>