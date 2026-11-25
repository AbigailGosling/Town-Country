<?php

use App\Models\CollectionReturnMetadata;
use App\Models\User;

	include('includes/frontHeader.php');

    $recordId = (int) request()->input('id', 0);
    $collectionRecord = $recordId > 0 ? CollectionReturnMetadata::with('transportPallet')->find($recordId) : null;
?>
<div id="top">
	<a href="menu.php" id="menu">MENU</a>
	<a href="logout" id="logout">LOGOUT</a>
</div>

<?php if ($collectionRecord) { ?>
<div class="container container--pt" style="margin-bottom: 18px;">
    <div class="row" style="border:1px solid #d6d6d6; background:#f7f7f7; padding:16px;">
        <div class="col" style="flex:1;">
            <strong>Collection / Return record</strong><br>
            ID: <?php echo (int) $collectionRecord->id; ?>
            | Type: <?php echo htmlspecialchars($collectionRecord->type_selector === 'collection' ? 'Supplier Collection' : 'Customer Return'); ?>
            | Transport Pallet: <?php echo (int) ($collectionRecord->transport_pallet_id ?? 0); ?>
        </div>
        <div class="col" style="flex:1;">
            Client: <?php echo htmlspecialchars((string) ($collectionRecord->client_id ?: 'n/a')); ?>
            | Invoice: <?php echo htmlspecialchars((string) ($collectionRecord->invoice_id ?: 'n/a')); ?>
            | Date: <?php echo htmlspecialchars($collectionRecord->estimated_delivery_date ? date('d/m/Y', strtotime($collectionRecord->estimated_delivery_date)) : '-'); ?>
        </div>
    </div>
</div>
<?php } ?>

<form id="pickerForm" method="POST" action="scripts/buildPicker2.php" onkeydown="if(event.key == 'Enter'){ $('#sendfake').trigger('click'); return false; } else{ return event.key }" autocomplete="off">
<input autocomplete="off" name="hidden" type="text" style="display:none;">
<input type="hidden" name="addressid" id="addressid" value="1">
<input type="hidden" name="customer_id" id="customer_id" value="">
<input type="hidden" name="supplier_id" id="supplier_id" value="">
<input type="hidden" name="invoice_id" id="invoice_id" value="">
<input type="hidden" name="is_supplier" id="is_supplier" value="0">
<div class="container container--pt">
    <div class="row">
		<div class="col">
			<label>Type</label><br/>
			<select id="type_selector" name="type_selector" class="form-control">
			<option value="return" selected>Customer Return</option>
            <option value="collection">Supplier Collection</option>
			</select>
        </div>
        <div class="col" id="invoice_block" style="display:none;">
			<label>Invoice</label><br/>
			<input class="form-control" type="text" id="invoice" class="inputbox">
			<div id="invoice_search_results" style="position:relative;z-index:99999;"></div>
		</div>
	</div>
	<div class="row">
		<div class="col" id="customer_block">
			<label>Customer</label><br/>
			<input class="form-control" type="text" id="customer" class="inputbox" required>
			<div id="customer_search_results" style="position:relative;z-index:99999;"></div>
		</div>
		<div class="col" id="supplier_block" style="display:none;">
			<label>Supplier</label><br/>
			<input class="form-control" type="text" id="supplier" class="inputbox">
			<div id="supplier_search_results" style="position:relative;z-index:99999;"></div>
		</div>
		<div class="col">
			<label>Collection Date</label><br/>
			<input class="form-control" type="text" class="inputbox" id="estimated_delivery_date" name="estimated_delivery_date" placeholder="">
		</div>

	</div>

	<div class="row" id="address"></div>

	<div class="row">
		<div class="col">
			<label>Return Notes</label><br/>
			<textarea class="form-control" id="picksheet_note" name="picksheet_note" style="height:85px;padding:10px;resize:none;"></textarea>
		</div>
		<div class="col">
			<label>Goods Notes</label><br/>
			<textarea class="form-control" id="goods_out_note" name="goods_out_note" style="height:85px;padding:10px;resize:none;"></textarea>
		</div>
	</div>

	<div class="row">
		<div class="col">
			<label>	Reference</label><br/>
			<input class="form-control" type="text" class="inputbox" id="orderReferenceNumber" name="orderReferenceNumber" value="<?php echo $row['orderReferenceNumber']; ?>">
		</div>
        <div class="col">
            <?php if($user['allow_override_salesman'] == 0){ ?>
                <input type="hidden" id="sales_person" name="sales_person" value="<?php echo $userid; ?>">
            <?php }else{ ?>
                <label> Salesperson</label><br />
                <select id="sales_person" name="sales_person" class="form-control">
                    <?php
                        $newUsers = User::where([["disabled",false],["is_hidden",false]]);
                                $newUsers= $newUsers->orWhere("id",$userid);
                                $newUsers = $newUsers->get()->pluck("id")->toArray();
                        $_users = prepareExecuteQuery("SELECT * FROM `users` where 1 in (pages) AND `id` IN (".implode(",",$newUsers).")");

                        while ($_user = mysqli_fetch_array($_users)) {
                            ?><option value="<?php echo $_user['id']; ?>" <?php if($userid == $_user['id']){ echo 'selected'; } ?>><?php echo $_user['name']; ?></option><?php
                        }
                    ?>
                </select>
            <?php } ?>
        </div>
	</div>
	<div class="row custom-warning-box" id="warning" style="width: 100%; display: none; padding-top:0px; padding-bottom:0px;  padding: left right 15px;"></div>
