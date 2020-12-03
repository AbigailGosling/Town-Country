<?php

	require('../functions.php');
    
    $month = $_POST['month'];
	$year = $_POST['year'];
	
	$month = str_pad($month, 2, '0', STR_PAD_LEFT);
	
	$startDate = $year . '-' . $month . '-01';
	$endDate = $year . '-' . $month . '-31';
        
    
    $searchResults = mysqli_query($conn, "SELECT * FROM `purchase_form` WHERE date_due BETWEEN '$startDate' AND '$endDate' ORDER BY date_due DESC");
	
    $countResults = mysqli_num_rows($searchResults);
    
    if($countResults == 0){
		?><h2 style="color:#fff;font-size:12px;">No purchases found</h2><?php
	}else{
		while($row = mysqli_fetch_array($searchResults)){
            $date_purchased = date('d/m/Y', strtotime($row['date_due']));
        ?>
		 <tr><td align="center" class="pos">
            <a href="createPurchase.php?id=<?php echo $row['id']; ?>" class="intake">
                <table width="100%" border="0">
                    <tr>
                        <td width="35%" align="left">ID: P-00<?php echo $row['id']; ?> </td>
                        <td align="left" style="font-size: 16px;">
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
                        <td width="35%" align="right"><?php echo $date_purchased; ?></td>
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