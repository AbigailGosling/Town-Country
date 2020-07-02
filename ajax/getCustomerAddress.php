<?php

	require('../functions.php');

	$customer_id = $_GET['id'];
	$address_id = $_GET['address_id'];
	
	$x = "SELECT * FROM `customers` WHERE id = '$customer_id'";
	$y = mysqli_query($conn, $x);
	
	$row = mysqli_fetch_array($y);
	
	
?>

<div style="position:absolute;left:92px;top:240px;">
	

	<input type="text" name="customer_id" value="<?php echo $row['id']; ?>" style="display:none;">
	
	<div>
		<label>Contact Number</label><br/>
		<input type="text" class="inputbox" name="contactnumber" value="<?php echo $row['tel_number']; ?>" disabled>
	</div>

	<div>
		<label>Billing Address</label><br/>
		<textarea name="billingaddress" style="width:300px;height:80px;padding:10px;resize:none;"disabled><?php echo $row['accounts_address_1']; ?>,&#13;<?php echo $row['accounts_address_2']; ?>,&#13;<?php echo $row['accounts_address_3']; ?><?php if($row['accounts_address_3'] != ''){ echo ',&#13;'; } ?><?php echo $row['accounts_address_4']; ?></textarea>
	</div>
</div>

<?php
	 if($address_id != ''){
		if($address_id == 1){
			$addressNumber = $row['address1_number'];
			
			$address = $row['address1_1'];
			if($row['address1_2']){ $address .= ',&#13;'; }
			$address .= $row['address1_2'];
			
			if($row['address1_3']){ $address .= ',&#13;'; }
			$address .= $row['address1_3'];

			if($row['address1_4']){ $address .= ',&#13;'; }
			$address .= $row['address1_4'];
					
					
		}else if($address_id == 2){
			$addressNumber = $row['address2_number'];
			
			$address = $row['address2_1'];
			if($row['address2_2']){ $address .= ',&#13;'; }
			$address .= $row['address2_2'];
			
			if($row['address2_3']){ $address .= ',&#13;'; }
			$address .= $row['address2_3'];

			if($row['address2_4']){ $address .= ',&#13;'; }
			$address .= $row['address2_4'];
			
		}else if($address_id == 3){
			$addressNumber = $row['address3_number'];
			
			$address = $row['address3_1'];
			if($row['address3_2']){ $address .= ',&#13;'; }
			$address .= $row['address3_2'];
			
			if($row['address3_3']){ $address .= ',&#13;'; }
			$address .= $row['address3_3'];

			if($row['address3_4']){ $address .= ',&#13;'; }
			$address .= $row['address3_4'];
			
		}
	 }else{
		$addressNumber = $row['address1_number'];
		
		$address = $row['address1_1'];
		if($row['address1_2']){ $address .= ',&#13;'; }
		$address .= $row['address1_2'];
		
		if($row['address1_3']){ $address .= ',&#13;'; }
		$address .= $row['address1_3'];

		if($row['address1_4']){ $address .= ',&#13;'; }
		$address .= $row['address1_4'];
	 }
	
?>

<div style="">
	<div style="display:block;padding-top:10px;">
	<label>Delivery Contact Number</label><br/>
	<input type="text" class="inputbox" name="deliverynumber" value="<?php echo $addressNumber; ?>">
	</div>
	<div style="display:block;padding-top:10px;">
		<label>Delivery Address</label> <a href="#changeAddress" data-lity>[Other]</a><br/>
		<textarea name="deliveryaddress" style="width:300px;height:80px;padding:10px;resize:none;"><?php echo $address; ?></textarea>
	</div>
</div>
<div style="">
		
	<div style="display:block;padding-top:10px;">
		<label>Comments</label><br/>
		<textarea name="comments" style="width:300px;height:142px;padding:10px;resize:none;"></textarea>
		
 		<?php
			if($_GET['empty'] != 'true'){
				
				$current_outstanding = (float) $row['current_outstanding'];
				$flaguplimit = (float) $row['flaguplimit'];
				$credit_rating = (float) $row['credit_rating'];
				 
				
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
 		?>
 		 
 	</div>
</div>
<div id="changeAddress" class="lity-hide">
	<h2><?php echo $row['businessname']; ?>'s Address List</h2>
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
	<div class="row" onclick="changeAddress('<?php echo $row['id']; ?>', 2)"><?php echo $address2; ?></div>
	<div class="row" onclick="changeAddress('<?php echo $row['id']; ?>', 3)"><?php echo $address3; ?></div>
		
</div>