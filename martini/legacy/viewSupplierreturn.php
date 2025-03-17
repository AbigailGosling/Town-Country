<?php
use App\Models\User;
use Illuminate\Support\Facades\Auth;
	$adv = request()->has("adv");
	$usermodel = User::find(Auth::id());
	if ($adv == false) include_once('includes/frontHeader.php');
	else require_once('functions.php');

	$picksheet_id = request()->input('id');
	$x = "SELECT * FROM `pickerSheets` WHERE id=?";
	$y = prepareExecuteQuery($x,'i',[$picksheet_id]);
	$picksheet = mysqli_fetch_array($y);

	$supplier_id = $picksheet['customer_id'];
	$x1 = "SELECT * FROM `supplier` WHERE id=?";
	$y1 = prepareExecuteQuery($x1,'i',[$supplier_id]);
	$supplier = mysqli_fetch_array($y1);
    $address = $supplier['address_1'];
	if($supplier['address_2']){ $address .= ',&#13;'; }
	$address .= $supplier['address_2'];
	if($supplier['address_3']){ $address .= ',&#13;'; }
	$address .= $supplier['address_3'];
	if($supplier['address_4']){ $address .= ',&#13;'; }
	$address .= $supplier['address_4'];
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
		<h2>Supplier Return</h2>
	</div>
	<div align="right">
		<h3>No: <?php echo str_pad($picksheet_id, 6, '0', STR_PAD_LEFT); ?></h3>
		<div align="right" class="printhide">
		<a href="javascript:;" onclick="printStuff()">Print</a>
		|
		<a href="javascript:;" onclick="emailStuff()">Email</a>
		</div>
	</div>
