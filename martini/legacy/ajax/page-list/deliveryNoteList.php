<?php

use App\Models\User;
use App\Models\Location;
use Illuminate\Support\Facades\Auth;

    require(__DIR__.'/../../functions.php');

    $toSkip = request()->input('toSkip');
    $limit = 80;

    session_start();session_write_close();
    $userid = $_SESSION['USER'];
    $usermodel = User::find(Auth::id());
    $queryResult = prepareExecuteQuery("SELECT * FROM `pickerSheets` WHERE `completed`='1' AND (`customer_id` IN (".implode(",",$usermodel->listViewableCustomers()).") AND `is_return_to_supplier` = 0) OR `is_return_to_supplier` = 1 ORDER BY `id` DESC LIMIT $toSkip, $limit");
    $queryResult = $queryResult->fetch_all(MYSQLI_ASSOC);
    $count = count($queryResult);
    $pickIDs = array_column($queryResult,"id");

    $newSkipCount = ($toSkip + $count);

    $totalRowsQueryResult = prepareExecuteQuery("SELECT count(`id`) as `count` FROM `pickerSheets` WHERE `completed`='1' AND (`customer_id` IN (".implode(",",$usermodel->listViewableCustomers()).") AND `is_return_to_supplier` = 0) OR `is_return_to_supplier` = 1");
    $totalRowsData = mysqli_fetch_array($totalRowsQueryResult);
    $totalRowsInDatabase = $totalRowsData['count'];

    $result_product = prepareExecuteQuery("SELECT `pickersheet_id`,GROUP_CONCAT(DISTINCT `product_id`) as `prod_ids` FROM `pickerItems` WHERE `pickersheet_id` IN (".implode(",",$pickIDs).") GROUP BY `pickersheet_id`");
    $allProdsByPick = array();
    while($row = mysqli_fetch_assoc($result_product)){
        $allProdsByPick[$row['pickersheet_id']] = $row['prod_ids'];
    }

    foreach($queryResult as $row){

        $result_location = loggedQuery("SELECT GROUP_CONCAT(DISTINCT `pallet`.`storage_location`) as `loc` FROM `product` INNER JOIN `pallet` ON `product`.`pallet_id` = `pallet`.`id` WHERE `product`.`id` IN (".$allProdsByPick[$row['id']].") LIMIT 1");
        $location = mysqli_fetch_assoc($result_location)['loc'];
        $locs = Location::whereIn("id",explode(",",$location))->pluck("name")->toArray();
        if (count($locs) > 2) $loc = $locs[0] . "<br/>" . $locs[1] . "<br/>+ More...";
        else if (count($locs) > 1) $loc = implode("<br/>",$locs);
        else $loc = $locs[0];

        $customer_id = $row['customer_id'];

        $date = $row['estimated_delivery_date'];

        $date=DateTime::createFromFormat('d/m/Y',$date);
        $date = ($date instanceof DateTimeInterface)?date_format($date,"d/m/Y"):"INVALID DELIVERY DATE!";

        if ($row['is_return_to_supplier']==0)
        {
            $x2 = "SELECT * FROM `customers` WHERE id =?";
            $y2 = prepareExecuteQuery($x2,'i',[$customer_id]);
            $row2 = mysqli_fetch_array($y2);
        }
        else
        {
            $x2 = "SELECT * FROM `supplier` WHERE id =?";
            $y2 = prepareExecuteQuery($x2,'i',[$customer_id]);
            $row2 = mysqli_fetch_array($y2);
        }
    ?>
    <tr class="pages"><td align="center" class="pos">
    <a href="deliverynote.php?id=<?php echo $row['id']; ?>" class="intake" style="padding-left:10px;padding-right:10px;">
        <table width="100%" border="0">
            <tr>
                <td width="25%" align="left">ID: <?php echo $row['id']; ?></td>
                <td align="left" style="font-size: 18px;"><?php echo ($row['is_return_to_supplier']==0)?$row2['businessname']:$row2['name']; ?></td>
                <td align="right">
                <?php if($row['deliverynote_printed'] == 1){ ?>
                        <div class="printedLabel">Printed</div>
                    <?php } ?>
                </td>
                <td align="right" style="font-size:12px;width:70px;white-space: nowrap;line-height:1"><?php echo $loc;?></td>
                <td width="90" align="right"><?php echo $date; ?></td>
            </tr>
        </table>
    </a>
    </td></tr>
    <?php
    }
?>

<script>
    $('#toSkipCount').val(<?php echo $newSkipCount; ?>);
    $('#totalRowsCount').val(<?php echo $totalRowsInDatabase; ?>);
</script>
