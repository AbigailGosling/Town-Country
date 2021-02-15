<?php

	require('../functions.php');
	
	$term = $_POST['searchterm'];
    
    $x = "SELECT * FROM `customers` WHERE businessname LIKE '$term%' || businessname LIKE '%$term%' || businessname LIKE '$term%'";
    $y = mysqli_query($conn, $x);
    
    $customerids = '';
    
    while($row = mysqli_fetch_array($y)){
        $rowid = $row['id'];
        $customerids .= " OR completed='1' && customer_id='$rowid'";
    }
    
    $x = "SELECT * FROM `pickerSheets` WHERE completed='1' && id='" . $term . "' OR completed='1' && id LIKE '%$term%' $customerids  ORDER BY `id` DESC";
    
	$y = mysqli_query($conn, $x) or die(mysqli_error($conn));
    $count = mysqli_num_rows($y);
	
	if($count == 0){
		?><h2 style="color:#fff;font-size:12px;">No invoices found</h2><?php
	}else{
        
        $page_limit = 50;
        $num_of_pages = 1;
        $entry_count = 0;
		while($row = mysqli_fetch_array($y)){
            $entry_count++;
            if($entry_count == $page_limit){
                $entry_count = 0;
                $num_of_pages++;
            }

            $customer_id = $row['customer_id'];
					
            $date = $row['estimated_delivery_date'];
            
            $date=date_create($date);
            $date = date_format($date,"d/m/Y");
            
            $x2 = "SELECT * FROM `customers` WHERE id ='$customer_id'";
            $y2 = mysqli_query($conn, $x2);
            $row2 = mysqli_fetch_array($y2);
            
        ?>
        <tr class="pages page<?php echo $num_of_pages; ?>"><td align="center" class="pos">
        <a href="invoice.php?id=<?php echo $row['id']; ?>" class="intake" style="padding-left:10px;padding-right:10px;">
            <table width="100%" border="0">
                <tr>
                    <td width="100" align="left">ID: 0000<?php echo $row['id']; ?></td>
                    <td align="center" style="font-size: 18px;"><?php echo $row2['businessname']; ?></td>
                    <td width="100" align="right"><?php echo $row['estimated_delivery_date']; ?></td>
                </tr>
            </table>
        </a>
        <div class="sendcontainer">
                    <div class="active" picksheetid="<?php echo $row['id']; ?>" <?php if($row['invoicesent'] == 0){ echo 'style="display:none;"'; }?>>
                        <i class="fa fa-check" aria-hidden="true"></i>
                    </div>
                </div>
        </td></tr>
        <?php
        }
    }
?>
<tr>
    <td><br/><br/>
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