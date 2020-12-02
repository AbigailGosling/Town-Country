<?php

	require('../functions.php');
	
	$term = $_POST['searchterm'];
	
	if($term != ''){ ?>
		<?php
		
		$x = "SELECT * FROM `supplier` WHERE name LIKE '%$term%'";
		$y = mysqli_query($conn, $x);
		
		$supplierids = '';
		
		while($row = mysqli_fetch_array($y)){
			$rowid = $row['id'];
			$supplierids .= " OR supplier_id='$rowid'";
		}
		
		
		if (validateDate($term)) {
			$date = str_replace('/', '-', $term);
			$termDate = date('Y-m-d', strtotime($date));
			$x = "SELECT * FROM `purchase_form` WHERE date_due LIKE '%$termDate%' ORDER BY date_due DESC";
		}else{
			$x = "SELECT * FROM `purchase_form` WHERE id='" . $term . "' OR id LIKE '%$term%' $supplierids  ORDER BY date_due DESC";
		}
		
		$y = mysqli_query($conn, $x) or die(mysqli_error($conn));
		$count = mysqli_num_rows($y);
	
		if($count == 0){
			?><h2 style="color:#fff;font-size:12px;">Nothing found</h2><?php
		}else{
			
			while($row = mysqli_fetch_array($y)){
				$date_due = date('d/m/Y', strtotime($row['date_due']));
			?>
				<tr><td align="center" class="pos">
					<a href="createPurchase.php?id=<?php echo $row['id']; ?>" class="intake">
						<table width="100%" border="0">
							<tr>
								<td width="100" align="left">ID: 0000<?php echo $row['id']; ?></td>
								<td align="center" style="font-size: 16px;">
									<?php if($row['direct_drop'] == 1){ echo '<span style="font-size:12px;">[direct drop]</span>'; } ?>
										<?php echo supplierName($row['supplier_id']); ?>
										<?php if($row['booking_ref_number'] == ''){ ?><span style="color:red;padding-left:5px;font-size:26px;font-weight:700">!</span><?php } ?>
										
										<?php
											$thisid = $row['id'];
											
											$x2 = "SELECT * FROM `intake` WHERE purchase_id='$thisid'";
											$y2 = mysqli_query($conn, $x2);
											$count22 = mysqli_num_rows($y2);
											
											if($intakeCount != 0){
											?> <div class="printedLabel">Intake Created</div> <?php
											}else{
											?>  <?php
											}
									?>
								</td>
								<td width="150" align="right">Created <?php echo $date_due; ?></td>
							</tr>
						</table>
					</a>
					
					<a href="javascript:;" onclick="deleteRow('<?php echo $row['id'];?>')" id="delete_intake"><i class="fa fa-times" aria-hidden="true"></i></a>
				</td></tr>
			<?php
			}
		}
	 }else{ ?>
		<?php
		
		$x = "SELECT * FROM `purchase_form` ORDER BY date_due DESC";;
		$y = mysqli_query($conn, $x) or die(mysqli_error($conn));
		while($row = mysqli_fetch_array($y)){
		    $date_due = date('d/m/Y', strtotime($row['date_due']));
		?>
			<tr><td align="center" class="pos">
				<a href="createPurchase.php?id=<?php echo $row['id']; ?>" class="intake">
					<table width="100%" border="0">
						<tr>
							<td width="100" align="left">ID: 0000<?php echo $row['id']; ?></td>
							<td align="center" style="font-size: 16px;">
								<?php if($row['direct_drop'] == 1){ echo '<span style="font-size:12px;">[direct drop]</span>'; } ?>
									<?php echo supplierName($row['supplier_id']); ?>
									<?php if($row['booking_ref_number'] == ''){ ?><span style="color:red;padding-left:5px;font-size:26px;font-weight:700">!</span><?php } ?>
									
									<?php
										$thisid = $row['id'];
										
										$x2 = "SELECT * FROM `intake` WHERE purchase_id='$thisid'";
										$y2 = mysqli_query($conn, $x2);
										$count22 = mysqli_num_rows($y2);
										
										if($intakeCount != 0){
										?> <div class="printedLabel">Intake Created</div> <?php
										}else{
										?>  <?php
										}
									?>
							</td>
							<td width="100" align="right"><?php echo $date_due; ?></td>
						</tr>
					</table>
				</a>
				
				<a href="javascript:;" onclick="deleteRow('<?php echo $row['id'];?>')" id="delete_intake"><i class="fa fa-times" aria-hidden="true"></i></a>
			</td></tr>
		<?php
		}
	}

?>
<?php
	function validateDate($date, $format = 'd/m/Y')
	{
		$d = DateTime::createFromFormat($format, $date);
		return $d && $d->format($format) === $date;
	}
?>
<script type="text/javascript">
	$(document).ready(function(){
		
	});
</script>