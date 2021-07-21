<?php
    require('../../functions.php');
    
    $toSkip = $_POST['toSkip'];
    $limit = 80;

    $queryResult = mysqli_query($conn, "SELECT * FROM `intake` ORDER BY date_received DESC, id DESC LIMIT $toSkip, $limit");
    $count = mysqli_num_rows($queryResult);

    $newSkipCount = ($toSkip + $count);

    $totalRowsQueryResult = mysqli_query($conn, "SELECT count(id) as count FROM `intake`");
    $totalRowsData = mysqli_fetch_array($totalRowsQueryResult);
    $totalRowsInDatabase = $totalRowsData['count'];

    while($intake = mysqli_fetch_array($queryResult)){
        $date_received = date('d/m/Y', strtotime($intake['date_received']));
        ?>
        <tr><td align="center" class="pos">

            <a href="intake.php?id=<?php echo $intake['id']; ?>" class="intake">
                <table width="100%" border="0">
                <tr>
                    <?php
                        $productCountNotCosted = productCountOnIntakeNotCosted($intake['id']);    
                    ?>
                    <td width="30%" align="left">
                        ID: I-0000<?php echo $intake['id'];?></td>
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

                            if($productCountNotCosted == 0){
                            ?><i class="fa fa-check" aria-hidden="true" style="margin-left:10px;"></i><?php
                            }
                        ?>
                    </td>
                    <td width="30%" align="right"><?php echo $date_received; ?></td>
                </tr>
                </table>
            </a>
            <a href="javascript:;" onclick="deleteRow('<?php echo $intake['id'];?>')" id="delete_intake"><i class="fa fa-times" aria-hidden="true"></i></a>
        </td></tr>
        <?php
    }
?>

<script>
    $('#toSkipCount').val(<?php echo $newSkipCount; ?>);
    $('#totalRowsCount').val(<?php echo $totalRowsInDatabase; ?>);
</script>