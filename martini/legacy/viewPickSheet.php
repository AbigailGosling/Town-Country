<?php

use App\Models\ClientAddress;
use App\Models\ClientType;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

	include('includes/frontHeader.php');

	$picksheetid = request()->input('id');
	$transactionId = Str::random(50);

	$x = "SELECT * FROM `pickerSheets` WHERE id =?";
	$y = prepareExecuteQuery($x,'i',[$picksheetid]);

	$pickerSheet = mysqli_fetch_array($y);
    if($pickerSheet['addressid'] == ''){ $pickerSheet['addressid'] = 1; }
	if ($pickerSheet['is_return_to_supplier']==0)
    {
        $customerResult = prepareExecuteQuery("SELECT * FROM `customers` WHERE id=?",'i',[$pickerSheet['customer_id']]);
        $customer = mysqli_fetch_array($customerResult);

        $recieverName = $customer['businessname'];
        $recieverTA = 't/a '.$customer['tradingas'];
        $ca = ClientAddress::where('client_id', $pickerSheet['customer_id'])->where('address_id', $pickerSheet['addressid'])->where('client_type', ClientType::CUSTOMER->value)->first();
        $recieverAddress1 = $ca->address_1 . '<br/>';
        $recieverAddress2 = $ca->address_2 . '<br/>';
        $recieverAddress3 = $ca->address_3 . '<br/>';
        $recieverPostCode = $ca->postcode . '<br/>';
    }
    else
    {
        $customerResult = prepareExecuteQuery("SELECT * FROM `supplier` WHERE id=?",'i',[$pickerSheet['customer_id']]);
        $customer = mysqli_fetch_array($customerResult);

        $recieverName = $customer['name'];
        $recieverTA = "";
        $recieverAddress1 = $customer['address_1'] . '<br/>';
        $recieverAddress2 = $customer['address_2'] . '<br/>';
        $recieverAddress3 = $customer['address_3'] . '<br/>';
        $recieverPostCode = $customer['postcode'] . '<br/>';
    }


	$type = request()->input('type');

	if($type == 'fresh'){
		$type_value = '1';
	}else{
		$type_value = '2,3';
	}

?>
<style type="text/css">
	#palletAddBtnForm{
		margin-bottom: 12vh;
	}

	.productGroup.disabled{
		opacity: 0.4;
		background: rgba(0,0,255,0.1);
		pointer-events: none;
	}

</style>

<div id="top">
	<a href="menu.php" id="menu">MENU</a>
	<a href="logout" id="logout">LOGOUT</a>
</div>
<script type="text/javascript">
</script>

