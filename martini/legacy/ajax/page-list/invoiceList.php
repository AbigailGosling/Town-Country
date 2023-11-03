<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

    require(__DIR__.'/../../functions.php');

    $toSkip = request()->input('toSkip');
    $limit = 80;
			
    session_start();session_write_close();
    $userid = $_SESSION['USER'];
    $usermodel = User::find(Auth::id());
    $queryResult = prepareExecuteQuery("SELECT * FROM `pickerSheets` WHERE completed='1' AND customer_id IN (".implode(",",$usermodel->listViewableCustomers()).") ORDER BY `id` DESC LIMIT ?, ?",'ii',[$toSkip,$limit]);
    $count = mysqli_num_rows($queryResult);
    
    $newSkipCount = ($toSkip + $count);

    $totalRowsQueryResult = prepareExecuteQuery("SELECT count(id) as count FROM `pickerSheets` WHERE completed='1' AND customer_id IN (".implode(",",$usermodel->listViewableCustomers()).")");
    $totalRowsData = mysqli_fetch_array($totalRowsQueryResult);
    $totalRowsInDatabase = $totalRowsData['count'];

    while($row = mysqli_fetch_array($queryResult)){
        
        $customer_id = $row['customer_id'];
        if (!$usermodel->canViewCustomer($customer_id)) continue;
        $date = $row['estimated_delivery_date'];
        
        $date=date_create($date);
        if ($date == false)$date=DateTime::createFromFormat('d/m/Y',"".$row['estimated_delivery_date']);
        if ($date == false) continue;
        $date = date_format($date,"d/m/Y");
        
        $x2 = "SELECT * FROM `customers` WHERE id = ?";
        $y2 = prepareExecuteQuery($x2,'i',[$customer_id]);
        $row2 = mysqli_fetch_array($y2);
        
    ?>
    <tr class="pages"><td align="center" class="pos">
    <a href="invoice.php?id=<?php echo $row['id']; ?>" class="intake" style="padding-left:10px;padding-right:10px;">
        <table width="100%" border="0">
            <tr>
                <td width="100" align="left">ID: <?php echo $row['id']; ?></td>
                <td align="center" style="font-size: 18px;"><?php echo $row2['businessname']; ?></td>
                <td width="100" align="right"><?php echo $row['estimated_delivery_date']; ?></td>
            </tr>
        </table>
    </a>

    <div class="sendcontainer sendcontainer--invoice-list">
        <div class="active" picksheetid="<?php echo $row['id']; ?>" <?php if($row['invoicesent'] == 0){ echo 'style="display:none;"'; }?>>
            <i class="fa fa-check" aria-hidden="true"></i>
        </div>
    </div>
    </td></tr>
    <?php
    }
?>
<script>
    $('#toSkipCount').val(<?php echo $newSkipCount; ?>);
    $('#totalRowsCount').val(<?php echo $totalRowsInDatabase; ?>);
</script>