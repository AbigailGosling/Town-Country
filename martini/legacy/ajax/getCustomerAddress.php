<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

	require_once(__DIR__.'/../functions.php');
	require_once('customer_soa_results_function.php');

	$customer_id = request()->input('id');
	$address_id = request()->input('address_id');
	
	$x = "SELECT * FROM `customers` WHERE id = ?";
	$y = prepareExecuteQuery($x,'i',[$customer_id]);
	
	$row = mysqli_fetch_array($y);
	$creditCheck = precredit_check($customer_id);
?>
<script type="text/javascript">
	transactionAllowed 	= <?php echo ($creditCheck['saleAllowed'])?"true":"false"; ?>;
	showWarning 		= <?php echo ($creditCheck['showWarning'])?"true":"false"; ?>;
	showHigherWarning 	= <?php echo ($creditCheck['showHigherWarning'])?"true":"false"; ?>;
	delCheckingOn		= <?php echo ($row['delivery_day_checking'] == 1 && $row['delivery_day_override'] == 0)?"true":"false"; ?>;
	delDays				= <?php echo ($row['delivery_days']>0)?$row['delivery_days']:0; ?>;
	warningMessage		="<table style='width:100%;'><tr><td style='width:50%'><?php echo $creditCheck['message']."</td><td></td><td>".$creditCheck['infoMessage']; ?></td></tr></table>";
</script>
<div class="col">
	<div class="row">
	<div class="col">
	

	<input class="form-control" type="text" id="customer_id" name="customer_id" value="<?php echo $row['id']; ?>" style="display:none;">
	
	<div>
		<label>Contact Number</label><br/>
		<input class="form-control input box" type="text" id="contactnumber" name="contactnumber" value="<?php echo $row['tel_number']; ?>" disabled>
	</div>

	<div>
		<label>Billing Address</label><br/>
		<textarea class="form-control" name="billingaddress" style="height:155px;padding:10px;resize:none;"disabled><?php echo $row['accounts_address_1']; ?>,&#13;<?php echo $row['accounts_address_2']; ?>,&#13;<?php echo $row['accounts_address_3']; ?><?php if($row['accounts_address_3'] != ''){ echo ',&#13;'; } ?><?php echo $row['accounts_address_4']; ?></textarea class="form-control">
	</div>
</div>


<?php
	if($address_id != ''){

		$addressNumber = $row['address'.$address_id.'_number'];	
		$addressline1 = $row['address'.$address_id.'_1'];
		$addressline2 = $row['address'.$address_id.'_2'];
		$addressline3 = $row['address'.$address_id.'_3'];
		$addressline4 = $row['address'.$address_id.'_4'];
		$addresspostcode = $row['postcode_'.$address_id];	

	}else{
		$addressNumber = $row['address1_number'];
		
		$addressline1 = $row['address1_1'];
		$addressline2 = $row['address1_2'];
		$addressline3 = $row['address1_3'];
		$addressline4 = $row['address1_4'];
		$addresspostcode = $row['postcode_1'];	
	}
	
