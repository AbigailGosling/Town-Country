<?php
	include('includes/frontHeader.php');
	
	
	$picksheet_id = $_GET['id'];

	$x = "SELECT * FROM `pickerSheets` WHERE id='$picksheet_id'";
	$y = mysqli_query($conn, $x) or die(mysqli_error($conn));
	$picksheet = mysqli_fetch_array($y);
	
	
	
	$customer_id = $picksheet['customer_id'];
	$x1 = "SELECT * FROM `customers` WHERE id='$customer_id'";
	$y1 = mysqli_query($conn, $x1) or die(mysqli_error($conn));
	$customer = mysqli_fetch_array($y1);
	
	$addressNumber = $row['address1_number'];
    
    if($picksheet['addressid'] == ''){ $picksheet['addressid'] = 1; }


    if($picksheet['addressid'] == 1){

        $address = $customer['address1_1'];
        if($customer['address1_2']){ $address .= ',&#13;'; }
        $address .= $customer['address1_2'];
        
        if($customer['address1_3']){ $address .= ',&#13;'; }
        $address .= $customer['address1_3'];

        if($customer['address1_4']){ $address .= ',&#13;'; }
        $address .= $customer['address1_4'];
    }

    if($picksheet['addressid'] == 2){

        $address = $customer['address2_1'];
        if($customer['address2_2']){ $address .= ',&#13;'; }
        $address .= $customer['address2_2'];
        
        if($customer['address2_3']){ $address .= ',&#13;'; }
        $address .= $customer['address2_3'];

        if($customer['address2_4']){ $address .= ',&#13;'; }
        $address .= $customer['address2_4'];
    }

    
    if($picksheet['addressid'] == 3){

        $address = $customer['address3_1'];
        if($customer['address3_2']){ $address .= ',&#13;'; }
        $address .= $customer['address3_2'];
        
        if($customer['address3_3']){ $address .= ',&#13;'; }
        $address .= $customer['address3_3'];

        if($customer['address3_4']){ $address .= ',&#13;'; }
        $address .= $customer['address3_4'];
    }
# 
?>
<div id="top" class="printhide">
	<a href="menu.php" id="menu">MENU</a>
	<a href="logout.php" id="logout">LOGOUT</a>
</div>

<div class="container container--pt flex space-between">
	<div>
		<h2>Sales Confirmation</h2>
	</div>
	<div align="right">
		<h3>Invoice No: <?php echo str_pad($picksheet_id, 6, '0', STR_PAD_LEFT); ?></h3>
		<a href="javascript:;" class="printhide" onclick="printStuff()">Print</a>
	</div>
</div>

<input autocomplete="off" name="hidden" type="text" style="display:none;">
<div class="container">
<div class="row">
	<div class="col">
			<label>Customer</label><br/>
			<input type="text" id="customer" value="<?php echo $customer['businessname']; ?>" class="inputbox" disabled>
	</div>
	<div class="col">
			<label>Contact Number</label><br/>
			<input type="text" class="inputbox" name="contactnumber" value="<?php echo $customer['contactnumber']; ?>" disabled>
	</div>
</div>
<div class="row">
	<div class="col">
			<label>Delivery Date</label><br/>
			<input type="text" class="inputbox" value="<?php echo $picksheet['estimated_delivery_date']; ?>" disabled>
	</div>
	<div class="col">
		<label>Delivery Contact Number</label><br/>
		<input type="text" class="inputbox" name="deliverynumber" value="<?php echo $addressNumber; ?>">
	</div>
</div>

	
	<span class="row" id="address">
		<input type="text" name="customer_id" value="<?php echo $row['id']; ?>" style="display:none;">

		<span class="col">
			<label>Billing Address</label><br/>
			<textarea name="billingaddress" style="width:300px;height:80px;padding:10px;resize:none;"disabled><?php echo $customer['accounts_address_1']; ?>,&#13;<?php echo $customer['accounts_address_2']; ?>,&#13;<?php echo $customer['accounts_address_3']; ?><?php if($customer['accounts_address_3'] != ''){ echo ',&#13;'; } ?><?php echo $customer['accounts_address_4']; ?></textarea>
		</span>

		<span class="col">
			<label>Delivery Address</label><br/>
			<textarea name="deliveryaddress" style="width:300px;height:80px;padding:10px;resize:none;"><?php echo $address; ?></textarea>
		</span>

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
	</span>

	<div class="row">
		<div class="col">
			<label>Comments</label><br/>
			<textarea name="comments" style="width:300px;height:142px;padding:10px;resize:none;"><?php echo $picksheet['comments']; ?></textarea>	 
		</div>

		<div class="col">
			<label>	Order Reference Number</label><br/>
			<input type="text" class="inputbox" name="orderReferenceNumber" value="<?php echo $picksheet['orderReferenceNumber']; ?>">
		</div>

	 
	</div>
</div>

