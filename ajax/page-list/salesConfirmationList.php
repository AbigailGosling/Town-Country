<?php
    require('../../functions.php');
    require('../../scripts/SLabsEmailer.php');
    use InternalScripts\SLabsEmailerStatus;
    $toSkip = $_POST['toSkip'];
    $limit = 80;
    $searchterm = $_POST['searchterm'];
	
	if($searchterm != ''){      
        # Check if any customer names match the search input
        $customerIDs = [];
        $customerResult = mysqli_query($conn, "SELECT * FROM `customers` WHERE businessname LIKE '$searchterm%' || businessname LIKE '%$searchterm%' || businessname LIKE '$searchterm%' || REPLACE(businessname, ' ', '') LIKE '$searchterm%' || REPLACE(businessname, ' ', '') = '$searchterm'");
        while($customer = mysqli_fetch_array($customerResult)){ array_push($customerIDs, $customer['id']); }

        $x = "SELECT * FROM `pickerSheets` WHERE id = '$searchterm' || id LIKE '$searchterm%'";

        if(count($customerIDs) > 0){
            $customerIDs = implode(',', $customerIDs);
            $x .= " || customer_id IN ($customerIDs)";
        }
        $x .= " ORDER BY date DESC";
        $queryResult = mysqli_query($conn, $x);
    }else{
        $queryResult = mysqli_query($conn, "SELECT * FROM `pickerSheets` ORDER BY date DESC LIMIT $toSkip, $limit");
    }
    $count = mysqli_num_rows($queryResult);
    
    $newSkipCount = ($toSkip + $count);

    $totalRowsQueryResult = mysqli_query($conn, "SELECT count(id) as count FROM `pickerSheets`");
    $totalRowsData = mysqli_fetch_array($totalRowsQueryResult);
    $totalRowsInDatabase = $totalRowsData['count'];

    while($picksheet = mysqli_fetch_array($queryResult)){
        
        $date_purchased = date('d/m/Y', strtotime($picksheet['date']));
    ?>
        <tr class="pages"><td align="center" class="pos">
            <a href="viewSalesconfirmation.php?id=<?php echo $picksheet['id']; ?>" class="intake">
                <table width="100%" border="0">
                    <tr>
                        <td width="25%" align="left">ID: P-00<?php echo $picksheet['id']; ?> </td>
                        <td align="left" style="font-size: 14px;">
                            <?php
                            
                                $customer_id = $picksheet['customer_id'];
                                $x1 = "SELECT * from `customers` WHERE id='$customer_id'";
                                $y1 = mysqli_query($conn, $x1);
                                
                                $customer = mysqli_fetch_array($y1);
                            
                            ?>
                            <?php echo $customer['businessname'] . '  <span style="text-transform:lowercase;">t/a</span>  ' . $customer['tradingas']; ?>

                            <?php
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
        $mailResult = mysqli_query($conn, "SELECT `status`,`secondary_code`,`addressee` FROM `mail_tracking` WHERE document_id = ".$picksheet['id']." AND `type` = 'SALES_CONFIRMATION' AND `customer_id` = $customer_id ORDER BY `id` DESC");
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