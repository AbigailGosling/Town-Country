<?php
    require('../../functions.php');
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
    require('../../scripts/SLabsEmailer.php');
    use InternalScripts\SLabsEmailerStatus;
    $toSkip = $_POST['toSkip'];
    $limit = 80;

    $queryResult = mysqli_query($conn, "SELECT * FROM `pickerSheets` ORDER BY date DESC LIMIT $toSkip, $limit") or die(mysqli_error($conn));

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
        $mailResult = mysqli_query($conn, "SELECT `status` FROM `mail_tracking` WHERE document_id = ".$picksheet['id']." AND `type` = 'SALES_CONFIRMATION' AND `customer_id` = $customer_id ORDER BY `id` DESC limit 1");
        $style = ""; 
        if (mysqli_num_rows($queryResult) > 0)
        {
            $mailData = mysqli_fetch_assoc($mailResult);
            if ($mailData['status']!=null) $style = "background-color:".SLabsEmailerStatus::getTrafficStatus($mailData['status']);
        }
         

?>
            

            <div class="sendcontainer" <?php echo 'style="'.$style.'"'; ?>>
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