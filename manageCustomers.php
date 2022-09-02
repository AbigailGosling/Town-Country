<?php
	include('functions.php');
	$showDisabled = 0;
	if (isset($_GET['showDisabled']))
	{
		$showDisabled = $_GET['showDisabled'];
	}
?>
<!doctype html>
<html class="int">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Town &amp; Country</title>
	
	<link href="css/style.css" rel="stylesheet" type="text/css">
	<link href="css/lity.css" rel="stylesheet" type="text/css">
	<link href="css/font-awesome.css" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<script src="js/lity.js"></script>

	<script>
	$( function() {
		$( "#datepicker" ).datepicker();
	});
	   
    function blockSpecialChar(e) {
		var k = e.keyCode;
		return ((k > 64 && k < 91) || (k > 96 && k < 123) || k == 8  ||  k == 67 || (k >= 48 && k <= 57));
	}
	</script>
	<style>
		.transferPopup{
			display:none;
			position: fixed;
			top: 0px;
			left: 0px;
			width: 100%;
			height: 100vh;
			background-color: rgba(0,0,0,0.5);
		}

		.transferPopup-container{
			display:flex;
			align-items:center;
			justify-content: center;
			width: 100%;
			height: 100vh;
		}

		.transferPopup-content{
			background-color: #fff;
			padding:20px;
			text-align: center;
		}

		.transferPopup select{
			height:35px;
			width:300px;
		}

		.transferPopup .transferbtn{
			display: block;
			width: 300px;
			margin: 0 auto;
			margin-top: 20px;
			height: 35px;
		}
	</style>
</head>

<body class="menu">
<div id="top">
	<a href="menu.php" id="menu">MENU</a>
	<a href="logout.php" id="logout">LOGOUT</a>
</div>

