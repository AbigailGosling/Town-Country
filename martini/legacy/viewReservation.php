<?php

use App\Models\ContainerProduct;
use App\Models\InboundContainer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

	$adv = request()->has("adv");
	$usermodel = User::find(Auth::id());
	if ($adv == false) include_once('includes/frontHeader.php');
	else require_once('functions.php');


	$reservation_id = request()->input('id');

	$x = "SELECT * FROM `reservation` WHERE `id`=?";
	$y = prepareExecuteQuery($x,'i',[$reservation_id]);
	$reservation = mysqli_fetch_array($y);

	$customer_id = $reservation['customer_id'];
	$x1 = "SELECT * FROM `customers` WHERE id=?";
	$y1 = prepareExecuteQuery($x1,'i',[$customer_id]);
	$customer = mysqli_fetch_array($y1);

	$addressNumber = $row['address'.$reservation['address_id'].'_number'];

	$address = $customer['address'.$reservation['address_id'].'_1'];
	if($customer['address'.$reservation['address_id'].'_2']){ $address .= ',&#13;'; }
	$address .= $customer['address'.$reservation['address_id'].'_2'];

	if($customer['address'.$reservation['address_id'].'_3']){ $address .= ',&#13;'; }
	$address .= $customer['address'.$reservation['address_id'].'_3'];

	if($customer['address'.$reservation['address_id'].'_4']){ $address .= ',&#13;'; }
	$address .= $customer['address'.$reservation['address_id'].'_4'];

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
		<h2>Sales Reservation</h2>
	</div>
	<div align="right">
		<h3>Invoice No: <?php echo str_pad($reservation_id, 6, '0', STR_PAD_LEFT); ?></h3>
		<div align="right" class="printhide">
		<a href="javascript:;" onclick="printStuff()">Print</a>
		|
		<a href="javascript:;" onclick="emailStuff()">Email</a>
		</div>
	</div>
</div>

<input autocomplete="off" name="hidden" type="text" style="display:none;">

<form id="pickerForm" method="POST" action="scripts/updateSalesReservation.php" autocomplete="off">

