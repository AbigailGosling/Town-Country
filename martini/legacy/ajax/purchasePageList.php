<?php

	require(__DIR__.'/../functions.php');
	
	$term = request('searchterm');
	
	if($term != ''){ ?>
		<?php
		
		$x = "SELECT * FROM `supplier` WHERE name LIKE ?";
		$y = prepareExecuteQuery($x,'i',['%'.$term.'%']);
		
		$supplierids = '';
		
		while($row = mysqli_fetch_array($y)){
			$rowid = $row['id'];
			$supplierids .= " OR supplier_id='$rowid'";
		}
		
		
		if (validateDate($term)) {
			$date = str_replace('/', '-', $term);
			$termDate = date('Y-m-d', strtotime($date));
			$y = prepareExecuteQuery("SELECT * FROM `purchase_form` WHERE date_due LIKE ? ORDER BY date_due DESC",'i',['%'.$termDate.'%']);
		}else{
			$y = prepareExecuteQuery("SELECT * FROM `purchase_form` WHERE id=? OR id LIKE ? $supplierids  ORDER BY date_due DESC",'is',[$term,'%'.$term.'%']);
		}
		$count = mysqli_num_rows($y);
	
		if($count == 0){
			?><h2 style="color:#fff;font-size:12px;">Nothing found</h2><?php
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

				$date_due = date('d/m/Y', strtotime($row['date_due']));
			?>
				<tr class="pages page<?php echo $num_of_pages; ?>"><td align="center" class="pos">
					<a href="createPurchase.php?id=<?php echo $row['id']; ?>" class="intake">
						<table width="100%" border="0">
							<tr>
								<td width="35%" align="left">ID: <?php echo $row['id']; ?></td>
								<td align="left" style="font-size: 16px;">
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
								<td width="35%" align="right">Created <?php echo $date_due; ?></td>
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
		$y = prepareExecuteQuery($x);
		
		$page_limit = 50;
        $num_of_pages = 1;
		$entry_count = 0;
		
		while($row = mysqli_fetch_array($y)){
			$entry_count++;
            if($entry_count == $page_limit){
                $entry_count = 0;
                $num_of_pages++;
			}
			
		    $date_due = date('d/m/Y', strtotime($row['date_due']));
		?>
			<tr class="pages page<?php echo $num_of_pages; ?>"><td align="center" class="pos">
				<a href="createPurchase.php?id=<?php echo $row['id']; ?>" class="intake">
					<table width="100%" border="0">
						<tr>
							<td width="35%" align="left">ID: <?php echo $row['id']; ?></td>
							<td align="left" style="font-size: 16px;">
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
							<td width="35%" align="right"><?php echo $date_due; ?></td>
						</tr>
					</table>
				</a>
				
				<a href="javascript:;" onclick="deleteRow('<?php echo $row['id'];?>')" id="delete_intake"><i class="fa fa-times" aria-hidden="true"></i></a>
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

<?php
	function validateDate($date, $format = 'd/m/Y')
	{
		$d = DateTime::createFromFormat($format, $date);
		return $d && $d->format($format) === $date;
	}
?>

<script>
    total_pages = <?php echo $num_of_pages; ?>;
</script>