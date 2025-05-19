<?php

use App\Models\Permission;
use App\Models\User;

	include('includes/frontHeader.php');
?>
<div id="top">
	<a href="menu.php" id="menu">MENU</a>
	<a href="logout" id="logout">LOGOUT</a>
</div>


<form id="pickerForm" method="POST" action="scripts/buildPicker.php" onkeydown="if(event.key == 'Enter'){ $('#sendfake').trigger('click'); return false; } else{ return event.key }" autocomplete="off">
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
			<input class="form-control" type="text" class="inputbox" id="estimated_delivery_date" name="estimated_delivery_date" onkeydown="return false;" placeholder="">
		</div>

	</div>

	<div class="row" id="address"></div>

	<div class="row">
		<div class="col">
			<label>Picksheet Notes</label><br/>
			<textarea class="form-control" name="picksheet_note" style="height:85px;padding:10px;resize:none;"></textarea>
		</div>
		<div class="col"></div>
	</div>

	<div class="row">
		<div class="col">
			<label>	Order Reference Number</label><br/>
			<input class="form-control" type="text" class="inputbox" name="orderReferenceNumber" value="<?php echo $row['orderReferenceNumber']; ?>">
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
					$_users = User::where(['disabled'=>0])->orderBy('name')->get();

					foreach ($_users as $_user) {
						if (!$_user->hasPermission(Permission::find(1))) continue;
						?><option value="<?php echo $_user['id']; ?>" <?php if($userid == $_user['id']){ echo 'selected'; } ?>><?php echo $_user['name']; ?></option><?php
					}
				?>
			</select>
		</div>
		<div class="col"></div>
	</div>
	<?php } ?>
    <div class="row">
		<div class="col">
			<label>	Customer Served By</label><br/>
			<input class="form-control" type="text" class="inputbox" name="served_by" id="served_by" value="" disabled>
		</div>
		<div class="col"></div>
	</div>
	<div class="row custom-warning-box" id="warning" style="width: 100%; display: none; padding-top:0px; padding-bottom:0px;  padding: left right 15px;"></div>
</div>

<div class="rightPanel">
	<table width="100%" class="basketTable" id="basketTable">
		<tr align="left" style="background:#3FADDD;height:30px;color:#FFF;">
			<th>Intake ID</th>
			<th>Plt ID</th>
			<th>Site</th>
			<th>Product</th>
			<th>Nationality</th>
			<th>Brand</th>
			<th>Use By</th>
			<th>Volume <span style="display:none;">(num of cases)</span</th>
			<th>Weight</th>
			<th>Sell Price</th>
			<th>Value</th>
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