<main class="int container">

	<a href="<?php echo $domain; ?>pickSheetList.php" class="backbtn">< Back</a>

	<h1>PICK FORM</h1>
	<?php
		$notification = prepareExecuteQuery("SELECT * FROM `pickerNotifications` WHERE `pickersheet_id` = ? ORDER BY id DESC LIMIT 1",'i',[$picksheetid])->fetch_assoc();
		$pickIsLocked = false;
		$userC = User::find(Auth::id());
		if ($notification)
		{
			$sendingUser =  prepareExecuteQuery("SELECT * FROM `users` WHERE `id` = ?",'i',[$notification['user_id']])->fetch_assoc();
			if ($notification['locked'] == "1" && $notification['lock_release'] == "0"){
				$pickIsLocked = true;
				$controlLock = ($userC->hasPermission("send_picker_notification") && ($userC->id == $notification['user_id'] || User::find($userid)->hasPermission("admin")));
				?>
				<div class="row custom-warning-box" id="warning" style="<?php if ($controlLock) //echo "width:75%;";?>background:rgb(255, 102, 102); border: 2px solid rgb(255, 0, 0)">
					Locked by <?php echo $sendingUser['name']; ?> : <?php echo $notification['message']; ?>
					<?php

					?>
				</div>
				<?php
				if ($controlLock){
					?>
					<div style="margin:10px;width:100%">
					<form method="POST" action="scripts/releaseNotificationLock.php">
						<?php echo csrf_field(); ?>
						<input type="hidden" name="pick_id" value="<?php echo $picksheetid; ?>">
						<input type="hidden" name="pick_type" value="<?php echo $type; ?>">
						<input type="hidden" name="id" value="<?php echo $notification['id']; ?>">
						<input type="submit" value="Release Lock" style="width:24%;height:30px;margin-bottom:10px;">
					</form>
				</div>
					<?php
				}
			}
			else
			{
				?>
				<div class="row custom-warning-box" id="warning" style="background:rgb(144, 238, 144); border: 2px solid rgb(0, 255, 0)">
					Message from <?php echo $sendingUser['name']; ?> : <?php echo $notification['message']; ?>
				</div>
				<?php
			}
		}
		if ($userC->hasPermission("send_picker_notification")){
			?>
			<div style="padding:10px;font-size: 18px;width: 100%;border: solid 1px black;">
			<label>Contact Picker</label>
				<form method="POST" action="scripts/sendPickNotification.php" style="width: 100%;">
					<?php echo csrf_field(); ?>
					<input type="hidden" name="pick_id" value="<?php echo $picksheetid; ?>">
					<input type="hidden" name="pick_type" value="<?php echo $type; ?>">
					<input type="textarea" name="message" style="width:100%;height:50px;">
					<table><tr>
					<td style="width:33%"><input type="checkbox" id="checkbox_id" name="lock_pick">
					<label for="checkbox_id">Lock Pick?</label></td>
					<td style="width:80%"></td>
					<td><input type="submit" value="Send Message" style="width:110px;height:30px;margin-bottom:10px;right:0px"></td>
					</tr></table>
				</form>
				</div>
			<?php
		}
	?>
	<div>
		<?php if($pickerSheet['completed'] == '1'){ ?>

		<div>
			<a href="picknote.php?id=<?php echo $pickerSheet['id']; ?>">Pick Note</a>
			|<a href="deliverynote.php?id=<?php echo $pickerSheet['id']; ?>">Delivery Note</a>|
			<a href="invoice.php?id=<?php echo $pickerSheet['id']; ?>">Invoice</a>
		</div><br/>
		<?php } ?><br/>
		<div class="customer_info" style="flex-wrap: wrap;">
			<div style="padding-bottom:10px;font-size: 18px;width: 50%;">
				<label><b>Customer Name:</b> <?php echo $recieverName; ?></label><br/>
				<label><b>Order Number:</b> <?php echo $pickerSheet['id']; ?></label>
			</div>

			<div style="padding-bottom:10px;font-size: 18px;width: 50%;text-align:right;">
				<label><b>Delivery Date:</b> <?php echo $pickerSheet['estimated_delivery_date']; ?></label>
			</div>

			<div style="padding-bottom:10px;font-size: 18px;width:50%;">
				<label><b>Delivery Address:</b>
			<div class="deliverybox">
				<p>
 					<?php echo $recieverName; ?><br/>
					<?php echo $recieverTA; ?><br/>
					<?php



						echo $recieverAddress1 . '<br/>';
						echo $recieverAddress2 . '<br/>';
						echo $recieverAddress3 . '<br/>';
						echo $recieverPostCode . '<br/>';
					?>

				</p>
				</label>
			</div>

			<?php if($pickerSheet['picksheet_note'] != ''){ ?>
			<div style="padding-bottom:10px;font-size: 18px;width:100%;">
				<label><b>Sales note:</b> <?php echo $pickerSheet['picksheet_note']; ?></label>
			</div>
			<?php } ?>

		</div>
	</div>
	<form method="POST" id="palletAddBtnForm" action="scripts/buildOutPallet.php?id=<?php echo $picksheetid; ?>&type=<?php echo request()->input('type'); ?>">
    <input type="text" id="outgoingPalletID" name="outgoingPalletID" value="#" style="display:none;">
	<input type="hidden" id="transaction_id" name="transaction_id" value="<?php echo $transactionId; ?>">
	<?php

		##########################
		$x = "SELECT * FROM `pickerItems` WHERE pickersheet_id=? GROUP BY product_id";
		$y = prepareExecuteQuery($x,'i',[$picksheetid]);

		$productids = '';

		while($row = mysqli_fetch_array($y)){ $productids .= '(id = ' . $row['product_id'] . ' && cooling_id IN ('. $type_value .')) ||'; }
		$productids = rtrim($productids," ||");
		if($productids == '') return;
		##########################

		$productsQuery = "SELECT * FROM `product` WHERE $productids";
		$productsResult = prepareExecuteQuery($productsQuery);

		while($product = mysqli_fetch_array($productsResult)){
		$palletID = $product['pallet_id'];

		$productID = $product['id'];
		$cut_id = $product['cut_id'];


		# PALLET START
		$xPallet = "SELECT * FROM `pallet` WHERE id=? LIMIT 1";
		$yPallet = prepareExecuteQuery($xPallet,'i',[$palletID]);
		$pallet = mysqli_fetch_array($yPallet);
		# PALLET END

		$pickerItemsResult = prepareExecuteQuery("SELECT id,target_weight FROM `pickerItems` WHERE pickersheet_id=? && product_id=?",'ii',[$picksheetid,$productID]);
		$pickerItemsData = mysqli_fetch_array($pickerItemsResult);

		$target_weight = $pickerItemsData['target_weight'];
		$numRequired = mysqli_num_rows($pickerItemsResult);

		$temp_id = $product['cooling_id'];
	?>
	<div class="productGroup <?php if($temp_id == 1){ echo 'fresh'; }else{ echo 'frozen'; } ?>" id="topform<?php echo $product['id']; ?>" targetamount="<?php echo $numRequired; ?>" >
	<?php

		$smallestDate = $product['range_from'];
		$largestDate = ($product['range_extension']!= null && $product['range_extension']!= '')?$product['range_extension']:$product['range_to'];

		$ubbb = $product['ubbb'];
		$smallestDate = $product['range_from'];
		$largestDate = ($product['range_extension']!= null && $product['range_extension']!= '')?$product['range_extension']:$product['range_to'];

		$nationality_id = $product['nationality_id'];

		if($ubbb == 0){
			$ubtext = 'UB';
		}else if($ubbb == 1){
			$ubtext = 'BB';
		}else{
			$ubtext = 'N/A';
		}

	?>
		<div class="picksheetType">
			<table>
				<tr>
					<td>Intake ID</td>
					<td></td>
					<td>Pallet ID</td>
					<td><?php if($pallet['storage_location']){ echo Location::find($pallet['storage_location'])->name; }?></td>
					<td colspan="3"></td>
					<td>Advised Weight</td>
				</tr>
				<tr>
					<td><?php echo intakeIDfromPalletID($product['pallet_id']); ?></td>
					<td></td>
					<td><?php echo $product['pallet_id']; ?></td>
					<td <?php if($temp_id == 1){ echo 'style="background:#c0392b;color:#fff;padding:5px;"'; }else { echo 'style="background:#2980b9;color:#fff;padding:5px;"'; } ?>>
						<?php echo getTemp($temp_id); ?>
					</td>
					<td style="padding-left:20px;padding-right:20px;"><?php echo getSpeciesFromCutID($product['cut_id']) . ' ' . getCut($product['cut_id']); ?></td>
					<td><?php echo getNationality($product['nationality_id']); ?></td>
					<td style="padding-right:20px;"><?php echo getBrand($product['brand_id']); ?></td>
 					<td><?php echo $product['akg']; ?></td>
				</tr>
			</table>

			<div class="rowEndContainer">
				<div class="numRequired"><?php echo $numRequired; ?></div>
				<div class="weightcomment"><?php echo $target_weight . 'kg'; ?></div>
			</div>
		<input type="text" value="<?php if($pallet['grosspallet']){ echo 1; }else{ echo 0; } ?>" class="counter" id="counter-<?php echo $cut_id . '-'. $product['id']; ?>" style="display:none">
		<input type="text" value="<?php echo $numRequired; ?>" id="counter-<?php echo $cut_id . '-'. $product['id']; ?>-max" style="display:none">
		</div>
		<div class="pickerSheetType_content" style="position:relative;">
 			<div class="picksheetPalletDetail" style="display:block">
				<div class="row">
				<?php
					if($product['akg'] != ''){
						$thisproductid = $product['id'];
						$w1 = "SELECT * FROM `weights` WHERE product_id=?";
						$w2 = prepareExecuteQuery($w1,'i',[$thisproductid]);

						$thisweight = mysqli_fetch_array($w2);
					?>
						<input type="text" name="dolavs[]" value="<?php echo $thisweight['id']; ?>" style="display:none;">
						<div style="padding:10px;"><input type="number" name="dolav_<?php echo $thisweight['id']; ?>"><span> / <?php echo $product['akg']; ?></span></div>
					<?php
					}else{
					?>

					<?php
					$weightsQuery = "SELECT * FROM `weights` WHERE product_id=? && status_id != '1' ORDER BY ABS(weight_gross) ASC";
 					$weightsResult = prepareExecuteQuery($weightsQuery,'i',[$productID]);
					$numrows = mysqli_num_rows($weightsResult);

					if($numRequired >= 10 && $numrows != 0){ ?><div class="rowSelector" valselect='<?php echo $numRequired; ?>'><b>Select</b></div><?php }

					$count=0;

					while($weights = mysqli_fetch_array($weightsResult)){
						$count++;

						$weightgross = $weights['weight_gross'];

						// $weightsQuery2 = "SELECT id FROM `weights` WHERE product_id='$productID' && weight_gross='$weightgross'";
						// $weightsResult2 = prepareExecuteQuery($mysqli, $weightsQuery2);
						// $weightsRow = mysqli_fetch_array($weightsResult2);
						// $ccount = mysqli_num_rows($weightsResult2);


                        $someString = getSpeciesFromCutID($product['cut_id']) . ' ' . getCut($product['cut_id']). ' ' . getNationality($product['nationality_id']) . ' ' . $numRequired;


                        if($pallet['grosspallet']){

                            $netWeight = number_format($weights['weight_gross'], 2, '.', '');
                        ?>
                         	<div style="position:relative;padding:10px;">
                                <input type="hidden" value="<?php echo $weights['id']; ?>" name="grossids[]">
                                <input oninput="maxValueCheck(this, <?php echo (int)$netWeight; ?>)" type="number" class="counter" name="gross_<?php echo $weights['id']; ?>" value="0" min="0"><div style="position:absolute;right:25px;top:12px;color:red;"> / <?php echo $netWeight; ?></div>
                            </div>
                            <?php
                        }else{
                        ?>
                        <div class="weightbox" onclick="addStringName('<?php echo $someString; ?>'); addBoxIDtoList(<?php echo $weights['id']; ?>,<?php echo $product['cut_id']; ?>,<?php echo $product['id']; ?>,this,'<?php if($product['weightnote'] != ''){ echo 'true'; }else{ echo 'false'; } ?>');">
                        <?php echo $weights['weight_gross']; ?>
                        </div>
                        <?php
                        }
				?>
                       <?php
						if($count == 10){
							echo '</div><div class="row">';
							if($numRequired >= 10){ echo '<div class="rowSelector" valselect="' . $numRequired . '"><b>Select</b></div>'; }
							$count=0;
						}
					}
				?>
					<?php
					}
				?>
			</div>

			<div class="customWeightContainer" style="display:none;"><input type="text" class="selectedValue" name="selectedValue"></div>
		</div>
	</div>
	</div>
	<?php } ?>

	<br/><br/>

	<?php
		$outpalletQuery = "SELECT * FROM `pickWeightOut` WHERE pickersheet_id=?";
		$outpalletResult = prepareExecuteQuery($outpalletQuery,'i',[$picksheetid]);

		$outpalletCount = mysqli_num_rows($outpalletResult);
	?>

	<?php if($pickerSheet['completed'] != '1'){ ?>
	<div class="picksheet_controls" style="position:fixed;bottom:0px;right:10px;display:none;">
		 	<input type="text" id="weightids" name="weightids" style="display:none;">
			<input type="text" id="newweightvals" name="newweightvals" style="display:none">
			<input type="button" onclick="mainForm()" style="display:none;">
 			<input type="button" id="palletAddBtn" value="Add to Pallet" style="width:323px;height:34px;margin-bottom:10px;display:block;"<?php if($pickIsLocked) echo " disabled"?>>
			<!--<input type="button" id="nextPallet" value="Next Pallet" style="width:323px;height:34px;margin-bottom:10px;display: block;">-->
		</form>
		<br/>
		<form method="POST" action="scripts/markPickerSheetCompleted.php?id=<?php echo $picksheetid; ?>" id="markCompletedForm">
        <input type="hidden" name="transaction_id" value="<?php echo $transactionId; ?>">
		<input type="hidden" name="sheet_type" value="<?php echo request()->input('type'); ?>">
			<?php if($outpalletCount == 0){ ?><div class="completepickwarning">Not ready</div><?php } ?>
			<input type="button" id="completeFormBtn" value="Completed" style="width:323px;height:34px;margin-bottom:10px;"<?php if($outpalletCount == 0 || $pickIsLocked){ ?> disabled <?php } ?>>
		</form>

	</div>

	<script>
		var globalReady = 0;
		var globalNeed = 1;
	</script>

	<?php } ?>

	<br/><br/><br/>

		<?php if($pickerSheet['completed'] == '1'){ ?>
        	<div class="outgoing_pallets">
		<?php }else{ ?>
			<div class="outgoing_pallets" style="display:none;">
		<?php } ?>

		<?php
                $outpalletQuery = "SELECT *,pickWeightOut.id as pid FROM `outgoing_pallet_pickweights` INNER JOIN `pickWeightOut` ON `outgoing_pallet_pickweights`.pickWeightOut_id = `pickWeightOut`.id WHERE `pickWeightOut`.pickersheet_id=?";
                $outpalletResult2 = prepareExecuteQuery($outpalletQuery,'i',[$picksheetid]);

                $outpalletCount = mysqli_num_rows($outpalletResult2);

                while($outpallet = mysqli_fetch_array($outpalletResult2)){
                    $weightids = explode(',', $outpallet['weight_ids']);
                    ?><h3 style="text-align:left;">Outgoing Pallet: <?php echo str_pad($outpallet['outgoing_pallet_id'], 5, '0', STR_PAD_LEFT); ?></h3><?php

                    $productIDArray = array();

                    foreach($weightids as $weightid){
                        $x = "SELECT * FROM `weights` WHERE id=?";
                        $y = prepareExecuteQuery($x,'i',[$weightid]);
                        $weight = mysqli_fetch_array($y);

                        if(!in_array($weight['product_id'], $productIDArray)){
                            array_push($productIDArray, $weight['product_id']);
                        }

                        $queryBits .= ' id = ' . $weightid . ' || ';
                    }

                    foreach($productIDArray as $productID){

                        $x1 = "SELECT * FROM `product` WHERE id=?";
                        $y1 = prepareExecuteQuery($x1,'i',[$productID]);
                        $product = mysqli_fetch_array($y1);


                        if($product['unit'] == 'PPC'){
                            $ext = ' Cases';
                        }else{
                            $ext = ' kg';
                        }

                        $x2 = "SELECT * FROM `weights` WHERE product_id='$productID' AND id IN (".implode(",",array_fill(0,count($weightids),"?")).")";

                        $y2 = prepareExecuteQuery($x2,str_repeat('i',count($weightids)),$weightids);
                        $count = mysqli_num_rows($y2);

                        ${"globalProductCount" . $product['id']} += $count;

                        ?>
                        <script>
							$('#counter-<?php echo $product['cut_id']; ?>-<?php echo $product['id']; ?>').val(<?php echo $count; ?>);

							var howManyWeGot = '<?php echo ${"globalProductCount" . $product['id']}; ?>';
							var target = $('#topform<?php echo $product['id']; ?>').attr('targetamount');

							if(parseInt(howManyWeGot) >= parseInt(target)){
								$('#topform<?php echo $product['id']; ?>').css('opacity','0.2');
								$('#topform<?php echo $product['id']; ?>').css("pointer-events", "none");
								globalReady++;

                                $('#counter-<?php echo $product['cut_id']; ?>-<?php echo $product['id']; ?>').val( $('#counter-<?php echo $product['cut_id']; ?>-<?php echo $product['id']; ?>-max').val());
							}
						</script>
                        <?php
                        $k = 0;

                        while($weight = mysqli_fetch_array($y2)){

                            if($weight['weight_tear'] == $weight['weight_gross']){
                                (double)$w = (double)$weight['weight_gross'];
                            }else{
                                (double)$w = (double)$weight['weight_gross'] - (double)$weight['weight_tear'];
                            }

                            (double)$k = (double)$k + (double)$w;
                        }
                        ?><div><?php echo $outpallet['pid']." ".$count; ?> <?php echo getSpeciesFromCutID($product['cut_id']); ?> - <?php echo getCut($product['cut_id']); ?>
							<?php if($product['unit'] != 'PPC'){ ?>[<?php echo $k . $ext; $k = 0; ?>]</div> <?php } ?>
						<?php
                    }
                }
            ?>
        </div>
