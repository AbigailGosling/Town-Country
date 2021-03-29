<?php

	require('../functions.php');

	$customer_id = $_GET['id'];
	$address_id = $_GET['address_id'];
	
	$x = "SELECT * FROM `customers` WHERE id = '$customer_id'";
	$y = mysqli_query($conn, $x);
	
	$row = mysqli_fetch_array($y);
	
	
?>

<div class="col">
	<div class="row">
	<div class="col">
	

	<input class="form-control" type="text" name="customer_id" value="<?php echo $row['id']; ?>" style="display:none;">
	
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
		if($address_id == 1){
			
			$addressNumber = $row['address1_number'];
			
			$addressline1 = $row['address1_1'];
			$addressline2 = $row['address1_2'];
			$addressline3 = $row['address1_3'];
			$addressline4 = $row['address1_4'];
			$addresspostcode = $row['postcode_1'];						
					
		}else if($address_id == 2){
			$addressNumber = $row['address2_number'];
			
			$addressline1 = $row['address2_1'];
			$addressline2 = $row['address2_2'];
			$addressline3 = $row['address2_3'];
			$addressline4 = $row['address2_4'];
			$addresspostcode = $row['postcode_2'];	
			
		}else if($address_id == 3){
			$addressNumber = $row['address3_number'];
			
			$addressline1 = $row['address3_1'];
			$addressline2 = $row['address3_2'];
			$addressline3 = $row['address3_3'];
			$addressline4 = $row['address3_4'];
			$addresspostcode = $row['postcode_3'];	
			
		}
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
	<div>
		<label>Delivery Address</label> <a href="#changeAddress" data-lity>[Other]</a><br/>

	 	<label>Address line 1</label>
		<input class="form-control input box" type="text" id="addressline1" name="addressline1" value="<?php echo $addressline1; ?>">
		<label>Address line 2</label>
		<input class="form-control input box" type="text" id="addressline2" name="addressline2" value="<?php echo $addressline2; ?>">
		<label>Address line 3</label>
		<input class="form-control input box" type="text" id="addressline3" name="addressline3" value="<?php echo $addressline3; ?>">
		<label>Address line 4</label>
		<input class="form-control input box" type="text" id="addressline4" name="addressline4" value="<?php echo $addressline4; ?>">
		<label>Postcode</label>
		<input class="form-control input box" type="text" id="addresspostcode" name="addresspostcode" value="<?php echo $addresspostcode; ?>">

	</div>
</div>
</div>
<div class="row">
	<div class="col" style="">
		
	<div>
		<label>Comments</label><br/>
		<textarea class="form-control" id="comments" name="comments" style="height:142px;padding:10px;resize:none;"></textarea class="form-control">
		
 		<?php
			if($_GET['empty'] != 'true'){
				
				$current_outstanding = (float) $row['current_outstanding'];
				$flaguplimit = (float) $row['flaguplimit'];
				$credit_rating = (float) $row['credit_rating'];
				 
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
	
	<?php 
		$address1 = $row['address1_1'];
		
		if($row['address1_2']){ $address1 .= ','; }
		$address1 .= $row['address1_2'];
		
		if($row['address1_3']){ $address1 .= ','; }
		$address1 .= $row['address1_3'];

		if($row['address1_4']){ $address1 .= ','; }
		$address1 .= $row['address1_4'];


		$address2 = $row['address2_1'];
		
		if($row['address2_2']){ $address2 .= ','; }
		$address2 .= $row['address2_2'];
		
		if($row['address2_3']){ $address2 .= ','; }
		$address2 .= $row['address2_3'];

		if($row['address2_4']){ $address2 .= ','; }
		$address2 .= $row['address2_4'];
		
		

		$address3 = $row['address3_1'];
		
		if($row['address3_2']){ $address3 .= ','; }
		$address3 .= $row['address3_2'];
		
		if($row['address3_3']){ $address3 .= ','; }
		$address3 .= $row['address3_3'];

		if($row['address3_4']){ $address3 .= ','; }
		$address3 .= $row['address3_4'];
		
	?>
	<div class="row" onclick="changeAddress('<?php echo $row['id']; ?>', 1)"><?php echo $address1; ?></div>
	
	<?php if($address2 != ''){ ?>
	<div class="row" onclick="changeAddress('<?php echo $row['id']; ?>', 2)"><?php echo $address2; ?></div>
	<?php } ?>

	<?php if($address3 != ''){ ?>
	<div class="row" onclick="changeAddress('<?php echo $row['id']; ?>', 3)"><?php echo $address3; ?></div>
	<?php } ?>
		
</div>
</div>