<div class="leftPanel" style="position:relative;">
	<form id="searchForm">
		<table style="border-collapse: collapse;">
			<tr>
				<td style="width:20%"><select id="SearchSpecies" style="min-width:100px;width:100%;height:40px;text-overflow: ellipsis; border-radius: 0;">
					<option value="" disabled selected>Select species..</option>
					<?php
						$x = "SELECT * FROM `species`";
						$y = prepareExecuteQuery($x);

						while($row = mysqli_fetch_array($y)){
						?><option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option><?php
						}
					?>
					</select>
				</td>
				<td style="width:20%"><select id="SearchCutgroups" name="cutgroup_id" style="min-width:100px;width:100%;height:40px;text-overflow: ellipsis; border-radius: 0;">
						<option sid="<?php echo $rand; ?>" class="header" value="<?php echo $rand; ?>" selected>Select subcat...</option>
						<?php
							$x = "SELECT * FROM `cutgroups`";
							$y = prepareExecuteQuery($x);

							$i=0;
							while($row = mysqli_fetch_array($y)){


								$thisid = $row['species_id'];
								$y2 = prepareExecuteQuery("SELECT * FROM species WHERE id=?",'i',[$thisid]);
								$species = mysqli_fetch_array($y2);
								$rand = 'z' . rand(6000,12212);
									?><option style="display:none;" sid="<?php echo $row['id']; ?>" class="allsoption s<?php echo $species['id']; ?>" value="<?php echo $row['id']; ?>"<?php if(request()->input('acutgroup_id') == $row['id']){ echo 'selected'; } ?>><?php echo $row['name']; ?></option><?php
								}
						?>
					</select>
				</td>
                <td style="width:20%">
                    <select id="siteID" style="min-width:100px;width:100%;height:40px;text-overflow: ellipsis; border-radius: 0;">
					<option value="" disabled selected>Select site..</option>
					<?php
						$x = "SELECT * FROM `site`";
						$y = prepareExecuteQuery($x);

						while($row = mysqli_fetch_array($y)){
						?><option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option><?php
						}
					?>
					</select>
				</td>
				<td style="width:20%"><select id="SearchBrand" style="min-width:100px;width:100%;height:40px;text-overflow: ellipsis; border-radius: 0;">
					<option value="" disabled selected>Select Brand..</option>
					<?php
						$x = "SELECT * FROM `brands` ORDER BY `name`";
						$y = prepareExecuteQuery($x);

						while($row = mysqli_fetch_array($y)){
						?><option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option><?php
						}
					?>
					</select>
				</td>
				<td style="width:20%">
					<select id="SearchNationality" style="min-width:100px;width:100%;height:40px;text-overflow: ellipsis; border-radius: 0;">
						<option value="" disabled selected>Select Nationality..</option>
						<?php
							$x = "SELECT * FROM `nationality` ORDER BY `name`";
							$y = prepareExecuteQuery($x);

							while($row = mysqli_fetch_array($y)){
							?><option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option><?php
							}
						?>
					</select>
				</td>
				<td style="width:20%">
					<select id="SearchTime" style="min-width:100px;width:100%;height:40px;text-overflow: ellipsis; border-radius: 0;">
						<option value="0" disabled selected>Select Time Sensitivity..</option>
						<!--<option value="0">Green</option>-->
						<option value="1">Amber Warning</option>
						<option value="2">Red Warning</option>
					</select>
				</td>
				<td></td>
				<td><input type="number" name="intake_id" id="IntakeID" placeholder="Intake ID" style="width:65px;height: 33px;padding-left: 10px; border-radius: 0;"></td>
				<td><input type="number" name="pallet_id" id="PalletID" placeholder="Pallet ID" style="width:65px;height: 33px;padding-left: 10px; border-radius: 0;"></td>
				<td><input type="button" id="searcher" onclick="doSearch()" value="Search" style="height: 39px;width: 80px;float:right;border:2px solid darknavy; border-radius: 0;" disabled></td>
			</tr>
		</table>

		</form>


    <div class="weightTotal" style="display:none;">Total Weight: <span class="weightVal">0</span>kg</div>

	<div id="loadResults" class="resultsContainer"></div>
</div>

<script type="text/javascript" src="js/modal-dialog.js"></script>
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
	var transactionAllowed = false;
	var showWarning = false;
	var showHigherWarning = false;
	var warningMessage = "";
	var customerID = null;
	var infoMessage = "";
	var showPriceCheck = false;
	var delCheckingOn = false;
	var delDays = 0;
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
					window.location.href = 'intake.php?id=' + prop + '&ref=salesconfirmationsheet'
				}
			}
		}

    });

	function addToList(id){

		$.get( "scripts/getBasketItem.php?id="+id, function( data ) {
			$('.basketTable').append(data);
            setCustomerCreditFeedback();
		});

	}

	function removeFromList(id, pallet_id, product_id){
		$('.basketRow-' + id).remove();
		var COOKIE_NAME = "quantity-"+product_id+"-"+pallet_id;
		checkUBDates();
		document.cookie = COOKIE_NAME + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
	}


