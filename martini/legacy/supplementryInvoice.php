<?php
	include('includes/frontHeader.php');
?>
<div id="top">
	<a href="menu.php" id="menu">MENU</a>
	<a href="logout" id="logout">LOGOUT</a>
</div>


<form id="pickerForm" method="POST" action="scripts/buildPicker2.php" onkeydown="if(event.key == 'Enter'){ $('#sendfake').trigger('click'); return false; } else{ return event.key }" autocomplete="off">
<input autocomplete="off" name="hidden" type="text" style="display:none;">
<input type="hidden" name="addressid" id="addressid" value="1">
<div class="container container--pt">
	<div class="row" style="padding-top: 15px;">
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

	<div class="row">
		<div class="col">
			<label>Picksheet Notes</label><br/>
			<textarea class="form-control" id="picksheet_note" name="picksheet_note" style="height:85px;padding:10px;resize:none;"></textarea>
		</div>
		<div class="col">
			<label>Type</label><br/>
			<select id="sup_type" name="sup_type" class="form-control">
			<option value="invoice" selected>Invoice</option>
			<option value="credit">Credit</option>
			</select>
</div>
	</div>

	<div class="row">
		<div class="col">
			<label>	Reference</label><br/>
			<input class="form-control" type="text" class="inputbox" id="orderReferenceNumber" name="orderReferenceNumber" value="<?php echo $row['orderReferenceNumber']; ?>">
		</div>
		<div class="col"></div>
	</div>

	<?php if($user['allow_override_salesman'] == 0){ ?>
		<input type="hidden" id="sales_person" name="sales_person" value="<?php echo $userid; ?>">
	<?php }else{ ?>
	<div class="row">
		<div class="col">
			<label> Salesperson</label><br />
			<select id="sales_person" name="sales_person" class="form-control">
				<?php
					$_users = prepareExecuteQuery("SELECT * FROM `users` where 1 in (pages) AND `is_hidden` = 0");

					while ($_user = mysqli_fetch_array($_users)) {
						?><option value="<?php echo $_user['id']; ?>" <?php if($userid == $_user['id']){ echo 'selected'; } ?>><?php echo $_user['name']; ?></option><?php
					}
				?>
			</select>
		</div>
		<div class="col"></div>
	</div>
	<?php } ?>
	<div class="row custom-warning-box" id="warning" style="width: 100%; display: none; padding-top:0px; padding-bottom:0px;  padding: left right 15px;"></div>
</div>
<div class="leftPanel" style="position:relative;">
    <form id="searchForm">
    <input type="text" name="itemname" id="itemname" placeholder="Item Description" style="width:70%;height: 33px;padding-left: 10px;">
    <input type="number" name="itemweight" id="itemweight" placeholder="Unit Weight" step=".001" style="width:100px;height: 33px;padding-left: 10px;">
    <input type="number" name="itemamount" id="itemamount" placeholder="Units" step="1" style="width:100px;height: 33px;padding-left: 10px;">
    <input type="number" name="itemcost" id="itemcost" placeholder="Unit Cost" step=".01" style="width:100px;height: 33px;padding-left: 10px;">
    <input type="button" id="searcher" onclick="add()" value="Add" style="height: 39px;width: 80px;">
    </form>