?>


	<div class="col">
	<div>
		<label>Delivery Contact Number</label><br/>
		<input class="form-control input box" type="text" id="deliverynumber" name="deliverynumber" value="<?php echo $addressNumber; ?>">
	</div>
	<div class="delivery_address_container">
		<label>Delivery Address</label> 	<?php if(request()->input('src') != 'salesconfirmation' || User::find(Auth::id())->hasPermission("change_sale_details")){ ?><a href="#changeAddress" data-lity>[Other]</a><?php } ?><br/>

 		<input class="form-control input box" style="margin-bottom:0px;" type="text" id="addressline1" name="addressline1" value="<?php echo $addressline1; ?>" disabled>
 		<input class="form-control input box" style="margin-bottom:0px;" type="text" id="addressline2" name="addressline2" value="<?php echo $addressline2; ?>" disabled>
 		<input class="form-control input box" style="margin-bottom:0px;" type="text" id="addressline3" name="addressline3" value="<?php echo $addressline3; ?>" disabled>
 		<input class="form-control input box" style="margin-bottom:0px;" type="text" id="addressline4" name="addressline4" value="<?php echo $addressline4; ?>" disabled>
 		<input class="form-control input box" style="margin-bottom:0px;" type="text" id="addresspostcode" name="addresspostcode" value="<?php echo $addresspostcode; ?>" disabled>

	</div>
	<?php if(request()->input('src') == 'salesconfirmation'){ ?>
		<div class="printenable" style="display:none;">
			<label>Delivery Address</label><br/>
			<textarea class="form-control" style="height:185px;padding:10px;resize:none;"disabled><?php if($addressline1 != ''){ echo $addressline1 . ',&#13;'; } ?><?php if($addressline2 != ''){ echo $addressline2 . ',&#13;'; } ?><?php if($addressline3 != ''){ echo $addressline3 . ',&#13;'; } ?><?php if($addressline4 != ''){ echo $addressline4 . ',&#13;'; } ?><?php if($addresspostcode != ''){ echo $addresspostcode . ',&#13;'; } ?></textarea>
		</div>
	<?php } ?>
</div>
</div>
<div class="row">
	<div class="col" style="">
		
	<div>
 		<?php
			if(request()->input('empty') != 'true'){
				
				$current_outstanding = (double) $row['current_outstanding'];
				$flaguplimit = (double) $row['flaguplimit'];
				$credit_rating = (double) $row['credit_rating'];
				 
				if(false){ # temp disable credit control requested by Jamie 
					if($current_outstanding >= $credit_rating && $row['override'] != 1){
					?>
					<div class="status stop" style="width:90%;position:fixed;top:470px;left:5%;">Stop</div>
					<script>
						$('.leftPanel').css('pointer-events','none');
						$('.leftPanel').css('opacity','0.2');
						
						$('#sendfake').css('pointer-events','none');
						$('#sendfake').css('opacity','0.2');
						
						
					</script>
					<?php
					}else if($current_outstanding >= $flaguplimit){
					?>
					<div class="status closetolimit" style="width:97%;">Close to limit</div>
					<script>
						$('.leftPanel').css('pointer-events','all');
						$('.leftPanel').css('opacity','1');

						$('#sendfake').css('pointer-events','all');
						$('#sendfake').css('opacity','1');
						
						
					</script>
					<?php
					}else{
					?>
					<script>
						$('.leftPanel').css('pointer-events','all');
						$('.leftPanel').css('opacity','1');
						
						
						$('#sendfake').css('pointer-events','all');
						$('#sendfake').css('opacity','1');
					</script>
					<?php
					}
				}
			}
 		?>
 		 
 	</div>
</div>
<div class="col"></div>
</div>
<div id="changeAddress" class="row lity-hide">
	<h2 style="width: 100%;text-align: center;"><?php echo $row['businessname']; ?>'s Address List</h2>
	<div class="addresses">
		<?php
			for ($u = 1;$u < 10;$u++)
			{
				if($row['address'.$u.'_1'] == '' && $row['postcode_'.$u] == ''){
					$addressOneEmpty = true;
				}else{
					$addressOneEmpty = false;
				}
		?>
		<div class="row flex v-center space-between" onclick="changeAddress('<?php echo $row['id']; ?>', <?php echo ($u>0)?$u:'1'; ?>)">
			<span><?php
				if($addressOneEmpty){
					echo 'Empty';
				}else{
					echo $row['address'.$u.'_1'] . ' ' . $row['postcode_'.$u];
				}
			?></span>
		</div>
		<?php
			}
		?>
	</div>

	
	<?php
		if($row['default_salesman_id'] != null){
		?>
		<script> $('#sales_person').val(<?php echo $row['default_salesman_id']; ?>); </script>
		<?php
		}
	?>
	
	<style>
		.addresses{
			margin:0 auto;
		}
	</style>
</div>
</div>