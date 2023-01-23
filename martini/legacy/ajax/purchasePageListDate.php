<?php

	require(__DIR__.'/../functions.php');
    
    $month = request('month');
	$year = request('year');
	
	$month = str_pad($month, 2, '0', STR_PAD_LEFT);
	
	$startDate = $year . '-' . $month . '-01';
	$endDate = $year . '-' . $month . '-31';
        
    
    $searchResults = prepareExecuteQuery("SELECT * FROM `purchase_form` WHERE date_due BETWEEN ? AND ? ORDER BY date_due DESC",'ss',[$startDate,$endDate]);
	
    $countResults = mysqli_num_rows($searchResults);
    
    if($countResults == 0){
		?><h2 style="color:#fff;font-size:12px;">No purchases found</h2><?php
	}else{

        $page_limit = 50;
        $num_of_pages = 1;
        $entry_count = 0;
		while($row = mysqli_fetch_array($searchResults)){
            $entry_count++;
            if($entry_count == $page_limit){
                $entry_count = 0;
                $num_of_pages++;
            }

            $date_purchased = date('d/m/Y', strtotime($row['date_due']));
        ?>
		 <tr class="pages page<?php echo $num_of_pages; ?>"><td align="center" class="pos">
            <a href="createPurchase.php?id=<?php echo $row['id']; ?>" class="intake">
                <table width="100%" border="0">
                    <tr>
                        <td width="35%" align="left">ID: <?php echo $row['id']; ?> </td>
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
<?php
	function validateDate($date, $format = 'd/m/Y')
	{
		$d = DateTime::createFromFormat($format, $date);
		return $d && $d->format($format) === $date;
	}
?>