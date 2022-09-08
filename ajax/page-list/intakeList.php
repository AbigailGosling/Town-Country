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
    $ucheck = mysqli_query($conn, "SELECT `user_type` FROM `users` WHERE `id` = $userid");
    $ucheck = mysqli_fetch_assoc($ucheck);
    while($intake = mysqli_fetch_array($queryResult)){
        $date_received = date('d/m/Y', strtotime($intake['date_received']));
        ?>
        <tr>
        <?php
        if ($ucheck['user_type'] == "A")
        {
            $date_paid_name = "date_paid_".$intake['id'];
        ?>
        <td align="left" class="pos">
            <table width="100%" border="0" style="margin-top: 8px;">
                <tr>
                    <td width="10%" align="left">
                        <input class="datepicker" name="<?php echo $date_paid_name; ?>" id="<?php echo $date_paid_name; ?>" placeholder="..." style="height:42px;width:75px;"></input>
                        <script>
                            var date_paid_name ="#<?php echo $date_paid_name; ?>";
                            var date_paid_enabled = <?php if ($intake['date_paid'] != null && $intake['date_paid'] != ""){ echo "false";}else{ echo "true";}?>;
                            $(date_paid_name).datepicker({onSelect:date_paid_changed,dateFormat: 'dd/mm/yy'});
                            $(date_paid_name).val("<?php if ($intake['date_paid'] != null && $intake['date_paid'] != "")echo date('d/m/Y', strtotime($intake['date_paid'])); ?>");
                            if (date_paid_enabled == false)
                            {
                                $(date_paid_name).datepicker('disable');
                            }
                        </script>
                    </td>
                </tr>
            </table>
        </td>
        <?php
        }
        ?>
        <td align="center" class="pos">

            <a href="intake.php?id=<?php echo $intake['id']; ?>" class="intake">
                <table width="100%" border="0">
                <tr>
                    <?php
                        $productCountNotCosted = productCountOnIntakeNotCosted($intake['id']);    
                    ?>
                    <td width="10%" align="left">
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
                            if($productCountNotCosted == 0){
                            ?><i class="fa fa-check" aria-hidden="true" style="margin-left:10px;"></i><?php
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
?>

<script>
    $('#toSkipCount').val(<?php echo $newSkipCount; ?>);
    $('#totalRowsCount').val(<?php echo $totalRowsInDatabase; ?>);
</script>