<div class="rightPanel">
	<table width="100%" class="basketTable">
		<tr align="left" style="background:#e6931894;height:30px;color:#FFF;">
			<th>Intake ID</th>
			<th>Plt ID</th>
			<th>Product</th>
			<th>Nationality</th>
			<th>Brand</th>
 			<th>Quantity</th>
 			<th>Unit</th>
 			<th>Price</th>
		</tr>
		<?php
			$query = "SELECT * FROM `product` WHERE ";
		
			$x = "SELECT * FROM `pickerItems` WHERE pickersheet_id='$picksheet_id'";
			$y = mysqli_query($conn, $x);
			
			while($item = mysqli_fetch_array($y)){
				$query .= " id = '".$item['product_id'] ."' ||";
			}
			
			$query = substr($query, 0, -3);

		?>
		
		
		<?php
 			$yproduct = mysqli_query($conn, $query);
			
			while($product = mysqli_fetch_array($yproduct)){
			?>
			<tr class="productsRow">
					<?php
						
						$thispalletid = $product['pallet_id'];
						
						$palletx = "SELECT * FROM `pallet` WHERE id ='$thispalletid'";
						$pallety = mysqli_query($conn, $palletx);
						$pallet = mysqli_fetch_array($pallety);
						
					?>
					<td align="left"><span class="intakeid"><?php echo $pallet['intake_id']; ?></span></td>
					<td align="left"><span class="palletid"><?php echo $product['pallet_id']; ?></span></td>
					<td align="left"><b class="species"><?php echo getSpeciesFromCutID($product['cut_id']); ?> <?php echo getCut($product['cut_id']); ?></b></td>
					<td align="left"><span class="chilled"><?php echo getNationality($product['nationality_id']); ?></span></td>
					<td align="left"><b class="brand"><?php echo getBrand($product['brand_id']); ?></b></td>
					<?php
						$productID = $product['id'];
						$howManyX = "SELECT * FROM `pickerItems` WHERE pickersheet_id='$picksheet_id' AND product_id='$productID'";
						$howManyY = mysqli_query($conn, $howManyX);
						$pickerItem = mysqli_fetch_array($howManyY);
						$howMany = mysqli_num_rows($howManyY);
					?>
					<td align="left">
						<b class="howmany">
						<?php echo $howMany; ?>
						</b>
					</td>
					<td align="left">
						<b class="unit">
						 
						<?php
							
							if($product['unit'] == 'C'){
								$unit = 'Cases';
							}else if($product['unit'] == 'PPC'){
								$unit = 'Per Case';
							}else if($product['unit'] == 'P'){
								$unit = 'Pallet';
							}else if($product['unit'] == 'KG'){
								$unit = 'Kilo';
							}else{
								$unit = 'Cases';
							}
							
							echo $unit;
						?>
						</b>
					</td>
					<td>
						<?php
							echo '£'. number_format((float)$pickerItem['price'], 2, '.', '');

						?>
					</td>
 					</tr>
			
			<?php
			}
		?>
		
 	</table>
	
	<div style="float:right;">
		<br/><br/>
		<div class="totalprice" style="display:none;"></div>
		<br/>
  	</div>
</div>
 


<div class="clearfix"></div>
<?php 
	if($_GET['msg'] != ''){
	?>
	<script type="text/javascript">
		alert('<?php echo $_GET['msg'];?>');
	</script>
	<?php	
	}
?>
<script type="text/javascript">
	function addToList(id){
		
		$.get( "/scripts/getBasketItem.php?id="+id, function( data ) {
			$('.basketTable').append(data);
		});
		
	}
	
	function removeFromList(id, pallet_id, product_id){
		$('.basketRow-' + id).remove();
		var COOKIE_NAME = "quantity-"+product_id+"-"+pallet_id;
		
		console.log('trying to delete cookie ' + COOKIE_NAME);
		document.cookie = COOKIE_NAME + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
	}
</script>

<style type="text/css">
	.rightPanel{
		width:calc(100% - 103px);
	
		float:left;
		padding:50px;
		position:relative;
		margin-top:40px;
	}
	.leftPanel{
		width:calc(100% - 103px);
		height:100%;
		float:left;
		padding:50px;
		border:1px solid #f4f4f4;
		position:relative;
	}
	
	.leftPanel{
		background:#f2f2f2;
	}
	
	.clearfix{
		clear:both;
	}
	
	.inputbox-button{
		width:323px;
		height:34px;
		margin-bottom:10px;
	}
	
	.inputbox{
		width:300px;
		height:34px;
		padding-left:18px;
 
	}
	
	.createCustomerContainer{
		font-weight:700;
		position:absolute;
		top:50px;
		right:30px;
	}
	
	.weightTotal{
		font-weight:700;
		position:absolute;
		top:50px;
		right:30px;
	}
	
	.resultsContainer{
		width: calc(100% - 40px);
		min-height: 400px;
		border: 2px dashed #cacaca;
		padding: 0px;
		margin-top: 20px;
		padding-top: 14px;
	}
</style>
<script type="text/javascript">
	
	function printStuff(){

		$('.printhide').hide();

		window.print();
	}

	function printCompleted(){
		$('.printhide').show();
	}
</script>

<style type="text/css">
input[type='number'] {
    -moz-appearance:textfield;
}
/* Webkit browsers like Safari and Chrome */
input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
</style>