<?php
    require(__DIR__.'/../../functions.php');
    require(__DIR__.'/../../scripts/SLabsEmailer.php');

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use InternalScripts\SLabsEmailerStatus;
    $toSkip = request()->input('toSkip');
    $limit = 80;
    $searchterm = request()->input('searchterm');
	$usermodel = User::find(Auth::id());
	if($searchterm != ''){
        # Check if any customer names match the search input
        $customerIDs = [];
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
        $x = "SELECT * FROM `pickerSheets` WHERE id = ? || id LIKE ?";

        if(count($customerIDs) > 0){
            $customerIDs = implode(',', $customerIDs);
            $x .= " || customer_id IN ($customerIDs)";
        }
        $x .= " ORDER BY date DESC";
        $queryResult = prepareExecuteQuery($x,'ss',[$searchterm,'%'.$searchterm.'%']);
    }else{
        if (!$usermodel->hasPermission("view_all_sale_confirmations"))
            $queryResult = prepareExecuteQuery("SELECT * FROM `pickerSheets` WHERE (customer_id IN (".implode(",",$usermodel->listViewableCustomers()).") AND is_return_to_supplier = 0) OR is_return_to_supplier = 1 ORDER BY date DESC LIMIT ?,?",'ii',[$toSkip, $limit]);
        else
            $queryResult = prepareExecuteQuery("SELECT * FROM `pickerSheets` ORDER BY date DESC LIMIT ?,?",'ii',[$toSkip, $limit]);
    }
    $count = mysqli_num_rows($queryResult);

    $newSkipCount = ($toSkip + $count);

    $totalRowsQueryResult = prepareExecuteQuery("SELECT count(id) as count FROM `pickerSheets`");
    $totalRowsData = mysqli_fetch_array($totalRowsQueryResult);
    $totalRowsInDatabase = $totalRowsData['count'];

    while($picksheet = mysqli_fetch_assoc($queryResult)){
        $customer_id = $picksheet['customer_id'];
        $date_purchased = date('d/m/Y', strtotime($picksheet['date']));
    ?>
        <tr class="pages"><td align="center" class="pos">
            <?php
            if ($picksheet['is_return_to_supplier']==0)
            {
            ?>
            <a href="viewSalesconfirmation.php?id=<?php echo $picksheet['id']; ?>" class="intake">
            <?php } else { ?>
            <a href="viewSupplierreturn.php?id=<?php echo $picksheet['id']; ?>" class="intake">
            <?php } ?>
                <table width="100%" border="0">
                    <tr>
                        <td width="25%" align="left">ID: <?php echo $picksheet['id']; ?> </td>
                        <td align="left" style="font-size: 14px;">
                            <?php

                                if ($picksheet['is_return_to_supplier']==0)
                                {
                                    $x1 = "SELECT * from `customers` WHERE id=?";
                                    $y1 = prepareExecuteQuery($x1,'i',[$customer_id]);
                                    $customer = mysqli_fetch_array($y1);
                                    echo $customer['businessname'] . '  <span style="text-transform:lowercase;">t/a</span>  ' . $customer['tradingas'];
                                }
                                else
                                {
                                    $x1 = "SELECT * from `supplier` WHERE id=?";
                                    $y1 = prepareExecuteQuery($x1,'i',[$customer_id]);
                                    $customer = mysqli_fetch_array($y1);
                                    echo $customer['name'];
                                }

                                if($picksheet['deleted'] == 1 && $picksheet['completed'] == 0){
                                    echo "(VOID)";
                                    if($picksheet['deleted_by_user_id'] != ''){
                                        echo " - " . getUsername($picksheet['deleted_by_user_id']);
                                    }
                                }
                            ?>
                        </td>
                        <td width="25%" align="right"> created <?php echo $date_purchased; ?></td>
                    </tr>
                </table>
            </a>
 <?php
        $mailResult = prepareExecuteQuery("SELECT `status`,`secondary_code`,`addressee` FROM `mail_tracking` WHERE document_id = ? AND `type` = 'SALES_CONFIRMATION' AND `customer_id` = ? ORDER BY `id` DESC",'ii',[$picksheet['id'],$customer_id]);
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
</script>