</div>
<div class="leftPanel" style="position:relative;">
    <div id="invoice_items_container" style="display:none; margin-top:20px;"></div>
    <form id="searchForm">
    <div id="supplier_item_builder" style="display:none;">
        <table style="border-collapse: collapse; width:100%;">
            <tr>
                <td style="width:19%"><select id="SearchSpecies" style="min-width:100px;width:100%;height:40px;text-overflow: ellipsis; border-radius: 0;">
                    <option value="" selected>Select species..</option>
                    <?php
                        $x = "SELECT * FROM `species`";
                        $y = prepareExecuteQuery($x);
                        while($row = mysqli_fetch_array($y)){
                        ?><option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option><?php
                        }
                    ?>
                </select></td>
                <td style="width:19%"><select id="SearchCutgroups" style="min-width:100px;width:100%;height:40px;text-overflow: ellipsis; border-radius: 0;">
                    <option value="" selected>Select subcat...</option>
                    <?php
                        $x = "SELECT * FROM `cutgroups`";
                        $y = prepareExecuteQuery($x);
                        while($row = mysqli_fetch_array($y)){
                            $thisid = $row['species_id'];
                            $y2 = prepareExecuteQuery("SELECT * FROM species WHERE id=?",'i',[$thisid]);
                            $species = mysqli_fetch_array($y2);
                            ?><option style="display:none;" class="allsoption s<?php echo $species['id']; ?>" value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option><?php
                        }
                    ?>
                </select></td>
                <td style="width:19%"><select id="SearchBrand" style="min-width:100px;width:100%;height:40px;text-overflow: ellipsis; border-radius: 0;">
                    <option value="" selected>Select Brand..</option>
                    <?php
                        $x = "SELECT * FROM `brands` WHERE `deleted` = 0 ORDER BY `name`";
                        $y = prepareExecuteQuery($x);
                        while($row = mysqli_fetch_array($y)){
                        ?><option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option><?php
                        }
                    ?>
                </select></td>
                <td style="width:19%"><select id="SearchNationality" style="min-width:100px;width:100%;height:40px;text-overflow: ellipsis; border-radius: 0;">
                    <option value="" selected>Select Nationality..</option>
                    <?php
                        $x = "SELECT * FROM `nationality` ORDER BY `name`";
                        $y = prepareExecuteQuery($x);
                        while($row = mysqli_fetch_array($y)){
                        ?><option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option><?php
                        }
                    ?>
                </select></td>
                <td style="width:19%"><select id="temperatureID" style="min-width:100px;width:100%;height:40px;text-overflow: ellipsis; border-radius: 0;">
                    <option value="" selected>Select Tempurature..</option>
                    <?php
                        $x = "SELECT * FROM `temperature` ORDER BY `temperature`";
                        $y = prepareExecuteQuery($x);
                        while($row = mysqli_fetch_array($y)){
                        ?><option value="<?php echo $row['id']; ?>"><?php echo $row['temperature']; ?></option><?php
                        }
                    ?>
                </select></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td><input type="text" id="supplier_item_date_from" placeholder="Dated From" style="width:100%;height: 33px;padding-left: 10px; border-radius: 0;"></td>
                <td><input type="text" id="supplier_item_date_to" placeholder="Dated To" style="width:100%;height: 33px;padding-left: 10px; border-radius: 0;"></td>
                <td><input type="number" id="supplier_item_weight" placeholder="Weight" step=".001" style="width:100%;height: 33px;padding-left: 10px; border-radius: 0;"></td>
                <td><input type="number" id="supplier_item_quantity" placeholder="Quantity" step="1" min="1" style="width:100%;height: 33px;padding-left: 10px; border-radius: 0;"></td>
                <td><select id="supplier_item_unit" style="width:100%;height:40px;text-overflow: ellipsis; border-radius: 0;">
                    <option value="" selected disabled>Select Unit..</option>
                    <option value="C">Cases</option>
                    <option value="PPC">Purchase Per Case</option>
                    <option value="P">G/T</option>
                </select></td>
                <td style="width:19%"><input type="number" id="supplier_item_price" placeholder="Price" step=".01" min="0" style="width:100%;height: 33px;padding-left: 10px; border-radius: 0;"></td>
                <td><input type="button" id="supplier_add_item" onclick="addSupplierBuiltItem()" value="Add Item" style="height: 39px;width: 100px;float:right;border:2px solid darknavy; border-radius: 0;"></td>
            </tr>
        </table>
    </div>
    </form>
