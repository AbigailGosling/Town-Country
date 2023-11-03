<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

	require(__DIR__.'/../functions.php');
	
	$term = request()->input('searchterm');
	
	if($term != ''){
		
		$SUPPLIER_CUSTOMER_IDS = array(0);

		// Check for search matching suppliers
		$suppliersResult = prepareExecuteQuery("SELECT id FROM `supplier` WHERE `name` LIKE ? || `name` = ?",'ss',[$term.'%',$term]);
		while($supplier = mysqli_fetch_array($suppliersResult)){ $SUPPLIER_CUSTOMER_IDS[]=$supplier['id']; }
		$usermodel = User::find(Auth::id());
		// Check for search matching customers
		$customersResult = prepareExecuteQuery("SELECT id FROM `customers` WHERE `businessname` LIKE ? || `businessname` = ?",'ss',[$term.'%',$term]);
		while($customer = mysqli_fetch_array($customersResult)){ if (!$usermodel->canViewCustomer($customer['id'])) continue;$SUPPLIER_CUSTOMER_IDS[]=$customer['id']; }

		$SUPPLIER_CUSTOMER_IDS = implode(',', $SUPPLIER_CUSTOMER_IDS);


		$palletQuery = prepareExecuteQuery("SELECT intake_id FROM `pallet` WHERE id = ?",'i',[$term]);
		$intakeIDs = array(0);
		while($pallet = mysqli_fetch_array($palletQuery)){ array_push($intakeIDs, $pallet['intake_id']); }
		$intakeIDs = implode(',', $intakeIDs);
		
		if (validateDate($term)) { # search term is a DATE
			$date = str_replace('/', '-', $term);
			$termDate = date('Y-m-d', strtotime($date));
			
			$searchResults = prepareExecuteQuery("SELECT * FROM `intake` WHERE  date_received LIKE ? ORDER BY date_received DESC, id DESC",'s',['%'.$termDate.'%']); 
		}else{
			$searchResults = prepareExecuteQuery("SELECT * FROM `intake` WHERE id=? OR vehicle_reg LIKE ? OR  id LIKE ? OR  delivery_note_number LIKE ? OR ((supplier_id <> '') && supplier_id IN ($SUPPLIER_CUSTOMER_IDS)) OR (id IN ($intakeIDs)) ORDER BY date_received DESC, id DESC"
		,'ssss',[$term,'$term%','$term%','$term%']);
		}

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
							<?php
								$productCountNotCosted = productCountOnIntakeNotCosted($intake['id']);    
							?>
							<td width="30%" align="left">
								ID: I-<?php echo $intake['id'];?></td>
							<td align="left" style="font-size: 18px;" class="<?php if($r == 1){ echo 'flex space-between v-center'; } ?>">
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
									if($intake['returned'] == '1'){ echo ' <small class="return-highlight">return entry</small>'; }

								?>
							</td>
							<td width="30">
								<?php
									if($productCountNotCosted == 0){
									?><i class="fa fa-check" aria-hidden="true" style="margin-left:10px;"></i><?php
									}
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
	}else{ ?>
		<?php
		
		$searchResults = prepareExecuteQuery("SELECT * FROM `intake` WHERE returned ='0' ORDER BY date_received DESC");
		while($intake = mysqli_fetch_array($searchResults)){
		    $date_received = date('d/m/Y', strtotime($intake['date_received']));
		?>
			<tr><td align="center" class="pos">
				<a href="intake.php?id=<?php echo $intake['id']; ?>" class="intake">
					<table width="100%" border="0">
						<tr>
							<td width="30%" align="left">ID: I-<?php echo $intake['id']; ?></td>
                            <td align="left" style="font-size: 18px;">
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

								$productCountNotCosted = productCountOnIntakeNotCosted($intake['id']);    
								if($productCountNotCosted == 0){
								?><i class="fa fa-check" aria-hidden="true" style="margin-left:10px;"></i><?php
								}
								if($intake['returned'] == '1'){ echo ' <small class="return-highlight">return entry</small>'; }
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