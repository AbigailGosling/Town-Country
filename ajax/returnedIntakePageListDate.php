<?php

	require('../functions.php');
	
	$month = $_POST['month'];
	$year = $_POST['year'];
	
	$month = str_pad($month, 2, '0', STR_PAD_LEFT);
	
	$startDate = $year . '-' . $month . '-01';
	$endDate = $year . '-' . $month . '-31';
		
	$x = "SELECT * FROM `intake` WHERE date_received BETWEEN '$startDate' AND '$endDate' AND returned=1 ORDER BY date_received DESC";
		
	$y = mysqli_query($conn, $x) or die(mysqli_error($conn));
	
	$count = mysqli_num_rows($y);
	
	if($count == 0){
		?><h2 style="color:#fff;font-size:12px;">No intakes found</h2><?php
	}else{
		
		while($row = mysqli_fetch_array($y)){
			$date_received = date('d/m/Y', strtotime($row['date_received']));
		?>
			<tr><td align="center" class="pos">
				<a href="intake.php?id=<?php echo $row['id']; ?>" class="intake">
					<table width="100%" border="0">
						<tr>
                            <?php
                                $customer = getCustomer($row['supplier_id']);
                            ?>
							<td width="100" align="left">ID: I-0000<?php echo $row['id']; ?></td>
                            <td align="center" style="font-size: 18px;">
                            <?php
                                echo $customer['businessname'];
                                $r = intakePriceComplete($row['id']);    
                                if($r == 1){
                                ?><i class="fa fa-check" aria-hidden="true" style="margin-left:10px;"></i><?php
                                }
                            ?>
                            </td>
							<td width="100" align="right"><?php echo $date_received; ?></td>
						</tr>
					</table>
				</a>
				
				<a href="javascript:;" onclick="deleteRow('<?php echo $row['id'];?>')" id="delete_intake"><i class="fa fa-times" aria-hidden="true"></i></a>
			</td></tr>
		<?php
		}
	}
?>