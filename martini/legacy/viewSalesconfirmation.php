<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

	$adv = request()->has("adv");

	if ($adv == false) include_once('includes/frontHeader.php');
	else require_once('functions.php');

	
	$picksheet_id = request()->input('id');

	$x = "SELECT * FROM `pickerSheets` WHERE id=?";
	$y = prepareExecuteQuery($x,'i',[$picksheet_id]);
	$picksheet = mysqli_fetch_array($y);
	
	
	
	$customer_id = $picksheet['customer_id'];
	$x1 = "SELECT * FROM `customers` WHERE id=?";
	$y1 = prepareExecuteQuery($x1,'i',[$customer_id]);
	$customer = mysqli_fetch_array($y1);
	
	$addressNumber = $row['address'.$picksheet['addressid'].'_number'];
	
	$address = $customer['address'.$picksheet['addressid'].'_1'];
	if($customer['address'.$picksheet['addressid'].'_2']){ $address .= ',&#13;'; }
	$address .= $customer['address'.$picksheet['addressid'].'_2'];
	
	if($customer['address'.$picksheet['addressid'].'_3']){ $address .= ',&#13;'; }
	$address .= $customer['address'.$picksheet['addressid'].'_3'];

	if($customer['address'.$picksheet['addressid'].'_4']){ $address .= ',&#13;'; }
	$address .= $customer['address'.$picksheet['addressid'].'_4'];

	if ($adv == false)
	{
?>
<div id="top" class="printhide">
	<a href="menu.php" id="menu">MENU</a>
	<a href="logout" id="logout">LOGOUT</a>
</div>
<?php
	}
?>
<div class="container container--pt flex space-between">
	<div>
		<h2>Sales Confirmation</h2>
	</div>
	<div align="right">
		<h3>Invoice No: <?php echo str_pad($picksheet_id, 6, '0', STR_PAD_LEFT); ?></h3>
		<div align="right" class="printhide">
		<a href="javascript:;" onclick="printStuff()">Print</a>
		|
		<a href="javascript:;" onclick="emailStuff()">Email</a>
		</div>
	</div>
</div>

<input autocomplete="off" name="hidden" type="text" style="display:none;">



