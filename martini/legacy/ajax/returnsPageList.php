<?php

	require(__DIR__.'/../functions.php');
	
	$term = request('searchterm');
	
	if($term != ''){
		
		# Get any customers that match the search term
		$customerQuery = prepareExecuteQuery("SELECT id FROM `customers` WHERE `businessname` LIKE ? || REPLACE(businessname, ' ', '') = ?",'ss',['%'.$term.'%','%'.$term.'%']);
		$customerIDs = array(0);
		while($customer = mysqli_fetch_array($customerQuery)){ array_push($customerIDs, $customer['id']); }
		$customerIDs = implode(',', $customerIDs);
		
		
		if(validateDate($term)) { # search term is a DATE
			$date = str_replace('/', '-', $term);
			$termDate = date('Y-m-d', strtotime($date));
			$searchResults = prepareExecuteQuery("SELECT * FROM `intake` WHERE returned=1 && date_received LIKE ? ORDER BY date_received DESC",'s',['%'.$termDate.'%']);
		}else{
			$searchResults = prepareExecuteQuery("SELECT * FROM `intake` 
			WHERE returned=1 && (id=? OR returned=1 && vehicle_reg LIKE ? OR returned=1 && id LIKE ? OR returned=1 && 
			delivery_note_number LIKE ? OR returned=1 && supplier_id IN ($customerIDs)) ORDER BY date_received DESC",'isss',[$term,'%'.$term.'%','%'.$term.'%','%'.$term.'%']);
		}

		$countResults = mysqli_num_rows($searchResults);
	
		if($countResults == 0){
			?><h2 style="color:#fff;font-size:12px;">No intakes found</h2><?php
		}else{
			while($returnedIntake = mysqli_fetch_array($searchResults)){
				$date_received = date('d/m/Y', strtotime($returnedIntake['date_received']));
			?>
				<tr><td align="center" class="pos">
					<a href="intake.php?id=<?php echo $returnedIntake['id']; ?>" class="intake">
						<table width="100%" border="0">
							<tr>
								<?php
									$customer = getCustomer($returnedIntake['supplier_id']);
								?>
								<td width="100" align="left">ID: I-<?php echo $returnedIntake['id']; ?></td>
								<td align="center" style="font-size: 18px;"><?php echo $customer['businessname']; ?></td>
								<td width="100" align="right"><?php echo $date_received; ?></td>
							</tr>
						</table>
					</a>
					
					<a href="javascript:;" onclick="deleteRow('<?php echo $returnedIntake['id'];?>')" id="delete_intake"><i class="fa fa-times" aria-hidden="true"></i></a>
				</td></tr>
			<?php
			}
		}
	}else{ # Search term is empty, show all returned intakes
		
		$searchResults = prepareExecuteQuery("SELECT * FROM `intake` WHERE returned='1' ORDER BY date_received DESC");

		while($returnedIntake = mysqli_fetch_array($searchResults)){
		    $date_received = date('d/m/Y', strtotime($returnedIntake['date_received']));
		?>
			<tr><td align="center" class="pos">
				<a href="intake.php?id=<?php echo $returnedIntake['id']; ?>" class="intake">
					<table width="100%" border="0">
						<tr>
							<?php
								$customer = getCustomer($returnedIntake['supplier_id']);
							?>
							<td width="100" align="left">ID: I-<?php echo $returnedIntake['id']; ?></td>
							<td align="center" style="font-size: 18px;"><?php echo $customer['businessname']; ?></td>
							<td width="100" align="right"><?php echo $date_received; ?></td>
						</tr>
					</table>
				</a>
				
				<a href="javascript:;" onclick="deleteRow('<?php echo $returnedIntake['id'];?>')" id="delete_intake"><i class="fa fa-times" aria-hidden="true"></i></a>
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