<main style="padding-top:0px !important;">
	<?php
		if($_GET['id'] != ''){
			
			$id = $_GET['id'];
			$x2 = "SELECT * FROM customers WHERE id = '$id'";
			$yy2 = mysqli_query($conn, $x2);
			

			$data = mysqli_fetch_array($yy2);

		}
	?>
	<form method="POST" action="<?php if($_GET['id'] != ''){ echo '/scripts/updateCustomer.php'; } else { echo '/scripts/addCustomer.php'; } ?>">
	<input type="hidden" value="<?php echo $_GET['id']; ?>" name="id">
	<div id="customerContainer">
		<div class="box">
			<h3>Customer Details</h3>
			
			<table width="100%" id="customerDetails">
				<tr>
					<td class="label"><label>Business Name</label></td>
					<td><input type="text" class="input" name="businessname" style="margin-bottom:-2px;" value="<?php echo $data['businessname']; ?>"></td>
				</tr>
				<tr>
					<td class="label"><label>Trading as</label></td>
					<td><input type="text" class="input" name="tradingas" value="<?php echo $data['tradingas']; ?>"></td>
				</tr>
				<?php
					for ($u=1;$u<10;$u++)
					{
						if ($u>1)$style1 = "display:none;";
				?>
				<tr style="vertical-align: top;">
					<td class="label"><label>Delivery Address <?php echo $u; ?></label></td>
					<td>
						<input type="text" class="input" id="address<?php echo $u; ?>" name="address<?php echo $u; ?>_1" value="<?php echo $data['address'.$u.'_1']; ?>">
						<div style="<?php echo $style1; ?>" id="address<?php echo $u; ?>container">
 							<input type="text" class="input" name="address<?php echo $u; ?>_2" value="<?php echo $data['address'.$u.'_2']; ?>"><br/>
							<input type="text" class="input" name="address<?php echo $u; ?>_3" value="<?php echo $data['address'.$u.'_3']; ?>"><br/>
							<input type="text" class="input" name="address<?php echo $u; ?>_4" value="<?php echo $data['address'.$u.'_4']; ?>">
						</div>
						
					</td>
				</tr>
				<tr id="address<?php echo $u; ?>containerPostcode" style="<?php echo $style1; ?>">
					<td class="label"><label>Postcode</label></td>
					<td><input type="text" class="input postcode" name="postcode_<?php echo $u; ?>" value="<?php echo $data['postcode_'.$u]; ?>"></td>
				</tr>
				<tr id="address<?php echo $u; ?>containerNumber" style="<?php echo $style1; ?>">
					<td class="label"><label>Delivery Contact No.</label></td>
					<td><input type="text" class="input" name="address<?php echo $u; ?>_number" value="<?php echo $data['address'.$u.'_number']; ?>"></td>
				</tr>
				
				<tr height="40"><td colspan="2"></td></tr>
				<?php
					}
				?>
				<tr>
					<td class="label"><label>Name of buyer</label></td>
					<td><input type="text" class="input" name="nameofbuyer" value="<?php echo $data['nameofbuyer']; ?>"></td>
				</tr>
				
				<tr>
					<td class="label"><label>Contact Number</label></td>
					<td><input type="text" class="input" name="contactnumber" value="<?php echo $data['contactnumber']; ?>"></td>
				</tr>
				
				<tr>
					<td class="label"><label>Email</label></td>
					<td><textarea type="text" style="resize: none; width: 169px; height: 47px;" class="input" name="customer_email"><?php echo $data['customer_email']; ?></textarea></td>
				</tr>
				
				<tr>
					<td class="label"><label>Disable Customer</label></td>
					<td><input type="checkbox" name="disabled" value="1" <?php echo ($data['disabled'] == 1)?"checked":""; ?>></td>
				</tr>				
			</table>
		</div>
		
		<div class="box">
			<h3>Internal use only</h3>
			<table width="100%" id="customerDetails">
				<tr>
					<td class="label"><label>ID Number</label></td>
					<td><input type="text" class="input" name="asdf" value="<?php echo $data['id']; ?>" style="background:#fff;" disabled></td>
				</tr>
				<tr height="40"><td colspan="2"></td></tr>	
				<tr>
					<td class="label"><label>Company Reg No.</label></td>
					<td><input type="text" class="input" name="companyregno" value="<?php echo $data['companyregno']; ?>"></td>
				</tr>
				<tr style="vertical-align: top;">
					<td class="label"><label>Accounts Address</label></td>
					<td>
						<input type="text" class="input" name="accounts_address_1" value="<?php echo $data['accounts_address_1']; ?>"><br/>
						<input type="text" class="input" name="accounts_address_2" value="<?php echo $data['accounts_address_2']; ?>"><br/>
						<input type="text" class="input" name="accounts_address_3" value="<?php echo $data['accounts_address_3']; ?>"><br/>
						<input type="text" class="input" name="accounts_address_4" value="<?php echo $data['accounts_address_4']; ?>">
 					</td>
				</tr>

				<tr style="vertical-align: top;">
					<td class="label"><label>Accounts Email</label></td>
					<td>
						<textarea type="email" style="resize: none; width: 169px; height: 47px;" class="input" name="accounts_email"><?php echo $data['accounts_email']; ?></textarea><br/>
 					</td>
				</tr>

				<tr style="vertical-align: top;">
					<td class="label"><label>Accounts Comments</label></td>
					<td>
						<textarea class="input" name="accounts_comments"><?php echo $data['accounts_comments']; ?></textarea>
 					</td>
				</tr>

				<tr height="40"><td colspan="2"></td></tr>	

				<tr>
					<td class="label"><label>Accounts Contact</label></td>
					<td><input type="text" class="input" name="accounts_contact" value="<?php echo $data['accounts_contact']; ?>"></td>
				</tr>
				<tr>
					<td class="label"><label>Tel Number</label></td>
					<td><input type="text" class="input" name="tel_number" value="<?php echo $data['tel_number']; ?>"></td>
				</tr>
				<tr>
					<td class="label"><label>Email</label></td>
					<td><textarea type="text" style="resize: none; width: 169px; height: 47px;" class="input" name="internal_email"><?php echo $data['internal_email']; ?></textarea></td>
				</tr>
				<tr height="40"><td colspan="2"></td></tr>
				<tr>
					<td class="label"><label>Due Warning</label></td>
					<td><input type="number" class="input" name="due_warning" min="-1" value="<?php echo $data['due_warning']; ?>"></td>
				</tr>	
				<tr>
					<td class="label"><label>Insurance Terms</label></td>
					<td><input type="number" class="input" name="credit_terms" min="-1" value="<?php echo $data['credit_terms']; ?>"></td>
				</tr>
				<tr>
					<td class="label"><label>Grace Period</label></td>
					<td><input type="number" class="input" name="credit_grace" min="-1" value="<?php echo $data['credit_grace']; ?>"></td>
				</tr>
				<tr height="40"><td colspan="2"></td></tr>	
				<tr>
					<td class="label"><label>Prices & Extensions</label></td>
					<td>
						<select name="pricedefault">
                            <option value="0" <?php if($data['pricedefault'] == 0 && $data != ''){ echo 'selected'; } ?>>Hide</option>
                            <option value="1" <?php if($data['pricedefault'] == 1 && $data != ''){ echo 'selected'; } ?>>Display</option>    
						</select>
					</td>
				</tr>
				<tr height="40"><td colspan="2"></td></tr>
				<tr>
					<td class="label"><label>Default User</label></td>
					<td>
						<select id="sales_person" name="default_salesman_id">
							<?php
								$_users = mysqli_query($conn, "SELECT * FROM `users` where 1 in (pages)");
				
								while ($_user = mysqli_fetch_array($_users)) {
									?><option value="<?php echo $_user['id']; ?>" <?php if($data['default_salesman_id'] == $_user['id']){ echo 'selected'; } ?>><?php echo $_user['name']; ?></option><?php
								}
							?>
						</select>
					</td>
				</tr>
 			</table>
		</div>
	</div>
	
	<div id="flexContainerTwo">
		<div class="fullbox">
			<table width="100%">
				<tr>
					<td class="label"><label>Credit Rating</label></td>
					<td><input type="text" class="input" name="credit_rating" value="<?php echo number_format((float)$data['credit_rating'], 2, '.', ''); ?>"></td>
				</tr>
				<tr>
					<td class="label"><label>Close to limit alert</label></td>
					<td><input type="text" class="input" name="flaguplimit" value="<?php echo number_format((float)$data['flaguplimit'], 2, '.', ''); ?>"></td>
				</tr>
				<tr>
					<td class="label"><label>Current outstanding</label></td>
					<td><input type="text" class="input" name="current_outstanding" value="<?php echo totalOutstandingForCustomer($data['id']); ?>"></td>
				</tr>
				<!--<tr>
					<td class="label"><label>Payments received</label></td>
					<td><input type="text" class="input" name="payment_received"></td>
				</tr>
				<tr style="vertical-align:top;">
					<td class="label"><label>Account status</label></td>
					<td>
						<?php
							$current_outstanding = (float) $data['current_outstanding'];
							$flaguplimit = (float) $data['flaguplimit'];
							$credit_rating = (float) $data['credit_rating'];
							
							if($current_outstanding >= $credit_rating){
							?><div class="status stop">Stop</div><?php
							}else if($current_outstanding >= $flaguplimit){
							?><div class="status closetolimit">Close to limit</div><?php
							}
						?>
					</td>
				</tr>-->
			</table>
		</div>
		
		<div class="fullbox controls">
			<table width="100%">
				<tr>
					<td class="label"><label>Commercial decision</label></td>
					<td>
						<a href="javascript:;" id="overrider" onclick="overrideSales(this,<?php echo $id; ?> )" class="override"><?php if($data['override'] == 1){ ?>Apply Credit Checking<?php } else { ?>Override Credit Check<?php } ?></a>
						
					</td>
				</tr>
				<tr height="140"><td colspan="2"></td></tr> 
			</table>
		</div>
	</div>
	<div id="flexContainerTwo">
 	 
    </div>
    	<div id="flexContainerTwo">

		<div class="fullbox controls">
			<table width="100%">
				<tr>
					<td>
						<?php if($_GET['id'] != ''){ ?>
							<a href="/customer_soa.php?id=<?php echo $data['id']; ?>" class="update" style="color:white;background:orange;">View Statement of account</a>
						<?php } ?>
					</td>
				</tr>			
			</table>
		</div>

		<div class="fullbox controls">
			<table width="100%">
				<tr>
					<td class="label"><label></label></td>
					<td style="text-align:right;">
						<a href="#" class="update" style="display:none;">Update & Save</a>
						<input type="submit" class="update" value="Update & Save">
					</td>
				</tr>			
			</table>
		</div>
	</div>
	
	</form>

	<Br/><BR/>

	<div id="intakelist">
 
		<h1 class="int">CUSTOMER LIST</h1>

		<div>
			<table>
				<tr>
					<td><input type="text" id="instantSearch" placeholder="Search.." style="width:260px;height:28px;padding-left:10px;" enterkeyhint="go"/></td>
					<td style="width:90%"></td>
					<td><input type="button" value="<?php echo ($showDisabled == 1)?"Hide":"Show"; ?> Disabled" style="width:110px;height:30px;"
						onclick='window.location.href = window.location.href.split("?")[0] + "?showDisabled=" + <?php echo ($showDisabled == 1)?0:1; ?>'/></td>
				</tr>
			</table>
			
		</div>
		<div id="cutAjax">

		<?php

			$x = "SELECT * FROM `customers` WHERE `disabled`=$showDisabled ORDER BY id ASC";

			$y = mysqli_query($conn, $x) or die(mysqli_error($conn));

			while($row = mysqli_fetch_array($y)){

				$customer_id = $row['id'];
				$resultsCheckPicksheets = mysqli_query($conn, "SELECT id FROM pickerSheets WHERE customer_id='$customer_id'");

				$existingPicksheetsCount = mysqli_num_rows($resultsCheckPicksheets);
			?>

			<table width="100%" border="0" cellpadding="0" cellspacing="0">

			<tr><td align="center" class="pos">
				<a href="manageCustomers.php?id=<?php echo $row['id']; ?>" class="intake">
					<table width="100%" border="0">
						<tr>
							<td width="100" align="left">ID: <?php echo $row['id']; ?></td>
							<td align="center" style="font-size: 18px;"><?php echo $row['businessname']; ?></td>
							<td width="100" align="right">
								<a href="/manageCustomers.php?id=<?php echo $row['id']; ?>" style="right:-35px;height:40px;padding-top:6px;top:0px;" id="delete_intake"><i class="fa fa-pencil" style="padding-right:4px;" aria-hidden="true"></i></a>
								<a href="javascript:;" onclick="deleteRow(<?php echo $row['id']; ?>, <?php echo $existingPicksheetsCount; ?>)" style="right:-70px;height:40px;padding-top:6px;top:0px;" id="delete_intake"><i class="fa fa-trash" style="padding-right:5px;" aria-hidden="true"></i></a>
							</td>
						</tr>
					</table>
				</a>
  
				 
			</td></tr>

			</table>

			<?php

			}

		?>
		</div>
	</div>
	
	<div class="transferPopup">
		<div class="transferPopup-container">
			<div class="transferPopup-content">
				<h2>Transfer required</h2>
				<p>There is currently <b id="transferCount"></b> picksheets connected to this customer.<br/>Please pick a new customer to transfer them.</p>
				<form method="POST" action="/scripts/transferPicksheetsCustomer.php">
					<input type="hidden" name="old_customer_id" id="old_customer_id">
					<select name="new_customer_id">
						<?php
							$customers = mysqli_query($conn, "SELECT id,businessname FROM `customers`");

							while($customer = mysqli_fetch_array($customers)){
								?><option value="<?php echo $customer['id']; ?>" class="transfer_customers transfer_customers_<?php echo $customer['id']; ?>"><?php echo $customer['businessname']; ?></option><?php
							}
						?>
					</select>
					
					<input type="submit" value="Transfer picksheets" class="transferbtn">
				</form>
			</div>
		</div>
	</div>
