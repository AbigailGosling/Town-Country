<?php

	require('../functions.php');
	
	$month = $_POST['month'];
	$year = $_POST['year'];
	
	$month = str_pad($month, 2, '0', STR_PAD_LEFT);
	
	$startDate = $year . '-' . $month . '-01';
	$endDate = $year . '-' . $month . '-31';
	
	$pickersheetResults = mysqli_query($conn, "SELECT * FROM `pickerSheets` WHERE date BETWEEN '$startDate' AND '$endDate' ORDER BY date DESC") or die(mysqli_error($conn));
	
	$count = mysqli_num_rows($pickersheetResults);
	
	if($count == 0){
		?><h2 style="color:#fff;font-size:12px;">No sales sheets found</h2><?php
	}else{
        
        $page_limit = 50;
        $num_of_pages = 1;
        $entry_count = 0;
        while($picksheet = mysqli_fetch_array($pickersheetResults)){
            $entry_count++;
            if($entry_count == $page_limit){
                $entry_count = 0;
                $num_of_pages++;
            }

            $date_purchased = date('d/m/Y', strtotime($picksheet['date']));
        ?>
            <tr class="pages page<?php echo $num_of_pages; ?>"><td align="center" class="pos">
                <a href="viewSalesconfirmation.php?id=<?php echo $picksheet['id']; ?>" class="intake">
                    <table width="100%" border="0">
                        <tr>
                            <td width="25%" align="left">ID: P-00<?php echo $picksheet['id']; ?> </td>
                            <td align="left" style="font-size: 14px;">
                                <?php
                                
                                    $customer_id = $picksheet['customer_id'];
                                    $cusResult = mysqli_query($conn, "SELECT * from `customers` WHERE id='$customer_id'");
                                    $customer = mysqli_fetch_array($cusResult);
                                
                                ?>
                                <?php echo $customer['businessname'] . '  <span style="text-transform:lowercase;">t/a</span>  ' . $customer['tradingas']; ?>

                                <?php if($picksheet['deleted'] == 1 && $picksheet['completed'] == 0){ echo "(VOID)"; } ?>
                            </td>
                            <td width="25%" align="right"> created <?php echo $date_purchased; ?></td>
                        </tr>
                    </table>
                </a>
    
                <div class="sendcontainer">
                    <div class="active" picksheetid="<?php echo $picksheet['id']; ?>" <?php if($picksheet['sent'] == 0){ echo 'style="display:none;"'; }?>>
                        <i class="fa fa-check" aria-hidden="true"></i>
                    </div>
                </div>
    
            </td></tr>
            <?php
        }
	}
?>
<tr>
    <td>
    <div class="pages_container">
        <div class="flex" style="align-items:center;justify-content:flex-end;">
            <p style="color:#fff;padding-right:10px;font-weight:bold">Jump to page</p>
            <?php $num_of_pages_temp = $num_of_pages+1; ?>
            <select style="width:60px;height:30px;" onchange="changePage(this)">
                <?php for($i=1;$i<($num_of_pages_temp); $i++){ ?>
                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                <?php } ?>
            </select>
        </div>
    </div>
    </td>
</tr>

<script>
    total_pages = <?php echo $num_of_pages; ?>;
</script>