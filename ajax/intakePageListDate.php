<?php
	require('../functions.php');
	
	$month = $_POST['month'];
	$year = $_POST['year'];
	
	$month = str_pad($month, 2, '0', STR_PAD_LEFT);
	
	$startDate = $year . '-' . $month . '-01';
	$endDate = $year . '-' . $month . '-31';
		
	$searchResults = mysqli_query($conn, "SELECT * FROM `intake` WHERE date_received BETWEEN '$startDate' AND '$endDate' ORDER BY date_received DESC");
	
	$countResults = mysqli_num_rows($searchResults);
	
	if($countResults == 0){
		?><h2 style="color:#fff;font-size:12px;">No intakes found</h2><?php
	}else{
		
		while($intake = mysqli_fetch_array($searchResults)){
			$date_received = date('d/m/Y', strtotime($intake['date_received']));
		?>
			<tr><td align="center" class="pos">
				<a href="intake.php?id=<?php echo $intake['id']; ?>" class="intake">
					<table width="100%" border="0">
						<tr>
							<td width="100" align="left">ID: 0000<?php echo $intake['id']; ?></td>
                            <td align="center" style="font-size: 18px;">
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
                                $r = intakePriceComplete($intake['id']);    
                                if($r == 1){
                                ?><i class="fa fa-check" aria-hidden="true" style="margin-left:10px;"></i><?php
                                }
                                if($intake['returned'] == '1'){ echo ' <small class="return-highlight">return entry</small>'; }
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
	}
?>