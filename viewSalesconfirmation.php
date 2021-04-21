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



<form id="pickerForm" method="POST" action="/scripts/updateSalesconfirmation.php" autocomplete="off">
<input autocomplete="off" name="hidden" type="text" style="display:none;">
<input type="hidden" name="picksheetid" id="picksheetid" value="<?php echo $picksheet_id; ?>">
<input type="hidden" name="customerid" id="customerid" value="<?php echo $customer_id; ?>">
<input type="hidden" name="addressid" id="addressid" value="<?php echo $picksheet['addressid']; ?>">
<div class="container container--pt">
	<div class="row">
		<div class="col">
			<label>Customer</label><br/>
			<input class="form-control" type="text" id="customer" class="inputbox" required>
			<div id="customer_search_results" style="position:relative;z-index:99999;"></div>
		</div>
		<div class="col">
			<label>Delivery Date</label><br/>
			<input class="form-control" type="text" class="inputbox" id="estimated_delivery_date" name="estimated_delivery_date" placeholder="">
		</div>
	  
	</div>
	
	<div class="row" id="address"></div>
	 
	<div class="row printhide">
		<div class="col">
			<label>Picksheet Notes</label><br/>
			<textarea class="form-control" name="picksheet_note" style="height:85px;padding:10px;resize:none;"><?php echo $picksheet['picksheet_note']; ?></textarea>
		</div>
		<div class="col"></div>
	</div>
	
	<div class="row">
		<div class="col">
			<label>	Order Reference Number</label><br/>
			<input id="orderReferenceNumber" class="form-control" type="text" class="inputbox" name="orderReferenceNumber" value="<?php echo $picksheet['orderReferenceNumber']; ?>">
		</div>
		<div class="col"></div>
	</div>

	<div class="row">
		<div class="col">
			<input type="submit" value="Update">
		</div>
	</div>
</div>
</form>

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
								$unit = 'p/KG';
							}else if($product['unit'] == 'PPC'){
								$unit = 'Per Case';
							}else if($product['unit'] == 'P'){
								$unit = 'Pallet';
							}else if($product['unit'] == 'KG'){
								$unit = 'Kilo';
							}else{
								$unit = 'p/KG';
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
	$('#customer').attr('disabled', 'disabled');
 
	$( "#estimated_delivery_date" ).datepicker({
			dateFormat: 'dd/mm/yy'
	});
	setCustomerDetails(<?php echo $customer_id; ?>,<?php echo $picksheet['addressid']; ?>, 'true');
	
	setTimeout(() => {
		$('#customer').val('<?php echo $customer['businessname']; ?>');
		$('#contactnumber').val('<?php echo $customer['contactnumber']; ?>');
		$('#estimated_delivery_date').val('<?php echo $picksheet['estimated_delivery_date']; ?>');
	}, 500);
	
	$('#customer').keyup(function(){
		var val = $('#customer').val();
		$('#customer_search_results').fadeIn();

	 	
		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
            $('#customer_search_results').html(this.responseText);
		}
		};
		xhttp.open("POST", "/ajax/getCustomerDropdown.php", true);
		xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xhttp.send("searchterm=" + val);
	
	});

	function setCustomerDetails(customer_id, addressid, empty='false'){
		customerID = customer_id;
		console.log(' setCustomerDetails()');
		
		$.get( "ajax/getCustomerAddress.php?src=salesconfirmation&address_id=" + addressid + "&id=" + customer_id + '&empty=' + empty, function( data ) {
			$('#address').html(data);
			$('.rating').fadeIn();
		});
	}

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

	function changeAddress(customer_id, address_id){

		$('#addressid').val(address_id);


		$.get("/ajax/getCustomerAddress.php?src=salesconfirmation&id=" + customer_id + '&address_id=' + address_id, function(data, status){
			$('#address').html(data);
			$('.lity-close').trigger('click');
		});
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
		$('.printenable').show();
		$('.delivery_address_container').hide();

		window.print();
	}

	function printCompleted(){
		$('.printhide').show();
		$('.printenable').hide();
		$('.delivery_address_container').show();
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