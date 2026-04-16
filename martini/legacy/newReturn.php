<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

	include('functions.php');

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
	<script src="https://code.jquery.com/jquery-1.12.4.js"></script><script src="https://malsup.github.io/jquery.form.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
</head>
<body>
<div id="top">
	<a href="menu.php" id="menu">MENU</a>
	<a href="logout" id="logout">LOGOUT</a>
</div>
<main class="int">
	<div id="product">
		<div id="product_heading">Return</div>
		<div id="product_options">
			<a href="#" onclick="saveReturn()">Save Return</a>
		</div>
		<form method="POST" id="mainForm" action="scripts/newReturn.php">
		<input type="text" name="purchase_id" value="<?php if($purchase['id'] != ''){ echo $purchase['id']; }else{ echo '#'; } ?>" style="display:none;">
		<table>
			<tbody>
				<tr>
					<td><div id="msgNotice" style="color:red;"></div></td>
				</tr>
				<tr>
                    <td>
                        <label>Original Invoice Number</label>
                        <input type="text" name="delivery_note_number" id="delivery_note_number">
                    </td>
				<td>
					<label>Vehicle Registration</label>
					<input type="text" name="vehicle_reg" id="vehicle_reg"  style="text-transform:uppercase;" placeholder="">
				</td>
				</tr>
				<tr>
				<td>
					<label>Date Received</label>
					<input type="text" name="date_received" id="date_received" value="<?php echo date('d/m/Y'); ?>" placeholder="">
				</td>
				<td>
					<label>Vehicle Temp (°C)</label>
					<input type="text" width="184" name="vehicle_temperature" id="vehicle_temperature" placeholder="">
				</td>
				</tr>
				<tr>
				<td>
					<label>Product Temp (°C)</label>
					<input type="text" width="184" name="product_temperature" id="product_temperature" placeholder="">
				</td>
                <td>
					<label>Security</label>
					<select name="security_id" style="width:192px;height:30px;">
						<option selected disabled>Please choose below</option>
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
					<label>Staff Name</label>
					<input type="text" name="staff_name" value="<?php echo User::find(Auth::id())->name; ?>" disabled>
					<input type="text" name="staff_id" value="<?php echo Auth::id(); ?>" style="display:none;">
				</td>
				</tr>
			</tbody>
		</table>
		</form>
	</div>

</main>
<div id="btm"></div>
<script>

	function setCustomer(customer_id, text){
		$('#customer_search_results').fadeOut();
		$('#supplier_id').val(customer_id);
		$('#supplier_search').val(text);
	}

	function saveReturn(){

		var supplier_search = $('#supplier_search').val();
		var date_received = $('#date_received').val();
		var vehicle_temperature = $('#vehicle_temperature').val();
		var product_temperature = $('#product_temperature').val();
		var delivery_note_number = $('#delivery_note_number').val();

		var good = 1;
		var msg = "";

		// if(supplier_search == undefined || supplier_search == ''){
		// 	msg = "The highlighted fields cannot be blank!";
		// 	$('#supplier_search').css('border','2px solid red');
		// 	good = 0;
		// }else{
		// 	$('#supplier_search').css('border','1px solid grey');
		// }

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
			$('#mainForm').ajaxSubmit({headers:{'X-CSRF-TOKEN': "<?php echo csrf_token();?>"},success:mainFormSucess});
		}
	}
	function mainFormSucess(event){
        if (event.indexOf('message:') === 0) {
            alert(event.replace('message:', ''));
            return;
        }
		window.location = 'intake.php?id=' + event;
	}



	$(document).ready(function(){
		$( "#date_received" ).datepicker({
			dateFormat: 'dd/mm/yy'
		});

		$('#supplier_search').keyup(function(){
			var val = $('#supplier_search').val();
			// $('#test2d').text(val);
			if(val != ''){
				$('#customer_search_results').fadeIn();
			}else{
				$('#customer_search_results').fadeOut();
			}

			var species = $('#species_id').val();

			var xhttp = new XMLHttpRequest();
			xhttp.onload = function() {
			if (this.readyState == 4 && this.status == 200) {
			  // document.getElementById("demo").innerHTML = this.responseText;
			  $('#customer_search_results').html(this.responseText);
			}
			};
			xhttp.open("POST", "ajax/getCustomerDropdown.php", true);
			xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xhttp.setRequestHeader('X-CSRF-TOKEN', "<?php echo csrf_token();?>");
			xhttp.send("searchterm=" + val + "&species_id=" + species);

		});


	});
</script>
</body>
</html>
