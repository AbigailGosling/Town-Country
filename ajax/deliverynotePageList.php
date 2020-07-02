<?php

	require('../functions.php');
	
	$term = $_POST['searchterm'];
    
    $x = "SELECT * FROM `customers` WHERE businessname LIKE '%$term%'";
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
		?><h2 style="color:#fff;font-size:12px;">No delivery notes found</h2><?php
	}else{
		
		while($row = mysqli_fetch_array($y)){
            $customer_id = $row['customer_id'];
					
            $date = $row['estimated_delivery_date'];
            
            $date=date_create($date);
            $date = date_format($date,"d/m/Y");
            
            $x2 = "SELECT * FROM `customers` WHERE id ='$customer_id'";
            $y2 = mysqli_query($conn, $x2);
            $row2 = mysqli_fetch_array($y2);
                
            ?>
            <tr><td align="center" class="pos">
            <a href="deliverynote.php?id=<?php echo $row['id']; ?>" class="intake" style="padding-left:10px;padding-right:10px;">
                <table width="100%" border="0">
                    <tr>
                        <td width="100" align="left">ID: 0000<?php echo $row['id']; ?></td>
                        <td align="center" style="font-size: 18px;"><?php echo $row2['businessname']; ?> 
                            <?php if($row['deliverynote_printed'] == 1){ ?>
                                <div class="printedLabel">Printed</div>
                            <?php } ?>
                        </td>

                        <td width="100" align="right"><?php echo $row['estimated_delivery_date']; ?></td>
                    </tr>
                </table>
            </a>
            </td></tr>
            <?php
        }
    }
?>