</div>
<input autocomplete="off" name="hidden" type="text" style="display:none;">
<input autocomplete="off" name="hidden" type="text" style="display:none;">
<input type="hidden" name="picksheetid" id="picksheetid" value="<?php echo $picksheet_id; ?>">
<input type="hidden" name="supplierid" id="supplierid" value="<?php echo $supplier_id; ?>">
<div class="container container--pt">
	<div class="row">
		<div class="col">
			<label>Supplier</label><br/>
			<input class="form-control" type="text" id="supplier" value="<?php echo $supplier['name'];?>" class="inputbox" required>
			<div id="supplier_search_results" style="position:relative;z-index:99999;"></div>
		</div>
		<div class="col">
			<label>Delivery Date</label><br/>
			<input class="form-control" type="text" class="inputbox" id="estimated_delivery_date" name="estimated_delivery_date" placeholder="" disabled>
		</div>
	</div>
	<div class="row" id="address"></div>
	<?php if($picksheet['user_from_id'] != ''){ ?>
	<div class="row">
		<div class="col">
			<label>Operator</label><br/>
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
			<label>Return Notes</label><br/>
			<textarea class="form-control" name="picksheet_note" style="height:85px;padding:10px;resize:none;"><?php echo $picksheet['picksheet_note']; ?></textarea>
		</div>
		<div class="col"></div>
	</div>
	<div class="row">
		<div class="col">
			<label>	External Reference Number</label><br/>
			<input id="orderReferenceNumber" class="form-control" type="text" class="inputbox" name="orderReferenceNumber" value="<?php echo $picksheet['orderReferenceNumber']; ?>">
		</div>
		<div class="col"></div>
	</div>
	<div class="row printhide">
		<div class="col">

		</div>
	</div>
    <div class="row custom-warning-box" id="warning" style="width: 100%; padding-top:0px; padding-bottom:0px;  padding: left right 15px; display:none"></div>
</div>
</form>
<div class="rightPanel">
	<table width="100%" class="basketTable">
		<tr align="left" style="background:#e6931894;height:30px;color:#FFF;">
			<th>Intake ID</th>
			<th>Plt ID</th>
			<th width="45%">Product</th>
			<th align="right">Dated</th>
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
						$smallestDate = ($product['range_extension']!= null && $product['range_extension']!= '')?"EXTENSION":$product['range_from'];
						$largestDate = ($product['range_extension']!= null && $product['range_extension']!= '')?$product['range_extension']:$product['range_to'];
                        $temp_id = $product['cooling_id'];
					?>
					<td align="left"><span class="intakeid"><?php echo $pallet['intake_id']; ?></span></td>
					<td align="left"><span class="palletid"><?php echo $product['pallet_id'];?></span></td>
					<td align="left"><b class="species"><?php echo getSpeciesFromCutID($product['cut_id']); echo getCut($product['cut_id']);  ?></b></td>
					<td align="right"><b class="species"><?php echo $smallestDate.'-'.$largestDate; ?></b></td>
                    <td style="display: none;" id="ubDate"><?php echo $smallestDate; ?></td><td id=temp_id style="display:none;"><?php echo $temp_id; ?></td>
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
	$('#supplier').attr('disabled', 'disabled');
	<?php if (User::find(Auth::id())->hasPermission("change_sale_details")) {?>
	$( "#estimated_delivery_date" ).datepicker({
            onSelect: ddChanged,
			dateFormat: 'dd/mm/yy'
	});
	<?php } ?>
	setSupplierDetails(<?php echo $supplier_id; ?>, 'true');
	setTimeout(() => {
		$('#supplier').val('<?php echo $supplier['name']; ?>');
		$('#contactnumber').val('<?php echo $supplier['contactnumber']; ?>');
		$('#estimated_delivery_date').val('<?php echo $picksheet['estimated_delivery_date']; ?>');
		renderCompleted = true;
	}, 500);
	$('#supplier').keyup(function(){
		var val = $('#supplier').val();
		$('#supplier_search_results').fadeIn();

		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
            $('#supplier_search_results').html(this.responseText);
		}
		};
		xhttp.open("POST", "ajax/getSupplierDropdown.php", true);
		xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xhttp.setRequestHeader('X-CSRF-TOKEN', "<?php echo csrf_token();?>");
		xhttp.send("searchterm=" + val);
	});
	var renderCompleted = false;
	function setSupplierDetails(supplier_id, empty='false'){
		supplierID = supplier_id;
		$.get( "ajax/getSupplierAddress.php?src=salesconfirmation&address_id=1&id=" + supplier_id + '&empty=' + empty, function( data ) {
			$('#address').html(data);
			$('.rating').fadeIn();
		});
	}
    function parseDMY(value) {
		var date = value.split("/");
		var d = parseInt(date[0], 10),
			m = parseInt(date[1], 10),
			y = parseInt(date[2], 10);
		return new Date(y, m - 1, d);
	}
    var delCheckingOn		= <?php echo ($supplier['delivery_day_checking'] == 1 && $supplier['delivery_day_override'] == 0)?"true":"false"; ?>;
	var delDays				= <?php echo ($supplier['delivery_days']>0)?$supplier['delivery_days']:0; ?>;
    function ddChanged(dateText){
		// var dateObj = $('#estimated_delivery_date').datepicker('getDate');
		// if (dateObj != null && delCheckingOn){
		// 	var daySelected = dateObj.getDay();
		// 	var weekday = 		["Sunday"	,"Monday"	,"Tuesday"	,"Wednesday","Thursday"	,"Friday"	,"Saturday"	];
		// 	var weekdayLookup = [1			,64			,32			,16			,8			,4			,2			];
		// 	var weekdayInt = weekdayLookup[daySelected];
		// }
        // console.log([dateObj,delCheckingOn,weekdayInt,delDays]);
		// if (dateObj != null && delCheckingOn && (weekdayInt & delDays) == 0)
		// {
		// 	day = weekday[daySelected];
		// 	$('#sendfake').attr('disabled', true);
		// 	$('#searcher').attr('disabled', true);
		// 	$('#warning').css('background', "#ff6666");
		// 	$('#warning').css('border', "2px solid #ff0000");
		// 	$('#warning').css('display', "inline-block");
		// 	$('#warning').html("<td align='center' style='height:100%;padding-top:15px;padding-bottom:15px;'>We do not deliver to this supplier on "+day+"s</td>");
		// 	return;
		// }
        // var ubs = $('#ubDate');
        // var temps = $('#temp_id');
        // if (ubs.length == 0 || dateText == null || dateText == "") return;
        // var date = dateObj.getTime();
        // $('#sendfake').prop('disabled',false);
        // var beyondBB = false;
        // for(var x = 0; x < ubs.length; x++){
        //     var ub = ubs[x];
        //     var temp = temps[x];
        //     if (supplierID == "420") break;
        //     if ((ub.innerHTML=="" || temp.innerHTML.trim() != 1))
        //     {
        //         continue;
        //     }
        //     var ubd = parseDMY(ub.innerHTML).getTime();
        //     if (ubd < date)
        //     {
        //         $(ub).css('background', "#ff6666");
        //         beyondBB = true;
        //     }
        //     else
        //     {
        //         $(ub).css('background', "#ffffff");
        //     }
        // }
        // if (beyondBB)
        // {
        //     $('#sendfake').prop('disabled',true);
        //     $('#warning').css('background', "#ff6666");
        //     $('#warning').css('border', "2px solid #ff0000");
        //     $('#warning').css('display', "inline-block");
        //     $('#warning').html("<td align='center' style='height:100%;padding-top:15px;padding-bottom:15px;'>An item in this return will expire before delivery</td>");
        // }
        // else
        // {
        //     $('#warning').css('display', "none");
        //     $('#sendfake').attr('disabled', false);
        //     $('#searcher').attr('disabled', false);
        // }
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
	// function changeAddress(supplier_id, address_id){
	// 	$('#addressid').val(address_id);
	// 	$.get("ajax/getSupplierAddress.php?src=salesconfirmation&id=" + supplier_id + '&address_id=' + address_id, function(data, status){
	// 		$('#address').html(data);
	// 		$('.lity-close').trigger('click');
	// 	});
	// }
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
	.createSupplierContainer{
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
