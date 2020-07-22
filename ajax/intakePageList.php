<?php

	require('../functions.php');
	
	$term = $_POST['searchterm'];
	
	if($term != ''){ ?>
		<?php
		
		$x = "SELECT * FROM `supplier` WHERE name LIKE '$term%'";
		$y = mysqli_query($conn, $x);
		
		$supplierids = '';
		
		while($row = mysqli_fetch_array($y)){
			$rowid = $row['id'];
			$supplierids .= " OR supplier_id='$rowid'";
        }
        
        $x = "SELECT * FROM `pallet` WHERE id = '$term'";
		$y = mysqli_query($conn, $x);
		
		$palletids = '';
		
		while($row = mysqli_fetch_array($y)){
			$rowid = $row['intake_id'];
			$palletids .= " OR id='$rowid'";
		}
		
		
		
		if (validateDate($term)) {
			// echo $term;
			// echo '<br/><br/>';
			$date = str_replace('/', '-', $term);
			$termDate = date('Y-m-d', strtotime($date));
			// echo '<br/><br/>';
			// echo $termDate = date('Y-m-d',$term);
			// echo		$dateThing = new DateTime($term);
			// $termDate = $dateThing->format('Y-m-d');
			// echo 'isdate';
			
			$x = "SELECT * FROM `intake` WHERE returned='0' date_received LIKE '%$termDate%' ORDER BY date_received DESC";
			 
		}else{
			$x = "SELECT * FROM `intake` WHERE id='" . $term . "' OR vehicle_reg LIKE '$term%' OR id LIKE '%$term%' OR delivery_note_number LIKE '$term%' $supplierids $palletids ORDER BY date_received DESC";
		}
		
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
                                <td width="100" align="left">ID: 0000<?php echo $row['id']; ?></td>
                                <td align="center" style="font-size: 18px;">
                                <?php
                                    echo supplierName($row['supplier_id']);
                                    $r = intakePriceComplete($row['id']);    
                                    if($r == 1){
                                    ?><i class="fa fa-check" aria-hidden="true" style="margin-left:10px;"></i><?php
                                    }
                                ?></td>
                                <td width="100" align="right"><?php echo $date_received; ?></td>
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
		
		$x = "SELECT * FROM `intake` ORDER BY date_received DESC";;
		$y = mysqli_query($conn, $x) or die(mysqli_error($conn));
		while($row = mysqli_fetch_array($y)){
		    $date_received = date('d/m/Y', strtotime($row['date_received']));
		?>
			<tr><td align="center" class="pos">
				<a href="intake.php?id=<?php echo $row['id']; ?>" class="intake">
					<table width="100%" border="0">
						<tr>
							<td width="100" align="left">ID: 0000<?php echo $row['id']; ?></td>
                            <td align="center" style="font-size: 18px;">
                            <?php
                                echo supplierName($row['supplier_id']);
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