</main>

<?php if($pickerSheet['completed'] != '1'){ ?>
	<script> setTimeout(() => { setPickMode('<?php echo request()->input('type'); ?>');  }, 500);</script>
<?php }else{ ?>
	<script> setTimeout(() => { setPickMode('all'); }, 500); </script>
<?php } ?>

<div id="btm"></div>
<script>
 function mainForm(){
	console.log('submit');
    //$('#palletAddBtnForm').ajaxSubmit({headers:{'X-CSRF-TOKEN': "<?php //echo csrf_token();?>"},success:mainFormSucess});
}
function mainFormSucess(){
	location.reload();
}
	function maxValueCheck(ele, max){
		if (parseInt($(ele).val()) > max) {
        	$(ele).val(max);
    	}
	}

	$('.picksheetType').click(function(){
		$(this).next('.pickerSheetType_content').toggle();
	});

	$('.rowSelector').click(function(){

		var maxselect = $(this).attr('valselect');

		var i = 0;
		maxselect++;

		$(this).parent().find('.weightbox').each(function(){
			i++;
			if(i < maxselect){
				$(this).trigger('click');
			}
		});
	});


	function setPickMode(mode){
		$('.pickmodeContainer').hide();
		$('.picksheet_controls').show();
		$('.outgoing_pallets').show();
		if(mode == 'fresh'){
			$('.productGroup.frozen').hide();
			$('.productGroup.fresh').show();
		}

		if(mode == 'frozen'){
			$('.productGroup.fresh').hide();
			$('.productGroup.frozen').show();
		}

		if(mode == 'all'){
			$('.productGroup.fresh').show();
			$('.productGroup.frozen').show();
		}
	}

	function addBoxIDtoList(id, cut_id, product_id, ele, customWeight, count = 1){

		if(customWeight == 'true'){
			// $('.customWeightContainer').fadeIn();
		}

		if($(ele).hasClass('activeWeight')){
			$(ele).removeClass('activeWeight');
			var ids = $('#weightids').val();

			console.log('id: ' + ids);
			ids = ids.replace(id + ',','');
			ids = ids.replace(id + '-' + cut_id + ',', '');
			console.log('new-ids: ' + ids);

			$('#weightids').val(ids);


			var counter = $('#counter-' + cut_id + '-' + product_id).val();
			counter--;
			$('#counter-' + cut_id + '-' + product_id).val(counter);


		}else{

			var maxCounter = $('#counter-' + cut_id + '-' + product_id + '-max').val();
			var counter = $('#counter-' + cut_id + '-' + product_id).val();

			if(counter == maxCounter){
				alert('You have already selected ' + maxCounter + '/' + maxCounter + ' weights!');
			}else{
				counter++;
				$('#counter-' + cut_id + '-' + product_id).val(counter);

				$(ele).addClass('activeWeight');

				if(count > 1){
					for(var i=0;i<count;i++){
						var ids = $('#weightids').val();
						$('#weightids').val(ids + id + ',');
					}
				}else{
					var ids = $('#weightids').val();
					$('#weightids').val(ids + id + ',');
				}

			}

		}
	}

	$('#palletAddBtn').click(function(){
		checkSelectedWeightsAndSubmit();
	});

	globalReady++;
	$('#completeFormBtn').click(function(){
		askForCompleteConfirmation();
	});

	function askForCompleteConfirmation()
	{
		var totalNeeded = 0;
		var totalGot = 0;

		$('.productGroup').each(function(){
			totalNeeded += parseInt($(this).attr('targetamount'));
		});

		$('.counter').each(function(){
			totalGot += parseInt($(this).val());
		});

		var confirmationText = 'Are you sure you want to mark this pick sheet as completed?';
		if(totalGot < totalNeeded){
			confirmationText = 'You have selected ' + totalGot + ' of ' + totalNeeded + ' required weights. Mark as completed anyway?';
		}

		Swal.fire({
			title: 'Confirm Completion',
			text: confirmationText,
			icon: 'warning',
			showCancelButton: true,
			confirmButtonText: 'Confirm'
		}).then((result) => {
			if (result.value) {
				submitCompleteForm();
			}
		});
	}
	function submitCompleteForm()
	{
		$('#completeFormBtn').attr("disabled", true);
		$('#markCompletedForm').ajaxSubmit({headers:{'X-CSRF-TOKEN': "<?php echo csrf_token();?>"},success:markCompletedSucess});
	}
	function markCompletedSucess(){
		window.location = '../menu.php';
	}

	function addStringName(data){
		$('#theRowName').append(data);
	}

	t = 0;

	if(t > 1){
		window.onbeforeunload = function(){
			// alert('test');
			return 1;
		};

	}

	function checkSelectedWeightsAndSubmit()
	{
		var bigValue = '';

		$('.selectedValue').each(function(){
			var value = $(this).val();
 			bigValue += value + ',';
 		});

		$('#newweightvals').val(bigValue);

		var shouldSubmit = false;
		var needApprovalBeforeSubmit = false;

		$('.productGroup.<?php echo request()->input('type'); ?>').each(function(){

			var numRequired = $(this).attr('targetamount');
			var selectedWeightsCount = parseInt($(this).find('.picksheetType').find('.counter').val());
			if(selectedWeightsCount)
			{
				shouldSubmit = true;
			}


			if(numRequired != selectedWeightsCount)
			{
				needApprovalBeforeSubmit = true;
			}
		 });


		 if(!shouldSubmit)
		 {
			 return false;
		 }

		 if(needApprovalBeforeSubmit)
		 {
			askForIncompleteSelectionApprovalAndSubmit();
			return false;
		 }
		askForOutgoingPallet();
	}

	function askForIncompleteSelectionApprovalAndSubmit()
	{

		Swal.fire({
			title: 'Are you sure?',
			text: "You haven't selected all the required weights",
			icon: 'warning',
			showCancelButton: true,
			confirmButtonText: 'Continue'
		}).then((result) => {
			if (result.value) {
				askForOutgoingPallet();
			}
		});
	}
    function askForOutgoingPallet()
    {
        $.ajax({
            url:'<?php echo route("outgoing-pallets.pick-pallets");?>',
            type:'POST',
            headers:{'X-CSRF-TOKEN': "<?php echo csrf_token();?>"},
            data:{pickersheet_id:<?php echo $picksheetid; ?>},
            success:function(data){
                if (data.length == 0)
                {
                    addToPalletSubmit(-1);
                }
                else
                {
                    var options = '<option value="" disabled selected>Select your option</option><option value="-1">Create New Pallet</option>';
                    data.forEach(function(pallet){
                        options += '<option value="' + pallet.id + '">Pallet ' + pallet.id + '</option>';
                    });

                    Swal.fire({
                        title: 'Select Outgoing Pallet',
                        html: '<select id="outgoingPalletSelect" class="swal2-input">' + options + '</select>',
                        showCancelButton: true,
                        confirmButtonText: 'Continue'
                    }).then((result) => {
                        if (result.value) {
                            var outgoingPalletID = $('#outgoingPalletSelect').val();
                            if (outgoingPalletID != null) addToPalletSubmit(outgoingPalletID);
                        }
                     });
                }
            }
        });
    }
    function addToPalletSubmit(outgoingPalletID)
    {
        $('#outgoingPalletID').attr('value', outgoingPalletID);
        $('#palletAddBtn').attr("disabled", true);
        $('#completeFormBtn').attr("disabled", true);
        $('#palletAddBtnForm').ajaxSubmit({headers:{'X-CSRF-TOKEN': "<?php echo csrf_token();?>"},success:mainFormSucess});
    }

</script>
</body>
</html>
