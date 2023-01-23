<?php
    require(__DIR__.'/../../functions.php');

    $toSkip = request('toSkip');
    $limit = 80;

    session_start();
    $userid = $_SESSION['USER'];
    
    $queryResult = prepareExecuteQuery("SELECT * FROM `pickerSheets` WHERE completed='1' ORDER BY `id` DESC LIMIT $toSkip, $limit");
    $count = mysqli_num_rows($queryResult);
    $newSkipCount = ($toSkip + $count);

    $totalRowsQueryResult = prepareExecuteQuery("SELECT count(id) as count FROM `pickerSheets` WHERE completed='1'");
    $totalRowsData = mysqli_fetch_array($totalRowsQueryResult);
    $totalRowsInDatabase = $totalRowsData['count'];

    while($row = mysqli_fetch_array($queryResult)){
        
        $customer_id = $row['customer_id'];
        
        $date = $row['estimated_delivery_date'];
       
        $date=DateTime::createFromFormat('d/m/Y',$date);     
        $date = date_format($date,"d/m/Y");
        
        $x2 = "SELECT * FROM `customers` WHERE id =?";
        $y2 = prepareExecuteQuery($x2,'i',[$customer_id]);
        $row2 = mysqli_fetch_array($y2);
        
    ?>
    <tr class="pages"><td align="center" class="pos">
    <a href="deliverynote.php?id=<?php echo $row['id']; ?>" class="intake" style="padding-left:10px;padding-right:10px;">
        <table width="100%" border="0">
            <tr>
                <td width="25%" align="left">ID: <?php echo $row['id']; ?></td>
                <td align="left" style="font-size: 18px;"><?php echo $row2['businessname']; ?></td>

                <td width="25%" align="right"><?php if($row['deliverynote_printed'] == 1){ ?>
                        <div class="printedLabel">Printed</div>
                    <?php } ?><?php echo $row['estimated_delivery_date']; ?></td>
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