</div>
<div class="leftPanel">
	<table width="100%" class="basketTable" id="basketTable">
		<tr align="left" style="background:#3FADDD;height:30px;color:#FFF;">
			<th>Intake ID</th>
			<th>Plt ID</th>
			<th width="45%">Product</th>
			<th align="right">Dated</th>
			<th>Nationality</th>
			<th>Brand</th>
			<th>Quantity</th>
			<th>Unit</th>
			<th>Price</th>
			<th>&nbsp;X&nbsp;</th>
		</tr>
	</table>

	<div>
		<br/><br/>
		<div class="totalprice" style="display:none;"></div>
		<br/>
		<input type="submit" value="Send" id="sendreal" class="inputbox-button" style="display:none">
		<input type="hidden" value="<?php use Illuminate\Support\Str;echo Str::random(50);?>" id="transaction_id" name="transaction_id">
		<input type="button" value="Completed" id="sendfake" class="inputbox-button">
	</div>
</div>
</form>
<script type="text/javascript" src="js/modal-dialog.js"></script>
<div class="clearfix"></div>
<style type="text/css">




	.rightPanel {
		padding:50px;
		position:relative;
		margin-top:40px;
	}
	.leftPanel{
		height:100%;
		padding:30px;
		border:1px solid #f4f4f4;
		position:relative;
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
		min-height: 400px;
		border: 2px dashed #cacaca;
		padding: 0px;
		margin-top: 20px;
		padding-top: 14px;
	}

	.searchRContent {
		border-collapse: collapse;
		text-align: center;
		font-size: 14px;
		table-layout: fixed;
		width: 100%;
	}

	.searchRContent__head {
		border-bottom: 1px solid #d9d9d9;
		font-size: 14px;
	}

	.searchRContent__head th {
		padding-bottom: 10px;
	}

	.searchRContent__icon {
		font-size: 14px;
	}

	.searchRContent .bold {
		font-size: 16px;
		font-weight: bold;
		padding: 0 5px;
	}

	.searchAccordTitle:nth-child(odd) {
		background: #f2f2f2;
	}

	.searchAccordTitle:nth-child(event) .overviewcomment {
		border: 1px solid #f2f2f2;
	}

	.searchAccordTitle td {
		border: 0;
		padding: 0;
	}

	.location-input {
		width: 80px;
	}

	.searchRContent__plus {
		width: 28px;
	}

	.searchRContent__product {
		width: 180px;
	}

	@media only screen
	and (min-device-width : 768px)
	and (max-device-width : 1024px)  {
		.searchRContent {
			font-size: 10px
		}

		.searchRContent__head {
			font-size: 12px;
		}

		.searchRContent .bold {
			font-size: 14px;
		}

			.searchRContent__id {
		width: 48px;
		}
		.searchRContent__location {
			width: 60px;
		}

		.searchRContent__dropdown {
			width: 20px;
		}

		.searchRContent__amount {
			width: 55px;
		}

		.searchRContent__chill {
			width: 40px;
		}

		.searchRContent__product {
			width: 140px;
		}

		.searchRContent__date-range {
			width: 70px;
		}

		.location-input {
			width: 50px;
		}
	}

	.subrow {
		height: 58px;
		background:#d9d9d9;
	}

	.subrow:hover {
		background: #979797;
		border: 1px solid #000;
	}

