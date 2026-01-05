<?php

use App\Models\InboundContainer;
use App\Models\Site;

	include('functions.php');
	// $id = request()->input('id');


	// $intake_id = request()->input('id');

    $isContainer = (request()->has("container"));
    if ($isContainer)$container = InboundContainer::findOrFail(request()->input("container"));

	// $intake = getIntake($id);

	// $supplier = getSupplier($intake['supplier_id']);

	$purchase = getPurchase(request()->input('purchaseid'));
	$supplierid = $purchase['supplier_id'];
	$supplier = getSupplier($supplierid);
?>

<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Town &amp; Country</title>
	<link href="css/style.css" rel="stylesheet" type="text/css">
	<link href="css/font-awesome.css" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.min.css">
	<script src="https://code.jquery.com/jquery-1.12.4.js"></script><script src="https://malsup.github.io/jquery.form.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.full.min.js"></script>
</head>
<body>
<div id="top">
	<a href="menu.php" id="menu">MENU</a>
	<a href="logout" id="logout">LOGOUT</a>
</div>
<main class="int">
	<div id="product">
		<div id="product_heading"><?php echo (!$isContainer)?"New Delivery":" Details for Container: ".$container->internal_number ?></div>
		<div id="product_options">
			<a href="javascript:;" onclick="saveDelivery(event)">Save Delivery</a>
		</div>
		<form method="GET" id="mainForm" action="scripts/newDelivery.php">
        <?php if($isContainer){ ?><input type="text" name="container" value="<?php echo $container->id;?>" style="display:none;"><?php } ?>
		<input type="text" name="purchase_id" value="<?php if($purchase['id'] != ''){ echo $purchase['id']; }else{ echo '#'; } ?>" style="display:none;">
		<table>
			<tbody>
				<tr>
					<td><div id="msgNotice" style="color:red;"></div></td>
				</tr>
				<tr>
				<td>
					<label>Supplier</label>

					<input name="supplier_id" id="supplier_id" type="text" style="display:none;" value="<?php echo $supplierid; ?>">
					<input name="supplier_search" id="supplier_search" type="text" value="<?php echo $supplier['name']; ?>" autocomplete="off">
					<div id="supplier_search_results">

					</div>

				</td>
				<td>
					<label>Vehicle Registration</label>
					<input type="text" name="vehicle_reg" id="vehicle_reg"  style="text-transform:uppercase;" placeholder="">
				</td>
				</tr>
				<tr>
				<td>
					<label>Date Recieved</label>
					<input type="text" name="date_received" id="date_received" value="<?php echo date('d/m/Y'); ?>" placeholder="">
				</td>
				<td>
					<label>Vehicle Temp (°C)</label>
					<input type="text" width="184" name="vehicle_temperature" id="vehicle_temperature" placeholder="">
				</td>

				</tr>
				<tr>
				<td>
					<label>Delivery Note Number</label>
					<input type="text" name="delivery_note_number" id="delivery_note_number" value="">
				</td>
                <td>
					<label>Depot</label>
					<select name="site_id" id="secusite_idrity_id" style="width:192px;height:30px;" required>
						<option selected value="0">Please choose below</option>
						<?php
                            foreach (Site::all() as $site)
                            {
                        ?>
						<option value="<?php echo $site->id; ?>" <?php if($site_id == $site->id){ echo 'selected'; } ?>><?php echo $site->name; ?></option>
                        <?php
                            }
                        ?>
					</select>
				</td>
				</tr>
				<tr>
				<td>
					<label>Staff Name</label>
					<input type="text" value="<?php echo getUsername($_SESSION['USER']); ?>" disabled style="display:none;">
					<input type="text" name="staff_id">
				</td>
				<td>
					<label>Security</label>
					<select name="security_id" id="security_id" style="width:192px;height:30px;" required>
						<option selected value="0">Please choose below</option>
						<?php
							$x = "SELECT * FROM `security`";
							$y = prepareExecuteQuery($x);

							while($row = mysqli_fetch_array($y)){
							?><option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option><?php
							}
						?>
					</select>
				</td>
				</tr>
                <tr>
				<td>
					<label>T&C Number</label>
					<input type="text" name="internal_number" id="internal_number" value="<?php if ($isContainer) echo $container->internal_number; ?>">
				</td>
				<td></td>

				</tr>
			</tbody>
		</table>
		</form>
	</div>

