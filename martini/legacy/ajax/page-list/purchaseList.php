<?php
    require(__DIR__.'/../../functions.php');
    
    $toSkip = request('toSkip');
    $limit = 80;


    $queryResult = prepareExecuteQuery("SELECT * FROM `purchase_form` ORDER BY date_due DESC LIMIT ?, ?",'ii',[$toSkip,$limit]);
    $count = mysqli_num_rows($queryResult);

    $newSkipCount = ($toSkip + $count);

    $totalRowsQueryResult = prepareExecuteQuery("SELECT count(id) as count FROM `intake`");
    $totalRowsData = mysqli_fetch_array($totalRowsQueryResult);
    $totalRowsInDatabase = $totalRowsData['count'];

    while($row = mysqli_fetch_array($queryResult)){
        
        $id = $row['id'];
        
        $x1 = "SELECT * FROM `intake` WHERE purchase_id=?";
        $y1 = prepareExecuteQuery($x1,'i',[$id]);
        $intake = mysqli_fetch_array($y1);
        $intakeCount = mysqli_num_rows($y1);
    
            
        $date_purchased = date('d/m/Y', strtotime($row['date_due']));
        ?>
        <tr class="pages"><td align="center" class="pos">
            <a href="createPurchase.php?id=<?php echo $row['id']; ?>" class="intake">
                <table width="100%" border="0">
                    <tr>
                        <td width="35%" align="left">ID: <?php echo $row['id']; ?> </td>
                        <td width="60%" align="left" style="font-size: 16px;">
                            <?php if($row['direct_drop'] == 1){ echo '<span style="font-size:12px;">[direct drop]</span>'; } ?>
                            <?php echo supplierName($row['supplier_id']); ?>
                            <?php if($row['booking_ref_number'] == ''){ ?><span style="color:red;padding-left:5px;font-size:26px;font-weight:700">!</span><?php } ?>
                            
                            <?php
                                $thisid = $row['id'];
                                
                                $x2 = "SELECT * FROM `intake` WHERE purchase_id=?";
                                $y2 = prepareExecuteQuery($x2,'i',[$thisid]);
                                $count22 = mysqli_num_rows($y2);
                                
                                if($intakeCount != 0){
                                ?> <div class="printedLabel">Intake Created</div> <?php
                                }else{
                                ?>  <?php
                                }
                            ?>
                        </td>
                        <td width="35%" align="right"><?php echo $date_purchased; ?></td>
                        <td width="10%" align="right">
                            <i class="peek-products fa fa-product-hunt" aria-hidden="true"></i>
                            <div class="tooltip-content">
                                <?php
                                    $species = explode('|', $row['species']);
                                    $cuts = explode('|', $row['cut']);
                                    $units = explode('|', $row['units']);
                                    $prices = explode('|', $row['price']);
                                    
                                    $size = sizeof($species);
                                ?>
                                <table>
                                    <tr>
                                        <th>Species</th>
                                        <th>Cuts</th>
                                        <th>Units</th>
                                        <th>Prices</th>
                                    </tr>
                                    <?php for($i=0; $i < $size; $i++){ ?>
                                        <tr>
                                            <td><?php echo $species[$i]; ?></td>
                                            <td><?php echo $cuts[$i]; ?></td>
                                            <td><?php echo $units[$i]; ?></td>
                                            <td><?php echo $prices[$i]; ?></td>
                                        </tr>
                                        <?php } ?>
                                </table>
                            </div>
                        </td>
                    </tr>
                </table>
            </a>
            
                
            <a href="javascript:;" onclick="deleteRow('<?php echo $row['id'];?>')" id="delete_intake"><i class="fa fa-times" aria-hidden="true"></i></a>

        </td></tr>
        <?php
    }
?>
<script>
    $('#toSkipCount').val(<?php echo $newSkipCount; ?>);
    $('#totalRowsCount').val(<?php echo $totalRowsInDatabase; ?>);
</script>