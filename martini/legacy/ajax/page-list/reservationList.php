<?php
    require(__DIR__.'/../../functions.php');
    require(__DIR__.'/../../scripts/SLabsEmailer.php');

use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use InternalScripts\SLabsEmailerStatus;
    $toSkip = request()->input('toSkip');
    $limit = 80;
    $searchterm = request()->input('searchterm');
    /** @var User $usermodel */
	$usermodel = User::find(Auth::id());
    $canDelete = $usermodel->hasPermission(Permission::where("name","Can Delete Reservation")->first());
	if($searchterm != ''){
        # Check if any customer names match the search input
        $customerIDs = [];
        $resProdIDs=[];
        $customerResult = prepareExecuteQuery("SELECT * FROM `customers` WHERE businessname LIKE ? || REPLACE(businessname, ' ', '') LIKE ?"
            ,'ss',['%'.$searchterm.'%','%'.$searchterm.'%']);
        while($customer = mysqli_fetch_array($customerResult)){
            if (!$usermodel->canViewCustomer($customer['id']) && !$usermodel->hasPermission("view_all_sale_confirmations")) continue;
            array_push($customerIDs, $customer['id']);
        }
        $customerResult = prepareExecuteQuery("SELECT * FROM `supplier` WHERE `name` LIKE ? || REPLACE(`name`, ' ', '') LIKE ?"
            ,'ss',['%'.$searchterm.'%','%'.$searchterm.'%']);
        while($customer = mysqli_fetch_array($customerResult)){
            array_push($customerIDs, $customer['id']);
        }
        $containerResult = prepareExecuteQuery("SELECT GROUP_CONCAT(`id`) AS `ids` FROM `inbound_container` WHERE `internal_number` LIKE ? || REPLACE(`internal_number`, ' ', '') LIKE ?"
            ,'ss',['%'.$searchterm.'%','%'.$searchterm.'%']);
        $containerIDs = mysqli_fetch_assoc($containerResult)['ids'];
        if (count(explode(",",$containerIDs))>0 && $containerIDs!="")
        {
            $prodResult = prepareExecuteQuery("SELECT GROUP_CONCAT(`product_id`) AS `ids` FROM `container_product` WHERE `container_id` IN ($containerIDs)");
            $prodIDs = mysqli_fetch_assoc($prodResult)['ids'];
            if (count(explode(",",$prodIDs))>0 && $prodIDs !="")
            {
                $resResult = prepareExecuteQuery("SELECT GROUP_CONCAT(`reservation_id`) AS `ids` FROM `reservation_product` WHERE `product_id` IN ($prodIDs)");
                $resProdIDs = explode(",",mysqli_fetch_assoc($resResult)['ids']);
            }
        }

        $x = "SELECT * FROM `reservation` WHERE (id = ? || id LIKE ?";

        if(count($customerIDs) > 0){
            $customerIDs = implode(',', $customerIDs);
            if ($customerIDs != "")$x .= " || customer_id IN ($customerIDs)";
        }
        if(count($resProdIDs) > 0){
            $resProdIDs = implode(',', $resProdIDs);
            if ($resProdIDs != "") $x .= " || `id` IN ($resProdIDs)";
        }
        $x .= ") AND `deleted` = 0 ORDER BY `id` DESC";
        $queryResult = prepareExecuteQuery($x,'ss',[$searchterm,'%'.$searchterm.'%']);
    }else{
        $customerIDs = implode(",",$usermodel->listViewableCustomers());
        $queryResult = prepareExecuteQuery("SELECT * FROM `reservation` where `customer_id` IN ($customerIDs) AND `deleted` = 0 ORDER BY `id` DESC LIMIT ?,?",'ii',[$toSkip, $limit]);
    }
    $count = mysqli_num_rows($queryResult);

    $newSkipCount = ($toSkip + $count);

    $totalRowsQueryResult = prepareExecuteQuery("SELECT count(id) as count FROM `reservation` where `deleted` = 0");
    $totalRowsData = mysqli_fetch_array($totalRowsQueryResult);
    $totalRowsInDatabase = $totalRowsData['count'];

    while($picksheet = mysqli_fetch_assoc($queryResult)){
        $customer_id = $picksheet['customer_id'];
        $date_purchased = date('d/m/Y', strtotime($picksheet['created_at']));
        $date_updated = date('d/m/Y', strtotime($picksheet['updated_at']));
    ?>
        <tr class="pages"><td align="center" class="pos">
            <a href="viewReservation.php?id=<?php echo $picksheet['id']; ?>" class="intake">
                <table width="100%" border="0">
                    <tr>
                        <td width="25%" align="left">ID: <?php echo $picksheet['id']; ?> </td>
                        <td align="left" style="font-size: 14px;">
                            <?php

                                $x1 = "SELECT * from `customers` WHERE id=?";
                                $y1 = prepareExecuteQuery($x1,'i',[$customer_id]);
                                $customer = mysqli_fetch_array($y1);
                                echo $customer['businessname'] . '  <span style="text-transform:lowercase;">t/a</span>  ' . $customer['tradingas'];

                                if($picksheet['deleted'] == 1 && $picksheet['completed'] == 0){
                                    echo "(VOID)";
                                }
                            ?>
                        </td>
                        <td width="25%" align="right"> Created <?php echo $date_purchased; ?>
                        <?php
                        if ($picksheet['processed'] == 0 && $canDelete) {
                        ?>
                        <div class="actions">
                            <a href="javascript:;" onclick="if(confirm('Are you sure you want to delete this?')){ doDelete(<?php echo $picksheet['id']; ?>); }" class="icon"><i class="fa fa-close" style="padding-right:4px;" aria-hidden="true"></i></a>
                        </div></td>
                        <?php } ?>
                    </tr>
                </table>
            </a>
 <?php
        $mailResult = prepareExecuteQuery("SELECT `status`,`secondary_code`,`addressee` FROM `mail_tracking` WHERE document_id = ? AND `type` = 'RESERVATION' AND `customer_id` = ? ORDER BY `id` DESC",'ii',[$picksheet['id'],$customer_id]);
        $style = "";
        $title = "";
        if (mysqli_num_rows($queryResult) > 0)
        {
            $mailData = null;
            while ($tmpD = mysqli_fetch_assoc($mailResult))
            {
                $mailData = $tmpD;
                if (strpos($tmpD['addressee'],"townandcountrymeats.co.uk") == -1) break;
            }
            if ($mailData['status']!=null)
            {
                $title = 'title="'.SLabsEmailerStatus::getTextStatus($mailData['status'],$mailData['secondary_code']).'"';
                $style = "background-color:".SLabsEmailerStatus::getTrafficStatus($mailData['status']);
            }
        }
?>
            <div class="sendcontainer" <?php echo 'style="'.$style.'" '.$title; ?>>
                <div class="active" picksheetid="<?php echo $picksheet['id']; ?>" <?php if($picksheet['sent'] == 0){ echo 'style="display:none;"'; }?>>
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
    function doDelete(id){
        $.post("ajax/deleteReservation.php", {'id':id}, results);
    }
    function results(){
        location.reload();
    }
</script>