</main>
<div id="btm"></div>
<script>

    var oneClickProtect = false;
	function saveDelivery(event){
        if (oneClickProtect == true) return;
		event.preventDefault();
		var supplier_search = $('#supplier_search').val();
		var date_received = $('#date_received').val();
		var vehicle_temperature = $('#vehicle_temperature').val();
		var product_temperature = $('#product_temperature').val();
		var delivery_note_number = $('#delivery_note_number').val();
        var internal_number = $('#internal_number').val();
		var security_id = $('#security_id').val();

		var good = 1;
		var msg = "";

		if(supplier_search == ''){
			msg = "The highlighted fields cannot be blank!";
			$('#supplier_search').css('border','2px solid red');
			good = 0;
		}else{
			$('#supplier_search').css('border','1px solid grey');
		}


		if(security_id == 0){
			msg = "The highlighted fields cannot be blank!";
			$('#security_id').css('border','2px solid red');
			good = 0;
		}else{
			$('#security_id').css('border','1px solid grey');
		}


		// if(vehicle_reg == ''){
			// msg = "The highlighted fields cannot be blank!";
			// $('#vehicle_reg').css('border','2px solid red');
			// good = 0;
		// }else{
			// $('#vehicle_reg').css('border','1px solid grey');
		// }

		if(date_received == ''){
			msg = "The highlighted fields cannot be blank!";
			$('#date_received').css('border','2px solid red');
			good = 0;
		}else{
			$('#date_received').css('border','1px solid grey');
		}

		if(vehicle_temperature == ''){
			msg = "The highlighted fields cannot be blank!";
			$('#vehicle_temperature').css('border','2px solid red');
			good = 0;
		}else{
			$('#vehicle_temperature').css('border','1px solid grey');
		}

		if(product_temperature == ''){
			msg = "The highlighted fields cannot be blank!";
			$('#product_temperature').css('border','2px solid red');
			good = 0;
		}else{
			$('#product_temperature').css('border','1px solid grey');
		}

		if(delivery_note_number == ''){
			msg = "The highlighted fields cannot be blank!";
			$('#delivery_note_number').css('border','2px solid red');
			good = 0;
		}else{
			$('#delivery_note_number').css('border','1px solid grey');
		}

		$('#msgNotice').html(msg);

		if(good == 1){
            oneClickProtect=true;
			$('#mainForm').ajaxSubmit({headers:{'X-CSRF-TOKEN': "<?php echo csrf_token();?>"},success:mainFormSucess});
		}
	}
	function mainFormSucess(event){
		$('#supplier_search_results').html(event);
	}

	$(document).ready(function(){
		// $( "#date_received" ).datepicker({
		// 	dateFormat: 'dd/mm/yy'
		// });

		jQuery('#date_received').datetimepicker({
			defaultDate: new Date(),
			defaultTime: '00:00',
			format:'d/m/Y H:i',
		});

		$('#supplier_search').keyup(function(){
			var val = $('#supplier_search').val();
			// $('#test2d').text(val);
			if(val != ''){
				$('#supplier_search_results').fadeIn();
			}else{
				$('#supplier_search_results').fadeOut();
			}

			var species = $('#species_id').val();

			var xhttp = new XMLHttpRequest();
			xhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
			  // document.getElementById("demo").innerHTML = this.responseText;
			  $('#supplier_search_results').html(this.responseText);
			}
			};
			xhttp.open("POST", "ajax/getSupplierDropdown.php", true);
			xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xhttp.setRequestHeader('X-CSRF-TOKEN', "<?php echo csrf_token();?>");
			xhttp.send("searchterm=" + val + "&species_id=" + species);

		});


	});
</script>
</body>
</html>