<form id="pickerForm" method="POST" action="scripts/updateSalesconfirmation.php" autocomplete="off">
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
			<input class="form-control" type="text" class="inputbox" id="estimated_delivery_date" name="estimated_delivery_date" placeholder="" <?php if (!User::find(Auth::id())->hasPermission("change_sale_details")) {?>disabled<?php }?>>
		</div>
	  
	</div>
	
	<div class="row" id="address"></div>

	<?php if($picksheet['user_from_id'] != ''){ ?>
	<div class="row">
		<div class="col">
			<label>Salesman</label><br/>
 		 	<select id="" class="form-control" name="user_from_id">
				<?php
					$_users = prepareExecuteQuery("SELECT * FROM `users` where 1 in (pages)");

					while ($_user = mysqli_fetch_array($_users)) {
						?><option value="<?php echo $_user['id']; ?>" <?php if($picksheet['user_from_id'] == $_user['id']){ echo 'selected'; } ?>><?php echo $_user['name']; ?></option><?php
					}
				?>
			</select>
		</div>
		<div class="col"></div>
	</div>
	
	<?php } ?>
	
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

	<div class="row printhide">
		<div class="col">
			<?php if (User::find(Auth::id())->hasPermission("change_sale_details")) {?>
			<input type="button" onclick="mainForm()" value="Update">
			<?php }?>
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
			
		
			$x = "SELECT * FROM `pickerItems` WHERE pickersheet_id=?";
			$y = prepareExecuteQuery($x,'i',[$picksheet_id]);
			$vars = array();
			while($item = mysqli_fetch_array($y)){
				$vars[]=$item['product_id'];
			}
			$query = "SELECT * FROM `product` WHERE id IN (".implode(",",array_fill(0,count($vars),"?")).")";

		?>
		
		
		<?php
 			$yproduct = prepareExecuteQuery($query,str_repeat('i',count($vars)),$vars);
			
			while($product = mysqli_fetch_array($yproduct)){
			?>
			<tr class="productsRow">
					<?php
						
						$thispalletid = $product['pallet_id'];
						
						$palletx = "SELECT * FROM `pallet` WHERE id =?";
						$pallety = prepareExecuteQuery($palletx,'i',[$thispalletid]);
						$pallet = mysqli_fetch_array($pallety);
						
					?>
					<td align="left"><span class="intakeid"><?php echo $pallet['intake_id']; ?></span></td>
					<td align="left"><span class="palletid"><?php echo $product['pallet_id']; ?></span></td>
					<td align="left"><b class="species"><?php echo getSpeciesFromCutID($product['cut_id']); ?> <?php echo getCut($product['cut_id']); ?></b></td>
					<td align="left"><span class="chilled"><?php echo getNationality($product['nationality_id']); ?></span></td>
					<td align="left"><b class="brand"><?php echo getBrand($product['brand_id']); ?></b></td>
					<?php
						$productID = $product['id'];
						$howManyX = "SELECT * FROM `pickerItems` WHERE pickersheet_id=? AND product_id=?";
						$howManyY = prepareExecuteQuery($howManyX,'ii',[$picksheet_id,$productID]);
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
							echo '£'. number_format((double)$pickerItem['price'], 2, '.', '');

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
	if(request()->input('msg') != ''){
	?>
	<script type="text/javascript">
		alert('<?php echo request()->input('msg');?>');
	</script>
	<?php	
	}
?>
<script type="text/javascript">
	$('#customer').attr('disabled', 'disabled');
	<?php if (User::find(Auth::id())->hasPermission("change_sale_details")) {?>
	$( "#estimated_delivery_date" ).datepicker({
			dateFormat: 'dd/mm/yy'
	});
	<?php } ?>
	setCustomerDetails(<?php echo $customer_id; ?>,<?php echo $picksheet['addressid']; ?>, 'true');
	
	setTimeout(() => {
		$('#customer').val('<?php echo $customer['businessname']; ?>');
		$('#contactnumber').val('<?php echo $customer['contactnumber']; ?>');
		$('#estimated_delivery_date').val('<?php echo $picksheet['estimated_delivery_date']; ?>');	
		renderCompleted = true;
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
		xhttp.open("POST", "ajax/getCustomerDropdown.php", true);
		xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xhttp.setRequestHeader('X-CSRF-TOKEN', "<?php echo csrf_token();?>");
		xhttp.send("searchterm=" + val);
	
	});
	var renderCompleted = false;
	function setCustomerDetails(customer_id, addressid, empty='false'){
		customerID = customer_id;
		$.get( "ajax/getCustomerAddress.php?src=salesconfirmation&address_id=" + addressid + "&id=" + customer_id + '&empty=' + empty, function( data ) {
			$('#address').html(data);
			$('.rating').fadeIn();
		});
		
	}

	function addToList(id){
		
		$.get( "scripts/getBasketItem.php?id="+id, function( data ) {
			$('.basketTable').append(data);
		});
		
	}
	
	function removeFromList(id, pallet_id, product_id){
		$('.basketRow-' + id).remove();
		var COOKIE_NAME = "quantity-"+product_id+"-"+pallet_id;
		
		document.cookie = COOKIE_NAME + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
	}

	function changeAddress(customer_id, address_id){

		$('#addressid').val(address_id);

		$.get("ajax/getCustomerAddress.php?src=salesconfirmation&id=" + customer_id + '&address_id=' + address_id, function(data, status){
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
	$.ajaxSetup({
		headers: { 'X-CSRF-TOKEN': "<?php echo csrf_token();?>" }
	});
	function mainForm(){
		if ($('#estimated_delivery_date').val() == "") {
			alert("Delivery Date cannot be empty");
			return;
		}
		$('#pickerForm').ajaxSubmit({headers:{'X-CSRF-TOKEN': "<?php echo csrf_token();?>"},success:mainFormSucess});
	}
	function mainFormSucess(){
		location.reload();
	}
	function printStuff(){
		window.print();
	}
	function beforePrint() {
		$('.printhide').hide();
		$('.printenable').show();
		$('.delivery_address_container').hide();

    }

	function emailStuff(){
		$.post("ajax/generatePDFsaleconfirm.php", {id: <?php echo $picksheet_id; ?>},getRenderResp);
		alert("Sent");
	}
	function getRenderResp(data, status){
        
	}
	function renderComplete(){
		return renderCompleted;
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