</main>

<script type="text/javascript">
	$(document).ready(function() {
		$("[name$=_email]").keypress(function(event) {
			if(event.which == '13') {				
				return false;
			}
		});
		});
	for (var u=2;u<10;u++){
		$('#address'+u.toString()).click(function(){
			var v = $(this).attr('name').toString().replace('address','').toString().substring(0, 1);
			$('#address'+v.toString()+'container').show();
			$('#address'+v.toString()+'containerPostcode').show();
			$('#address'+v.toString()+'containerNumber').show();
		});
	}
	
	$('#instantSearch').keydown(function(){

		var val = $('#instantSearch').val();

		// $('#test2d').text(val);

		console.log(val);

		

			var xhttp = new XMLHttpRequest();

			xhttp.onreadystatechange = function() {

			if (this.readyState == 4 && this.status == 200) {

			  // document.getElementById("demo").innerHTML = this.responseText;

			  $('#cutAjax').html(this.responseText);

			}

			};

			xhttp.open("POST", "/ajax/customersPageList.php", true);

			xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

			xhttp.send("searchterm=" + val);

		

	});
	
	$('.transferPopup-container').click(function(e){
		if(e.target != this) return;
		$('.transferPopup').hide();
		
	});

	function deleteRow(id, existingPicksheetsCount){
		
		if(existingPicksheetsCount > 0){
			$('.transferPopup').show();
			$('#transferCount').text(existingPicksheetsCount);
			$('#old_customer_id').val(id);

			$('.transfer_customers').show();
			$('.transfer_customers_' + id).hide();

		}else{
			if(confirm('Are you sure you want to delete this?')){
				window.location.href = "/scripts/deleteCustomer.php?id=" + id;
			}
		}
	}
	var allowOverride = true;
	function overrideSales(ele, id){
		if (allowOverride == true)
		{
			allowOverride = false;	
			var q = $('#overrider');
			if (q.text() != "Apply Credit Checking") q.text("Apply Credit Checking");
			else q.text("Override Credit Check");
			$.post("<?php echo $domain; ?>ajax/overrideSales.php",{
				id: id,
			},
			function(data, status){
				allowOverride = true;
			});
		}
 	}
	
</script>

<div id="btm"></div>

</body>

</html>