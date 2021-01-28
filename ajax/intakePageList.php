<?php

	require('../functions.php');
	
	$term = $_POST['searchterm'];
	
	if($term != ''){
		
		# Get any suppliers that match the search term
		$supplierQuery = mysqli_query($conn, "SELECT id FROM `supplier` WHERE `name` LIKE '$term%'");
		$supplierIDs = array(0);
		while($supplier = mysqli_fetch_array($supplierQuery)){ array_push($supplierIDs, $supplier['id']); }
		$supplierIDs = implode(',', $supplierIDs);
		//var_dump($supplierIDs);die;
		# Get intake_id for any pallets that match the search term
		$palletQuery = mysqli_query($conn, "SELECT intake_id FROM `pallet` WHERE id = '$term'");
		$intakeIDs = array(0);
		while($pallet = mysqli_fetch_array($palletQuery)){ array_push($intakeIDs, $pallet['intake_id']); }
		$intakeIDs = implode(',', $intakeIDs);
		//var_dump($intakeIDs);die;
		
		if (validateDate($term)) { # search term is a DATE
			$date = str_replace('/', '-', $term);
			$termDate = date('Y-m-d', strtotime($date));
			
			$searchQuery  = "SELECT * FROM `intake` WHERE returned='0' date_received LIKE '%$termDate%' ORDER BY date_received DESC, id DESC"; 
		}else{
			$searchQuery = "SELECT * FROM `intake` WHERE returned='0' && id='" . $term . "' OR returned='0' && vehicle_reg LIKE '$term%' OR returned='0' && id LIKE '%$term%' OR returned='0' && delivery_note_number LIKE '$term%' OR (returned='0' && (supplier_id <> '') && supplier_id IN ($supplierIDs)) OR (returned='0' && id IN ($intakeIDs)) ORDER BY date_received DESC, id DESC";
		}
		
		$searchResults = mysqli_query($conn, $searchQuery) or die(mysqli_error($conn));
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
                                <td width="30%" align="left">ID: I-0000<?php echo $intake['id']; ?></td>
                                <td align="left" style="font-size: 18px;">
                                <?php
                                    echo supplierName($intake['supplier_id']);
                                    $r = intakePriceComplete($intake['id']);    
                                    if($r == 1){
                                    ?><i class="fa fa-check" aria-hidden="true" style="margin-left:10px;"></i><?php
                                    }
                                ?></td>
                                <td width="30%" align="right"><?php echo $date_received; ?></td>
                            </tr>
                        </table>
                    </a>
                    
                    <a href="javascript:;" onclick="deleteRow('<?php echo $intake['id'];?>')" id="delete_intake"><i class="fa fa-times" aria-hidden="true"></i></a>
                </td></tr>
            <?php
            }
        }
    }else{ ?>
		<?php
		
		$searchResults = mysqli_query($conn, "SELECT * FROM `intake` WHERE returned ='0' ORDER BY date_received DESC");
		while($intake = mysqli_fetch_array($searchResults)){
		    $date_received = date('d/m/Y', strtotime($intake['date_received']));
		?>
			<tr><td align="center" class="pos">
				<a href="intake.php?id=<?php echo $intake['id']; ?>" class="intake">
					<table width="100%" border="0">
						<tr>
							<td width="30%" align="left">ID: I-0000<?php echo $intake['id']; ?></td>
                            <td align="left" style="font-size: 18px;">
                            <?php
                                echo supplierName($intake['supplier_id']);
                                $r = intakePriceComplete($intake['id']);    
                                if($r == 1){
                                ?><i class="fa fa-check" aria-hidden="true" style="margin-left:10px;"></i><?php
                                }
                            ?>
                            </td>
							<td width="30%" align="right"><?php echo $date_received; ?></td>
						</tr>
					</table>
				</a>
				
				<a href="javascript:;" onclick="deleteRow('<?php echo $intake['id'];?>')" id="delete_intake"><i class="fa fa-times" aria-hidden="true"></i></a>
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