</style>
<script type="text/javascript">
	var transactionAllowed = false;
	var showWarning = false;
	var showHigherWarning = false;
	var warningMessage = "";
	$.ajaxSetup({
		headers: { 'X-CSRF-TOKEN': "<?php echo csrf_token();?>" }
	});
    $(document).ready(function() {
        var formHasChanged = false;
        var submitted = false;

		document.getElementById('menu').addEventListener('click', function(e) {
			if (formHasChanged && !submitted) {
				e.preventDefault()
				changePage('menu')
			}
		})

        $(document).on('change', 'form #supplier', function (e) {
            formHasChanged  = true;
        });
        $(document).on('change', 'form #type_selector', function (e) {
            clearBasket();
            typeSelect($(this).val());
        });

        $("#pickerForm").submit(function() {
            submitted = true;
        });

		$(document).on('click', '.intakeLink', function(e) {
			if (formHasChanged && !submitted) {
				e.preventDefault()
				changePage($(this).attr('id'))
			}
		})

		function changePage(prop) {
			var alert = confirm('Are you sure you want to leave?')

			if (alert === true) {
				if (prop == 'menu') {
					window.location.href = "menu.php"
				} else {
					window.location.href = '/intake.php?id=' + prop + '&ref=salesconfirmationsheet'
				}
			}
		}

    });
	var pos = 0;
	var items = [];
	function appendBasketRow(rowId, details) {
		$('.basketTable').append('<tr id="basketRow-'+rowId+'" name="basketRow-'+rowId+'">' +
			'<td>'+details.intakeId+'</td>' +
			'<td>'+details.palletId+'</td>' +
			'<td><b>'+details.product+'</b></td>' +
			'<td>'+details.dated+'</td>' +
			'<td>'+details.nationality+'</td>' +
			'<td>'+details.brand+'</td>' +
			'<td>'+details.quantity+'</td>' +
			'<td>'+details.unit+'</td>' +
			'<td>£'+Number(details.price).toFixed(2)+'</td>' +
			'<td><a href="javascript:;" onclick="deleteRow('+rowId+')"><i class="fa fa-trash" aria-hidden="true" style="margin-left:30px;font-size:24px;color:#000;"></i></a></td>' +
		'</tr>');
	}

	function add() {
        var amount = $('#itemamount').val();
        var weight = $('#itemweight').val();
		var name = $('#itemname').val();
		var cost = $('#itemcost').val();
		var unit = 'p/KG';
		var quantity = (amount === '' || amount === null) ? 0 : Number(amount);
		var unitPrice = (cost === '' || cost === null) ? 0 : Number(cost);
		items[pos.toString()] = {name:name,cost:cost,amount:amount,weight:weight, unit: unit};
		appendBasketRow(pos, {
			intakeId: '-',
			palletId: '-',
			product: name,
			dated: '-',
			nationality: 'N/A',
			brand: 'N/A',
			quantity: quantity,
			unit: unit,
			price: unitPrice
		});
		pos++;
	}

	function deleteRow(id){
		delete items[id.toString()];
		$('#basketRow-' + id).remove();
	}

	function clearBasket() {
		items = [];
		pos = 0;
		$('#basketTable tr:gt(0)').remove();
	}

	function addSupplierBuiltItem() {
		var speciesText = $('#SearchSpecies option:selected').text();
		var cutText = $('#SearchCutgroups option:selected').text();
		var nationalityText = $('#SearchNationality option:selected').text();
		var brandText = $('#SearchBrand option:selected').text();
		var datedFrom = $('#supplier_item_date_from').val();
		var datedTo = $('#supplier_item_date_to').val();
		var dated = '';
		var quantity = Number($('#supplier_item_quantity').val() || 0);
		var unit = $('#supplier_item_unit').val();
		var price = Number($('#supplier_item_price').val() || 0);
		var weight = Number($('#supplier_item_weight').val() || 0);

		if (!speciesText || speciesText === 'Select species..' || !cutText || cutText === 'Select subcat...') {
			alert('Please select species and subcategory.');
			return;
		}
		if (quantity <= 0) {
			alert('Please enter a valid quantity.');
			return;
		}
		if (price < 0) {
			alert('Please enter a valid price.');
			return;
		}
		if (datedFrom !== '' || datedTo !== '') {
			dated = datedFrom + '-' + datedTo;
		}

		var product = $.trim(speciesText + ' ' + cutText);
		items[pos.toString()] = {
			product_id: Number($(this).data('product-id') || 0),
			name: product,
			cost: price,
			amount: quantity,
			weight: weight > 0 ? weight : 1,
			unit: unit
		};

		appendBasketRow(pos, {
			intakeId: '-',
			palletId: '-',
			product: product,
			dated: dated || '-',
			nationality: (!nationalityText || nationalityText === 'Select Nationality..') ? 'N/A' : nationalityText,
			brand: (!brandText || brandText === 'Select Brand..') ? 'N/A' : brandText,
			quantity: quantity,
			unit: unit,
			price: price
		});
		pos++;
	}

	$(document).on('click', '.add-invoice-item-btn', function(){
		var selectedQuantity = Number($(this).closest('tr').find('.invoice-item-quantity').val() || 0);
		var quantity = selectedQuantity > 0 ? selectedQuantity : Number($(this).data('quantity') || 0);
		if (quantity <= 0) {
			alert('Please select a valid quantity.');
			return;
		}
		var price = Number($(this).data('price') || 0);
		var product = $(this).data('product') || '';
		var targetWeight = Number($(this).data('target-weight') || 0);
		var row = {
			intakeId: $(this).data('intake-id') || '-',
			palletId: $(this).data('pallet-id') || '-',
			product: product,
			dated: $(this).data('dated') || '-',
			nationality: $(this).data('nationality') || 'N/A',
			brand: $(this).data('brand') || 'N/A',
			quantity: quantity,
			unit: $(this).data('unit') || 'p/KG',
			price: price
		};
		items[pos.toString()] = {
			name: product,
			cost: price,
			amount: quantity,
			weight: targetWeight,
			unit: row.unit
		};
		appendBasketRow(pos, row);
		pos++;
	});

	var supplierID = null;
	var transactionAllowed = false;
	var showWarning = false;
	var showHigherWarning = false;
	var warningMessage = "";
	var infoMessage = "";
	var showPriceCheck = false;
    setTimeout(function(){
        $('.select2-container').css('display', 'none');
        $('.select2-container').first().css('display', 'inline-block');
    }, 10);

	$('#sendfake').click(function(){
		$(this).prop('disabled', true);
		showPriceCheck = false;
		var type_selector = $('#type_selector').val();
		var selected_id = type_selector === 'return' ? $('#customer_id').val() : $('#supplier_id').val();
		var invoice_id = $('#invoice_id').val();
		var date = $('#estimated_delivery_date').val();
		var sellAsUser = $('#sales_person').val();
		var UserSet = false;
		var transaction_id = $('#transaction_id').val();
		dateEntered = false;
		var entityEntered = selected_id != undefined && selected_id != null && selected_id != '';
		var invoiceEntered = type_selector !== 'return' || (invoice_id != undefined && invoice_id != null && invoice_id != '');

		if (type_selector === 'return') {
			if (selected_id != undefined && selected_id != null && selected_id != '') {
				$('#customer').css('border-color', '#f2f2f2');
			} else {
				$('#customer').css('border','1px solid red');
			}
			if (invoiceEntered) {
				$('#invoice').css('border-color', '#f2f2f2');
			} else {
				$('#invoice').css('border','1px solid red');
			}
		} else {
			if (selected_id != undefined && selected_id != null && selected_id != '') {
				$('#supplier').css('border-color', '#f2f2f2');
			} else {
				$('#supplier').css('border','1px solid red');
			}
		}
		if (sellAsUser != undefined) {
			UserSet = true;
			$('#sales_person').css('border-color', '#f2f2f2');
		} else{
			UserSet = false;
 			$('#sales_person').css('border','1px solid red');
		}
		if (type_selector != undefined) {
			UserSet = true;
			$('#type_selector').css('border-color', '#f2f2f2');
		} else{
			UserSet = false;
 			$('#type_selector').css('border','1px solid red');
		}

		if (date != '') {
			dateEntered = true;
			$('#estimated_delivery_date').css('border-color', '#f2f2f2');
		} else{
			dateEntered = false;
 			$('#estimated_delivery_date').css('border','1px solid red');
		}
		var returnNotes = $.trim($('#picksheet_note').val());
		var goodsNotes = $.trim($('#goods_out_note').val());
		var orderReferenceNumber = $.trim($('#orderReferenceNumber').val());
		var returnNotesEntered = returnNotes !== '';
		$('#picksheet_note').css('border', returnNotesEntered ? '1px solid #f2f2f2' : '1px solid red');
		var overOnce = false;
		var priceEntered = true;
		var pricedCorrectly = true;
		var doneOnce = Object.keys(items).some(function(key){ return items[key] != null; });
		if(doneOnce && entityEntered && dateEntered && priceEntered && invoiceEntered && returnNotesEntered){
			var postData = {
				"items" : items,
				"transaction_id": transaction_id,
				"date": date,
				"user": sellAsUser,
				"type_selector": type_selector,
				"picksheet_note": returnNotes,
				"goods_out_note": goodsNotes,
				"orderReferenceNumber": orderReferenceNumber,
				"is_supplier": (type_selector === 'collection' ? 1 : 0)
			};
			if (type_selector === 'return') {
				postData.customer_id = $('#customer_id').val();
				postData.invoice_id = invoice_id;
				postData.invoice = invoice_id;
			} else {
				postData.customer_id = $('#supplier_id').val();
				postData.invoice_id = '';
				postData.invoice = '';
			}
			$.ajax({
				type: 'POST',
				url: 'scripts/buildCollection.php',
				data: postData,
				success: function (data) {
					if (data != "N/A")
                    {
                        alert("Done!");
                        window.location = 'menu.php';
                    }
					else alert("An Error Occurred!");
				}
			});
			return false;
		}else{
			if(!entityEntered || !dateEntered || !priceEntered || !UserSet || !invoiceEntered || !returnNotesEntered){
				alert('Please complete the missing fields');
			}

			$('#sendfake').prop('disabled', false);

		}
	});
	function mainFormSucess(){
		location.reload();
	}
	function setSupplierDetails(supplier_id, empty='false'){
		supplierID = supplier_id;

		$.get( "ajax/getSupplierAddress.php?id=" + supplier_id + '&empty=' + empty, function( data ) {
			getCustomResult = data;
			setSupplierCreditFeedback(data);
		});
	}
    function setSupplierCreditFeedback(data){
		if (data == "") data = getCustomResult;
		$('#address').html(data);
        $('.rating').fadeIn();

        $('#addressline1').prop('readonly', true);
        $('#addressline2').prop('readonly', true);
        $('#addressline3').prop('readonly', true);
        $('#addressline4').prop('readonly', true);
        $('#addresspostcode').prop('readonly', true);
        $('#deliverynumber').prop('readonly', true);
	}

	function setCustomer(customer_id, text){
		$('#customer_search_results').fadeOut();
		$('#customer_id').val(customer_id);
		$('#customer').val(text);
		$('#invoice_search_results').fadeOut().html('');
		$('#invoice').val('');
		$('#invoice_id').val('');
		$('#invoice_items_container').hide().html('');
	}

	function setInvoice(invoice_id){
		$('#invoice_search_results').fadeOut();
		$('#invoice_id').val(invoice_id);
		if (invoice_id != '') {
			$('#invoice').val(invoice_id);
			$.get('ajax/getInvoiceItems.php', { invoice_id: invoice_id }, function(data) {
				$('#invoice_items_container').html(data).show();
			});
		} else {
			$('#invoice_items_container').hide().html('');
		}
	}

	function typeSelect(type)
	{
		var isReturn = type === 'return';
		$('#is_supplier').val(isReturn ? 0 : 1);
		$('#customer_block').toggle(isReturn);
		$('#supplier_block').toggle(!isReturn);
		$('#invoice_block').toggle(isReturn);
		$('#manual_item_builder').toggle(isReturn);
		$('#supplier_item_builder').toggle(!isReturn);
		$('#customer_search_results').hide().html('');
		$('#supplier_search_results').hide().html('');
		$('#invoice_search_results').hide().html('');
		$('#customer').val('');
		$('#supplier').val('');
		$('#invoice').val('');
		$('#customer_id').val('');
		$('#supplier_id').val('');
		$('#invoice_id').val('');
		$('#invoice_items_container').hide().html('');
		$('#customer').prop('required', isReturn);
		$('#supplier').prop('required', !isReturn);
		$('#invoice').prop('required', isReturn);
	}

	$(document).ready(function(){
		$( "#estimated_delivery_date" ).datepicker({
			dateFormat: 'dd/mm/yy'
		});
		$( "#supplier_item_date_from" ).datepicker({
			dateFormat: 'dd/mm/yy'
		});
		$( "#supplier_item_date_to" ).datepicker({
			dateFormat: 'dd/mm/yy'
		});
		typeSelect($('#type_selector').val());
		$('#SearchSpecies').change(function(){
			var speciesId = $(this).val();
			$('#SearchCutgroups option').hide();
			$('#SearchCutgroups option:first').show();
			if (speciesId) {
				$('#SearchCutgroups .s' + speciesId).show();
			}
			$('#SearchCutgroups').val('');
		});
	});

	function parseDMY(value) {
		var date = value.split("/");
		var d = parseInt(date[0], 10),
			m = parseInt(date[1], 10),
			y = parseInt(date[2], 10);
		return new Date(y, m - 1, d);
	}
	function setSupplier(supplier_id, text){
		$('#supplier_search_results').fadeOut();
		$('#supplier_id').val(supplier_id);
		$('#supplier').val(text);
		setSupplierDetails(supplier_id);
	}
	$('#customer').keyup(function(){
		var val = $('#customer').val();
		if (val === '') {
			$('#customer_search_results').fadeOut().html('');
			return;
		}
		$('#customer_search_results').fadeIn();

		var request = $.ajax({
			type: "POST",
			url: "ajax/getCustomerDropdown.php",
			data: {searchterm: val},
			dataType: "html"
		});

		request.done(function(data) {
			$('#customer_search_results').html(data);
		});
	});

	$('#invoice').keyup(function(){
		var val = $('#invoice').val();
		var customerId = $('#customer_id').val();
		if (!customerId) {
			$('#invoice_search_results').fadeOut().html('');
			return;
		}
		if (val === '') {
			val = '';
		}
		$('#invoice_search_results').fadeIn();

		var request = $.ajax({
			type: "POST",
			url: "ajax/getInvoiceDropdown.php",
			data: {searchterm: val, customer_id: customerId},
			dataType: "html"
		});

		request.done(function(data) {
			$('#invoice_search_results').html(data);
		});
	});

	$('#supplier').keyup(function(){
		var val = $('#supplier').val();
		if (val === '') {
			$('#supplier_search_results').fadeOut().html('');
			return;
		}
		$('#supplier_search_results').fadeIn();

		var request = $.ajax({
			type: "POST",
			url: "ajax/getSupplierDropdown2.php",
			data: {
				searchterm: val
			},
			dataType: "html"
		});

		request.done(function(data) {
			$('#supplier_search_results').html(data);
 		});

		request.fail(function(jqXHR, textStatus) {
			// alert( "Request failed: " + textStatus );
		});

	});


	function changeAddress(supplier_id, address_id){

		$('#addressid').val(address_id);


		$.get("ajax/getSupplierAddress.php?id=" + supplier_id + '&address_id=' + address_id, function(data, status){
			$('#address').html(data);
			$('.lity-close').trigger('click');
		});
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

.select2-container--default .select2-selection--single{
    height:40px;
    border-radius:0px;
}

.select2-container--default .select2-selection--single .select2-selection__rendered{
    line-height:41px;
}

.select2-results__option:first-child{ display:none; }
</style>
