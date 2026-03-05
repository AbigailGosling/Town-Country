<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

	require(__DIR__.'/../../functions.php');
    $toSkip = request()->input('toSkip', 0);
    $limit = 80;
	$term = request()->input('searchterm');
    $addDateFilter = (request()->has('month') && request()->has('year'))?true:false;
    if ($addDateFilter){
        $month = request()->input('month');
        $year = request()->input('year');

        $month = str_pad($month, 2, '0', STR_PAD_LEFT);

        $startDate = $year . '-' . $month . '-01';
        $endDate = $year . '-' . $month . '-31';
    }
	$showDeleted = (request()->has("showDeleted") && request()->input('showDeleted') == 1)?"":"`deleted` = 0 AND";
    $searchResults = null;

    if($term != ''){
        $SUPPLIER_CUSTOMER_IDS = array(0);

        // Check for search matching suppliers
        $suppliersResult = prepareExecuteQuery("SELECT id FROM `supplier` WHERE `name` LIKE ? || `name` = ?",'ss',['%'.$term.'%',$term]);
        while($supplier = mysqli_fetch_array($suppliersResult)){ $SUPPLIER_CUSTOMER_IDS[]=$supplier['id']; }


        // Check for search matching customers
        $usermodel = User::find(Auth::id());
        $customersResult = prepareExecuteQuery("SELECT id FROM `customers` WHERE `businessname` LIKE ? || `businessname` = ?",'ss',['%'.$term.'%',$term]);
        while($customer = mysqli_fetch_array($customersResult)){ if (!$usermodel->canViewCustomer($customer['id'])) continue;$SUPPLIER_CUSTOMER_IDS[]=$customer['id']; }

        $SUPPLIER_CUSTOMER_IDS = implode(',', $SUPPLIER_CUSTOMER_IDS);

        $palletQuery = prepareExecuteQuery("SELECT intake_id FROM `pallet` WHERE id = ?",'i',[$term]);
        $intakeIDs = array(0);
        while($pallet = mysqli_fetch_array($palletQuery)){ array_push($intakeIDs, $pallet['intake_id']); }
        $intakeIDs = implode(',', $intakeIDs);

        $dateFilter = $addDateFilter ? "date_received BETWEEN ? AND ? AND " : "";
        $dateParams = $addDateFilter ? [$startDate, $endDate] : [];
        $orderBy = "ORDER BY `date_received` DESC, `id` DESC";

        if (validateDate($term)) {
            $date = str_replace('/', '-', $term);
            $termDate = date('Y-m-d', strtotime($date));
            $query = "SELECT * FROM `intake` WHERE $showDeleted {$dateFilter}`date_received` LIKE ? $orderBy";
            $params = array_merge($dateParams, ['%'.$termDate.'%']);
            $types = ($addDateFilter ? 'ss' : 's') . 's';
        }else{
            $query = "SELECT * FROM `intake` WHERE $showDeleted {$dateFilter}(id=? OR `import_num` LIKE ? OR `internal_num` LIKE ? OR `vehicle_reg` LIKE ? OR `id` LIKE ? OR `delivery_note_number` LIKE ? OR ((`supplier_id` <> '') && `supplier_id` IN ($SUPPLIER_CUSTOMER_IDS)) OR (id IN ($intakeIDs))) $orderBy";
            $params = array_merge($dateParams, [$term,$term.'%',$term.'%',$term.'%',$term.'%',$term.'%']);
            $types = ($addDateFilter ? 'ss' : '') . 'sssssss';
        }
    }else{
        if ($addDateFilter){
            $query = "SELECT * FROM `intake` WHERE $showDeleted date_received BETWEEN ? AND ? ORDER BY date_received DESC, id";
            $params = [$startDate, $endDate];
            $types = 'ss';
        }else{
            $query = "SELECT * FROM `intake` WHERE $showDeleted 1=1 ORDER BY date_received DESC, id DESC LIMIT ?, ?";
            $params = [$toSkip, $limit];
            $types = 'ii';
        }
    }
    $searchResults = prepareExecuteQuery($query, $types, $params);
    $countResults = mysqli_num_rows($searchResults);
    $newSkipCount = ($toSkip + $countResults);
    $totalRowsQueryResult = prepareExecuteQuery("SELECT count(`id`) as `count` FROM `intake`");
    $totalRowsData = mysqli_fetch_array($totalRowsQueryResult);
    $totalRowsInDatabase = $totalRowsData['count'];
    if($countResults == 0){
        ?><h2 style="color:#fff;font-size:12px;">No intakes found</h2><?php
    }else{

        while($intake = mysqli_fetch_array($searchResults)){
            $date_received = date('d/m/Y', strtotime($intake['date_received']));
        ?>
            <tr><td align="center" class="pos">
                <a href="intake.php?id=<?php echo $intake['id']; ?>" class="intake">
                    <table width="100%" border="0">
                    <tr>
                        <?php
                            $productCountNotCosted = productCountOnIntakeNotCosted($intake['id']);
                            $docs = explode(",", prepareExecuteQuery("SELECT GROUP_CONCAT(`type_id`) as `type_ids` FROM `intakedocs` WHERE `type_id` IN (1,2,4) AND `intakeid` = ?",'i',[$intake['id']])->fetch_assoc()['type_ids']) ?? [];
                            $hasInvoice = in_array(1, $docs);
                            $hasDN = in_array(2, $docs);
                            $hasCMR = in_array(4, $docs);
                        ?>
                        <td width="30%" align="left">
                            ID: I-<?php echo $intake['id'];?></td>
                        <td align="left" style="font-size: 18px;" class="<?php if($r == 1){ echo 'flex space-between v-center'; } ?>">
                            <?php

                                if($intake['returned'] == '1'){
                                    $cusDetails =  getCustomer($intake['supplier_id']);
                                    if(!empty($cusDetails) && isset($cusDetails['businessname'])){
                                        echo $cusDetails['businessname'];
                                    }else{
                                        echo 'No Customer Data';
                                    }

                                }else{
                                    echo supplierName($intake['supplier_id']);
                                }
                                if($intake['returned'] == '1'){ echo ' <small class="return-highlight">return entry</small>'; }

                            ?>
                        </td>
                        <td align="right">
                            <?php
                                if (strlen($intake['notes']) < 20) {
                                    echo $intake['notes'];
                                }
                                else {
                                    echo substr($intake['notes'],0,20-3)."...";
                                }
                            ?>
                        </td>
                        <td width="30">
                            <?php
                                if($hasInvoice){
                                ?><i class="fa fa-paperclip" aria-hidden="true" style="margin-left:10px;"></i><?php
                                }
                            ?>
                        </td>
                        <td width="30">
                            <?php
                                if($hasDN){
                                ?><i class="fa fa-truck" aria-hidden="true" style="margin-left:10px;"></i><?php
                                }
                            ?>
                        </td>
                        <td width="30">
                            <?php
                                if($hasCMR){
                                ?><i class="fa fa-globe" aria-hidden="true" style="margin-left:10px;"></i><?php
                                }
                            ?>
                        </td>
                        <td width="30">
                            <?php
                                if($productCountNotCosted == 0){
                                ?><i class="fa fa-sterling-sign" aria-hidden="true" style="margin-left:10px;"></i><?php
                                }
                            ?>
                        </td>
                        <td width="100" align="right"><?php echo $date_received; ?></td>
                    </tr>
                    </table>
                </a>

                <a href="javascript:;" onclick="deleteRow('<?php echo $intake['id'];?>')" id="delete_intake"><i class="fa fa-times" aria-hidden="true"></i></a>
            </td></tr>
        <?php
        }
    }
	function validateDate($date, $format = 'd/m/Y')
	{
		$d = DateTime::createFromFormat($format, $date);
		return $d && $d->format($format) === $date;
	}
?>

<script>
    $('#toSkipCount').val(<?php echo $newSkipCount; ?>);
    $('#totalRowsCount').val(<?php echo $totalRowsInDatabase; ?>);
</script>