function checkStock(){
	modalDialog.showMask();
    var readyToSubmit = 1;

    var group = $('input[name="basketRow[]"]');
	var allPass = true;
	var target = group.length;

	group.each(function (index) {
		var value = $(this).val();
		var bits = value.split('-');
		var product_id = bits[0];
		var quantity_wanted = bits[1];

		$.get("ajax/checkProductStockQuantity.php?product_id=" + product_id, function(num, status){
			var product_stock_count = parseInt(num);

			if(quantity_wanted <= product_stock_count){

			}else{
				allPass = false;
				$('.product' + product_id).css('background-color','red');
			}
			target--;
		});
	});

	var intervalPoll = setInterval(function() {
		if (target>0)return;
		else clearInterval(intervalPoll);
		if (!allPass)
		{
			modalDialog.hideMask();
			Swal.fire({
					title: "Some of the selected items are already sold",
					text: "Please search stock again to view available items",
					icon: "warning",
					allowOutsideClick: false,
					allowEscapeKey: false,
					showCancelButton: false,
					showConfirmButton: false,
					showCloseButton: true
				});

			$('#sendfake').prop('disabled', false);
		}
		else
		{
			$('#pickerForm').ajaxSubmit({headers:{'X-CSRF-TOKEN': "<?php echo csrf_token();?>"},success:finalSaleSucess,error:finalSaleFailure});
		}

	},5)

}
function completeSale()
{
	modalDialog.showMask();
	$('#pickerForm').ajaxSubmit({headers:{'X-CSRF-TOKEN': "<?php echo csrf_token();?>"},success:finalSaleSucess,error:finalSaleFailure});
}
function finalSaleSucess()
{
	alert("Done!");
	window.location = 'menu.php';
}
function finalSaleFailure()
{
	alert("An Error Occurred! Please check for a duplicate sale!");
	window.location = 'salesconfirmationList.php';
}
function cancelSale()
{
	$('#sendfake').prop('disabled', false);
}
</script>

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
			width: 80px;
		}

		.searchRContent__dropdown {
			width: 20px;
		}

		.searchRContent__unit {
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

    setTimeout(function(){
        $('.select2-container').css('display', 'none');
        $('.select2-container').first().css('display', 'inline-block');
    }, 10);
	$.ajaxSetup({
		headers: { 'X-CSRF-TOKEN': "<?php echo csrf_token();?>" }
	});

	ready = true;
	setInterval(function(){
		ready = true;
		var totalPrice = 0;

		$('.price').each(function(){
			var q = $(this).attr('q');

			if(this.value != ''){
				var finalVal = (parseFloat(this.value)) * q;

				totalPrice += finalVal;
			}else{
				ready = false;
			}

		});


 	}, 300);
	$(document).ready(function(){

		$.each(document.cookie.split(/; */), function(){
		  var splitCookie = this.split('=');


			if(splitCookie[0].includes('quantity-')){
				document.cookie = splitCookie[0] + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
			}
		});

		$( "#estimated_delivery_date" ).datepicker({
			onSelect: ddChanged,
			dateFormat: 'dd/mm/yy'
		});


	});
	function ddChanged(dateText, inst){
		if (customerID == null) checkUBDates(dateText);
		else
		{
			$.get("ajax/getCustomerAddress.php?id=" + customerID  + '&empty=false', function(data){
				getCustomResult = data;
				setCustomerCreditFeedback(data);
			});
		}

	}
	$('#sendfake').click(function(){

		$(this).prop('disabled', true);
		showPriceCheck = false;
		var customer_id = $('#customer_id').val();
		var customer = $('#customer').val();
		var date = $('#estimated_delivery_date').val();
		var sellAsUser = $('#sales_person').val();
		var UserSet = false;
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

		if (date != '') {
			dateEntered = true;
			$('#estimated_delivery_date').css('border-color', '#f2f2f2');
		} else{
			dateEntered = false;
 			$('#estimated_delivery_date').css('border','1px solid red');
		}
		var overOnce = false;
		var underOnce = false;
		var ustomerEntered = true;
		var priceEntered = true;
		var pricedCorrectly = true;
		var doneOnce = false;

		$('.price').each(function(index,element){
			doneOnce = true;
 			var value = $(element).val();
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

		if(checkSites() && doneOnce && customerEntered && dateEntered && priceEntered && UserSet && !showPriceCheck){
			checkStock();
			return false;
		}else{

			if(!customerEntered || !dateEntered || !priceEntered || !UserSet){
				alert('Please complete the missing fields');
			}
			else if (showPriceCheck) {
				modalDialog.showDialog("Pricing Error","There is an issue with the pricing of some of your selections","Continue Sale","Review Prices",completeSale,cancelSale)
			}
			$('#sendfake').prop('disabled', false);

		}
	});
	var getCustomResult;
	var addressID;
	function setCustomerDetails(customer_id, empty='false'){
		customerID = customer_id;

		$.get( "ajax/getCustomerAddress.php?id=" + customer_id + '&empty=' + empty, function( data ) {
			getCustomResult = data;
			setCustomerCreditFeedback(data);
		});
	}
	function setCustomerCreditFeedback(data){
        $('#warning').css('display', "none");
        var canContinue = true;
		if (data == "") data = getCustomResult;
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
            $('#warning').css('display', "inline-block");
            $('#warning').html(warningMessage);
            if (!showWarning)
            {

                $('#warning').css('background', "#ff6666");
                $('#warning').css('border', "2px solid #ff0000");
                canContinue = false;
            }
            else if (showHigherWarning)
            {
                $('#warning').css('background', "#ff6666");
                $('#warning').css('border', "2px solid #ff0000");
            }
            else
            {
                $('#warning').css('background', "#ffc266");
                $('#warning').css('border', "2px solid #ff9900");
            }
        }
        $('#searcher').attr('disabled', !canContinue);
        if (canContinue)
        {
            $('#sendfake').attr('disabled', !(checkAllowedDay() && checkSites() && checkUBDates()));
        }
        else
        {
            $('#sendfake').attr('disabled', true);
        }
    }
    function checkAllowedDay(){
        var dateObj = $('#estimated_delivery_date').datepicker('getDate');
        if (dateObj != null && delCheckingOn){
            var daySelected = dateObj.getDay();
            var weekday = 		["Sunday"	,"Monday"	,"Tuesday"	,"Wednesday","Thursday"	,"Friday"	,"Saturday"	];
            var weekdayLookup = [1			,64			,32			,16			,8			,4			,2			];
            var weekdayInt = weekdayLookup[daySelected];
        }
        if (dateObj != null && delCheckingOn && (weekdayInt & delDays) == 0)
        {
            day = weekday[daySelected];
            $('#warning').css('background', "#ff6666");
            $('#warning').css('border', "2px solid #ff0000");
            $('#warning').css('display', "inline-block");
            $('#warning').html("<td align='center' style='height:100%;padding-top:15px;padding-bottom:15px;'>We do not deliver to this customer on "+day+"s</td>");
            return false;
        }
        return true;
    }
	function checkUBDates(){
        var dateText = $('#estimated_delivery_date').val()
        var ubs = $('#basketTable #ubDate');
        var temps = $('#basketTable #temp_id');
        if (ubs.length == 0 || dateText == null || dateText == "") return true;
        var date = parseDMY(dateText).getTime();
        var beyondBB = false;
        for(var x = 0; x < ubs.length; x++){
            var ub = ubs[x];
            var temp = temps[x];
            if (customerID == "420") break;
            if ((ub.innerHTML=="" || temp.innerHTML.trim() != 1))
            {
                continue;
            }
            var ubd = parseDMY(ub.innerHTML).getTime();
            if (ubd < date)
            {
                $(ub).css('background', "#ff6666");
                beyondBB = true;
            }
            else
            {
                $(ub).css('background', "#ffffff");
            }
        }
        if (beyondBB)
        {
            $('#warning').css('background', "#ff6666");
            $('#warning').css('border', "2px solid #ff0000");
            $('#warning').css('display', "inline-block");
            $('#warning').html("<td align='center' style='height:100%;padding-top:15px;padding-bottom:15px;'>An item in this sale will expire before delivery</td>");
            return false;
        }
        return true;
	}
	function checkSites(){
		var allPass = true;
		var siteid = null;
		var elems = $('#basketTable > tbody > tr');
		for(var i = 0; i < elems.length; i++){
			element = elems[i];
			var classString = element.getAttribute("class");
			if (classString != null) {
				if (siteid == null) {
					siteid = classString.split(" ")[2].replace("siteid","");
				}
				else if (siteid != classString.split(" ")[2].replace("siteid","")){
					allPass = false;
				}
			}
		}
		if (allPass == false){
			$('#warning').css('background', "#ff6666");
			$('#warning').css('border', "2px solid #ff0000");
			$('#warning').css('display', "inline-block");
			$('#warning').html("<td align='center' style='height:100%;padding-top:15px;padding-bottom:15px;'>Cannot sell from multiple sites</td>");
		}
		return (allPass && checkNextDayCutoff(siteid));
	}
    const sitecutoffLookup = <?php echo json_encode(prepareExecuteQuery("SELECT `id`,`cutoff` FROM `site`")->fetch_all(MYSQLI_ASSOC)); ?>;
    const stockMovementLookup = <?php echo json_encode(prepareExecuteQuery("SELECT * FROM `stock_movements`")->fetch_all(MYSQLI_ASSOC)); ?>;
    function checkNextDayCutoff(siteid) {
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
        var deldate = $('#estimated_delivery_date').datepicker('getDate');
        var now = new Date();
        var tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        tomorrow.setHours(23,59,59,0);
        var todaysCutoff = new Date();
        todaysCutoff.setHours(targetCutoff.split(":")[0],targetCutoff.split(":")[1],0,0);

        if (now > todaysCutoff && deldate < tomorrow)
        {
			$('#warning').css('background', "#ff6666");
			$('#warning').css('border', "2px solid #ff0000");
			$('#warning').css('display', "inline-block");
			$('#warning').html("<td align='center' style='height:100%;padding-top:15px;padding-bottom:15px;'>Cannot sell for Next Day Delivery after "+targetCutoff+"  from this Site</td>");
            return false;
        }
        return checkStockMovement(siteid,targetCutoff);
    }
    function checkStockMovement(siteid,targetCutoff){
        var leadtime = 0;
        var deldate = $('#estimated_delivery_date').datepicker('getDate');
        if (siteid != served_by)
        {
            for (var movementrule of stockMovementLookup)
            {
                if (movementrule.origin == siteid && movementrule.destination == served_by)
                {
                    leadtime = movementrule.days;
                    break;
                }
            }
        }
        var leadingDay = new Date();
        var now = new Date();
        now.setHours(23,59,59,0);
        if (leadingDay > targetCutoff)leadtime++;
        leadingDay.setDate(leadingDay.getDate() + (leadtime-1));
        leadingDay.setHours(23,59,59,0);
        while (now < leadingDay)
        {
            if (now.getDay() === 0) leadingDay.setDate(leadingDay.getDate()+1);
            now.setDate(now.getDate()+1);
        }
        if (leadtime > 0 && leadingDay > deldate)
        {
			$('#warning').css('background', "#ff6666");
			$('#warning').css('border', "2px solid #ff0000");
			$('#warning').css('display', "inline-block");
			$('#warning').html("<td align='center' style='height:100%;padding-top:15px;padding-bottom:15px;'>Stock will take at least "+leadtime+" working days to move</td>");
            return false;
        }
        return true;
    }
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

    $('#SearchSpecies').change(function(){

        var thisval = $(this).val();
        $('#SearchCutgroups option.allsoption').hide();
        $('#SearchCutgroups option.s'+thisval).show();

		// iOS fix - display:none doesn't work on select options
		$('#SearchCutgroups option.allsoption').unwrap('span');
        $('#SearchCutgroups option.allsoption').wrap('<span/>');
        $('#SearchCutgroups option.s'+thisval).unwrap();
        //$('#SearchCutgroups').val($('#SearchCutgroups option.s'+thisval).first().attr('sid'));


        var id = $(this).val();

        //doSearch();
	});

	// hide cuts on load
	$('#SearchCutgroups option.allsoption').hide();
	// iOS fix - display:none doesn't work on select options
	$('#SearchCutgroups option.allsoption').wrap('<span/>');

    $('#SearchCutgroups').change(function(){
        var id = $(this).val();

        //doSearch();
    });


	function doSearch(){
		var cut = $('#SearchCut').val();
		var palletID = $('#SearchPallet').val();
		var species = $('#SearchSpecies').val();
		var cutgroup_id = $('#SearchCutgroups').val();
		var brand = $('#SearchBrand').val();
		var nationality = $('#SearchNationality').val();
		var time = $('#SearchTime').val();
 		var temperatureID = $('#temperatureID').val();
 		var intakeID = $('#IntakeID').val();
 		var palletID = $('#PalletID').val();
        var siteID = $('#siteID').val();
		 var customer_id = $('#customer_id').val();
		if(species != '' || cutgroup_id != '' && intakeID != '' || palletID != ''){
			$('#loadResults').html('<center><img src="/legacy/img/loading.gif" style="padding-top:170px;width:40px;text-align:center;"></center>');

			$.get("scripts/searchPicker.php?cutgroup_id=" + cutgroup_id + "&species=" + species +  "&temperatureID=" + temperatureID +  "&palletID=" + palletID + "&intakeID=" + intakeID + "&brandID=" + brand + "&nationalityID=" + nationality + "&time="+time + "&customerID="+customer_id +"&siteID="+siteID , function(data, status){
				$('#loadResults').html(data);

			});
            $('#siteID').prop('selectedIndex',0);
			$('#SearchBrand').prop('selectedIndex',0);
			$('#SearchNationality').prop('selectedIndex',0);
			$('#SearchSpecies').prop('selectedIndex',0);
			$('#SearchCutgroups').prop('selectedIndex',0);
			$('#SearchTime').prop('selectedIndex',0);
			$('#IntakeID').val('');
			$('#PalletID').val('');

			$('.allsoption').hide();
		}else{
			alert('Please fill out the form before searching');
		}
	}

	function ShowWeights(pallet_id,species_id, cut_id){
		// $('#weightsContainer').fadeOut();
		$('.weights' + pallet_id + species_id + cut_id).toggle();
	}

	$('#submitCustomerAccount').click(function(){
		$.ajax({
			type: 'POST',
			url: '/scripts/addCustomer.php',
			data: $('#createCustomerForm').serialize(),
			success: function () {
				$('#createCustomerForm')[0].reset();
				alert('Customer Added - please refresh to see changes!');
			}
		});
	});

	$('#customer').keyup(function(){
		var val = $('#customer').val();
		$('#customer_search_results').fadeIn();

		var request = $.ajax({
			type: "POST",
			url: "ajax/getCustomerDropdown.php",
			data: {
				searchterm: val,
				salescreen: "y"
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