<input autocomplete="off" name="hidden" type="text" style="display:none;">
<input type="hidden" name="picksheetid" id="picksheetid" value="<?php echo $reservation_id; ?>">
<input type="hidden" name="customerid" id="customerid" value="<?php echo $customer_id; ?>">
<input type="hidden" name="address_id" id="address_id" value="<?php echo $reservation['address_id']; ?>">
<div class="container container--pt">
	<div class="row">
		<div class="col">
			<label>Customer</label><br/>
			<input class="form-control" type="text" id="customer" class="inputbox" required>
			<div id="customer_search_results" style="position:relative;z-index:99999;"></div>
		</div>
        <div class="col">
			<label>ETA</label><br/>
			<input class="form-control" type="text" class="inputbox" id="eta" name="eta" placeholder="" onkeydown="return false;">
		</div>

	</div>

	<div class="row" id="address"></div>

	<?php if($reservation['user_id'] != ''){ ?>
	<div class="row">
		<div class="col">
			<label>Salesman</label><br/>
 		 	<select id="" class="form-control" name="user_id">
				<?php
					$_users = prepareExecuteQuery("SELECT * FROM `users` where 1 in (pages)");

					while ($_user = mysqli_fetch_array($_users)) {
						?><option value="<?php echo $_user['id']; ?>" <?php if($reservation['user_id'] == $_user['id']){ echo 'selected'; } ?>><?php echo $_user['name']; ?></option><?php
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
			<textarea class="form-control" name="picksheet_note" style="height:85px;padding:10px;resize:none;"><?php echo $reservation['picksheet_note']; ?></textarea>
		</div>
		<div class="col"></div>
	</div>

	<div class="row">
		<div class="col">
			<label>	Order Reference Number</label><br/>
			<input id="orderReferenceNumber" class="form-control" type="text" class="inputbox" name="orderReferenceNumber" value="<?php echo $reservation['orderReferenceNumber']; ?>">
		</div>
		<div class="col"></div>
	</div>

	<div class="row printhide">
		<div class="col">
			<?php if ($usermodel->hasPermission("change_sale_details")||$usermodel->hasPermission("change_sale_reference")) {?>
			<input type="button" id="sendfake" name="sendfake" onclick="mainForm()" value="Update">
			<?php }?>
		</div>
	</div>
    <div class="row custom-warning-box" id="warning" style="width: 100%; padding-top:0px; padding-bottom:0px;  padding: left right 15px; display:none"></div>
</div>
</form>
<?php
	if ($customer['is_petfood_customer'] == 1) { ?>
        <div style="text-align:center;background:#ffc266;border:2px solid #ff9900;">
        <div style="text-align:center;background:#ffc266;border:2px solid #ff9900;">
        <h4>FEED HYGIENE NUMBER- GB486R1812 PET FOOD NOT FOR HUMAN CONSUMPTION USE AS A PET FOOD ONLY. KEEP APART FROM FOOD. WASH HANDS AND CLEAN TOOLS, UTENSILS AND SURFACES AFTER HANDLING THIS PRODUCT.</h4>
        </div>
        </div>
<?php } ?>
<div class="rightPanel">
	<table width="100%" class="basketTable">
		<tr align="left" style="background:#e6931894;height:30px;color:#FFF;">
			<th>Container ID</th>
			<th width="45%">Product</th>
			<th>Nationality</th>
			<th>Brand</th>
 			<th>Quantity</th>
 			<th>Unit</th>
 			<th>Price</th>
		</tr>
		<?php

			$x = "SELECT * FROM `reservation_product` WHERE `reservation_id`=? AND `deleted` = 0";
			$y = prepareExecuteQuery($x,'i',[$reservation_id]);
			$vars = array();
			while($item = mysqli_fetch_array($y)){
			    $query = "SELECT * FROM `product` WHERE id = ?";

 			    $yproduct = prepareExecuteQuery($query,'i',[$item['product_id']]);

                while($product = mysqli_fetch_array($yproduct)){
                    $containerProduct = ContainerProduct::where([["product_id",$product['id']],["deleted",false]])->first();
                    if ($containerProduct == null) continue;
                    $container = InboundContainer::find($containerProduct->container_id);
                    $containerLabel = $container->internal_number;
                ?>
                <tr class="productsRow">
                        <td align="left"><span class="intakeid"><?php echo $containerLabel; ?></span></td>
                        <td align="left"><b class="species"><?php echo getSpeciesFromCutID($product['cut_id']); echo getCut($product['cut_id']);  ?></b></td>
                        <td align="left"><span class="chilled"><?php echo getNationality($product['nationality_id']); ?></span></td>
                        <td align="left"><b class="brand"><?php echo getBrand($product['brand_id']); ?></b></td>
                        <td align="left">
                            <b class="howmany">
                            <?php echo $item['target_count']; ?>
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
                                echo '£'. number_format((double)$item['price'], 2, '.', '');

                            ?>
                        </td>
                        </tr>

                <?php
                }
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
	$( "#eta" ).datepicker({
			dateFormat: 'dd/mm/yy'
	});
	<?php } ?>
	setCustomerDetails(<?php echo $customer_id; ?>,<?php echo $reservation['address_id']; ?>, 'true');

	setTimeout(() => {
		$('#customer').val('<?php echo $customer['businessname']; ?>');
		$('#contactnumber').val('<?php echo $customer['contactnumber']; ?>');
		$('#eta').val('<?php echo Carbon::createFromFormat("Y-m-d",$reservation['eta'])->format("d/m/Y"); ?>');
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
	function setCustomerDetails(customer_id, address_id, empty='false'){
		customerID = customer_id;
		$.get( "ajax/getCustomerAddress.php?src=salesconfirmation&address_id=" + address_id + "&id=" + customer_id + '&empty=' + empty, function( data ) {
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
    var delCheckingOn		= <?php echo ($customer['delivery_day_checking'] == 1 && $customer['delivery_day_override'] == 0)?"true":"false"; ?>;
	var delDays				= <?php echo ($customer['delivery_days']>0)?$customer['delivery_days']:0; ?>;

    const siteid = '<?php
    $loc = prepareExecuteQuery("SELECT * FROM `pallet` WHERE id=?",'i',[$thispalletid])->fetch_assoc()['storage_location'];
        echo prepareExecuteQuery("SELECT * FROM `location` WHERE id=?",'i',[$loc])->fetch_assoc()['site_id'];?>';
    const sitecutoffLookup = <?php echo json_encode(prepareExecuteQuery("SELECT `id`,`cutoff` FROM `site`")->fetch_all(MYSQLI_ASSOC)); ?>;
    const stockMovementLookup = <?php echo json_encode(prepareExecuteQuery("SELECT * FROM `stock_movements`")->fetch_all(MYSQLI_ASSOC)); ?>;
    function checkNextDayCutoff() {
        var targetCutoff = undefined;
        for (var ll of sitecutoffLookup)
        {
            if (ll.id == siteid)
            {
                targetCutoff = ll.cutoff;
                break;
            }
        }
        if (targetCutoff == undefined) return true;
        var deldate = $('#eta').datepicker('getDate');
        var now = new Date();
        var tomorrow = new Date();
        var tenDays = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        tomorrow.setHours(23,59,59,0);
        tenDays.setDate(tenDays.getDate() + 10);
        tenDays.setHours(0,0,0,0);
        var todaysCutoff = new Date();
        todaysCutoff.setHours(targetCutoff.split(":")[0],targetCutoff.split(":")[1],0,0);

        if (deldate < now)
        {
            $('#sendfake').attr('disabled', true);
			$('#warning').css('background', "#ff6666");
			$('#warning').css('border', "2px solid #ff0000");
			$('#warning').css('display', "inline-block");
			$('#warning').html("<td align='center' style='height:100%;padding-top:15px;padding-bottom:15px;'>Cannot Delivery date in the past</td>");
            return false;
        }
        if (now > todaysCutoff && deldate < tomorrow)
        {
            $('#sendfake').attr('disabled', true);
			$('#warning').css('background', "#ff6666");
			$('#warning').css('border', "2px solid #ff0000");
			$('#warning').css('display', "inline-block");
			$('#warning').html("<td align='center' style='height:100%;padding-top:15px;padding-bottom:15px;'>Cannot sell for Next Day Delivery after "+targetCutoff+"  from this Site</td>");
            return false;
        }
        if (deldate > tenDays)
        {
            $('#sendfake').attr('disabled', true);
            $('#warning').css('background', "#ff6666");
			$('#warning').css('border', "2px solid #ff0000");
			$('#warning').css('display', "inline-block");
			$('#warning').html("<td align='center' style='height:100%;padding-top:15px;padding-bottom:15px;'>Cannot sell ten days into the future</td>");
            return false;
        }
        return true;
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

		$('#address_id').val(address_id);

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
		if ($('#eta').val() == "") {
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
		$.post("ajax/generatePDFsaleconfirm.php", {id: <?php echo $reservation_id; ?>},getRenderResp);
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