</div>
<div class="leftPanel">
	<table width="100%" class="basketTable" id="basketTable">
		<tr align="left" style="background:#3FADDD;height:30px;color:#FFF;">
			<th style="width:80%;">&nbsp;Item Description</th>
            <th>&nbsp;Weight</th>
            <th>&nbsp;Amount</th>
			<th>&nbsp;Unit Cost</th>
            <th>&nbsp;Line Total</th>
			<th>&nbsp;X&nbsp;</th>
		</tr>
	</table>

	<div>
		<br/><br/>
		<div class="totalprice" style="display:none;"></div>
		<br/>
		<input type="submit" value="Send" id="sendreal" class="inputbox-button" style="display:none">
		<input type="hidden" value="<?php use Illuminate\Support\Str;echo Str::random(50);?>" id="transaction_id" name="transaction_id">
		<input type="button" value="Completed" id="sendfake" class="inputbox-button" disabled>
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

        $(document).on('change', 'form #customer', function (e) {
            formHasChanged  = true;
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
	function add() {
        var amount = $('#itemamount').val();
        var weight = $('#itemweight').val();
		var name = $('#itemname').val();
		var cost = $('#itemcost').val();
		items[pos.toString()] = {name:name,cost:cost,amount:amount,weight:weight};
		$('.basketTable').append('<tr id="basketRow-'+pos+'" name="basketRow-'+pos+'"><td>'+name+'</td><td id="weight">'+weight+'</td><td id="amount">'+amount+'</td><td id="price">'+cost+'</td><td>'+(weight*cost*amount)+'</td><td><a href="javascript:;" onclick="deleteRow('+pos+')"><i class="fa fa-trash" aria-hidden="true" style="margin-left:30px;font-size:24px;color:#000;"></i></a></td></tr>');
		pos++;
	}

	function deleteRow(id){
		delete items[id.toString()];
		$('#basketRow-' + id).remove();
	}

	var customerID = null;
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
		var customer_id = $('#customer_id').val();
		var customer = $('#customer').val();
		var date = $('#estimated_delivery_date').val();
		var sellAsUser = $('#sales_person').val();
		var UserSet = false;
		var transaction_id = $('#transaction_id').val();
		var sup_type = $('#sup_type').val();
		dateEntered = false;

		if (customer_id != undefined) {
			customerEntered = true;
			$('#customer').css('border-color', '#f2f2f2');
		} else{
			customerEntered = false;
 			$('#customer').css('border','1px solid red');
		}
		if (sellAsUser != undefined) {
			UserSet = true;
			$('#sales_person').css('border-color', '#f2f2f2');
		} else{
			UserSet = false;
 			$('#sales_person').css('border','1px solid red');
		}
		if (sup_type != undefined) {
			UserSet = true;
			$('#sup_type').css('border-color', '#f2f2f2');
		} else{
			UserSet = false;
 			$('#sup_type').css('border','1px solid red');
		}

		if (date != '') {
			dateEntered = true;
			$('#estimated_delivery_date').css('border-color', '#f2f2f2');
		} else{
			dateEntered = false;
 			$('#estimated_delivery_date').css('border','1px solid red');
		}
		var overOnce = false;
		var ustomerEntered = true;
		var priceEntered = true;
		var pricedCorrectly = true;
		var doneOnce = false;
		$('#price').each(function(index,element){
			doneOnce = true;
 			var value = $(element).val();
			console.log(element);
			return;
			$(element).css('border-color', '#f2f2f2');
			if(!(parseFloat(value) && value > 0)){
				priceEntered = false;
				$(element).css('border','1px solid red');
			}
			else if(parseFloat(value) < parseFloat($(element).attr('cost'))){
				$(element).css('border','1px solid red');
				showPriceCheck = true;
			}
			else if(parseFloat(value) >= (parseFloat($(element).attr('cost'))) * 2){
				$(element).css('border','1px solid red');
				showPriceCheck = true;
			}
		});
		if(doneOnce && customerEntered && dateEntered && priceEntered){
			$.ajax({
				type: 'POST',
				url: 'scripts/buildPicker2.php',
				data:
					{
						"items" : items,
						"customer_id": customer_id,
						"transaction_id": transaction_id,
						"date": date,
						"user": sellAsUser,
						"sup_type": sup_type,
						"picksheet_note": $('#picksheet_note').val(),
						"orderReferenceNumber": $('#orderReferenceNumber').val()
					},
				success: function (data) {
					if (data != "N/A")window.location.href = 'invoice.php?id='+data;
					else alert("An Error Occurred!");
				}
			});
			return false;
		}else{
			if(!customerEntered || !dateEntered || !priceEntered || !UserSet){
				alert('Please complete the missing fields');
			}

			$('#sendfake').prop('disabled', false);

		}
	});
	function mainFormSucess(){
		location.reload();
	}
	function setCustomerDetails(customer_id, empty='false'){
		customerID = customer_id;

		$.get( "ajax/getCustomerAddress.php?id=" + customer_id + '&empty=' + empty, function( data ) {
			$('#address').html(data);
			$('.rating').fadeIn();

			$('#addressline1').prop('readonly', true);
			$('#addressline2').prop('readonly', true);
			$('#addressline3').prop('readonly', true);
			$('#addressline4').prop('readonly', true);
			$('#addresspostcode').prop('readonly', true);
			$('#deliverynumber').prop('readonly', true);

			if (!transactionAllowed || showWarning)
			{
				if (!showWarning)
				{
					$('#sendfake').attr('disabled', true);
					$('#searcher').attr('disabled', true);
					$('#warning').css('background', "#ff6666");
					$('#warning').css('border', "2px solid #ff0000");
				}
				else if (showHigherWarning)
				{
					$('#sendfake').attr('disabled', false);
					$('#searcher').attr('disabled', false);
					$('#warning').css('background', "#ff6666");
					$('#warning').css('border', "2px solid #ff0000");
				}
				else
				{
					$('#sendfake').attr('disabled', false);
					$('#searcher').attr('disabled', false);
					$('#warning').css('background', "#ffc266");
					$('#warning').css('border', "2px solid #ff9900");
				}
				$('#warning').css('display', "inline-block");
				$('#warning').html(warningMessage);
			}
			else
			{
				$('#warning').css('background', "#90EE90");
				$('#warning').css('border', "2px solid #00FF00");
				$('#warning').css('display', "inline-block");
				$('#warning').html(warningMessage);
				$('#sendfake').attr('disabled', false);
				$('#searcher').attr('disabled', false);
			}
		});
	}
	$(document).ready(function(){
		$( "#estimated_delivery_date" ).datepicker({
			dateFormat: 'dd/mm/yy'
		});


	});

	function parseDMY(value) {
		var date = value.split("/");
		var d = parseInt(date[0], 10),
			m = parseInt(date[1], 10),
			y = parseInt(date[2], 10);
		return new Date(y, m - 1, d);
	}
	function setCustomer(customer_id, text){
		$('#customer_search_results').fadeOut();
		$('#customer_id').val(customer_id);
		$('#customer').val(text);
		setCustomerDetails(customer_id);
	}
	$('#customer').keyup(function(){
		var val = $('#customer').val();
		$('#customer_search_results').fadeIn();

		var request = $.ajax({
			type: "POST",
			url: "ajax/getCustomerDropdown.php",
			data: {
				searchterm: val
			},
			dataType: "html"
		});

		request.done(function(data) {
			$('#customer_search_results').html(data);
 		});

		request.fail(function(jqXHR, textStatus) {
			// alert( "Request failed: " + textStatus );
		});

	});



	function changeAddress(customer_id, address_id){

		$('#addressid').val(address_id);


		$.get("ajax/getCustomerAddress.php?id=" + customer_id + '&address_id=' + address_id, function(data, status){
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
