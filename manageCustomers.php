<?php
	include('functions.php');
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
				<tr style="vertical-align: top;">
					<td class="label"><label>Delivery Address 1</label></td>
					<td>
						<input type="text" class="input" name="address1_1" value="<?php echo $data['address1_1']; ?>"><br/>
						<input type="text" class="input" name="address1_2" value="<?php echo $data['address1_2']; ?>"><br/>
						<input type="text" class="input" name="address1_3" value="<?php echo $data['address1_3']; ?>"><br/>
						<input type="text" class="input" name="address1_4" value="<?php echo $data['address1_4']; ?>">
 					</td>
				</tr>
				<tr>
					<td class="label"><label>Postcode</label></td>
					<td><input type="text" class="input postcode" name="postcode_1" value="<?php echo $data['postcode_1']; ?>"></td>
				</tr>
				
				<tr>
					<td class="label"><label>Delivery Contact No.</label></td>
					<td><input type="text" class="input" name="address1_number" value="<?php echo $data['address1_number']; ?>"></td>
				</tr>
				
				<tr height="40"><td colspan="2"></td></tr>
				
				<tr style="vertical-align: top;">
					<td class="label"><label>Delivery Address 2</label></td>
					<td>
						<input type="text" class="input" id="address2" name="address2_1" value="<?php echo $data['address2_1']; ?>">
						<div style="display:none;" id="address2container">
 							<input type="text" class="input" name="address2_2" value="<?php echo $data['address2_2']; ?>"><br/>
							<input type="text" class="input" name="address2_3" value="<?php echo $data['address2_3']; ?>"><br/>
							<input type="text" class="input" name="address2_4" value="<?php echo $data['address2_4']; ?>">
						</div>
						
					</td>
				</tr>
				<tr id="address2containerPostcode" style="display:none;">
					<td class="label"><label>Postcode</label></td>
					<td><input type="text" class="input postcode" name="postcode_2" value="<?php echo $data['postcode_2']; ?>"></td>
				</tr>
				<tr id="address2containerNumber" style="display:none;">
					<td class="label"><label>Delivery Contact No.</label></td>
					<td><input type="text" class="input" name="address2_number" value="<?php echo $data['address2_number']; ?>"></td>
				</tr>
				
				
				<tr style="vertical-align: top;">
					<td class="label"><label>Delivery Address 3</label></td>
					<td>
						<input type="text" class="input" id="address3" name="address3_1" value="<?php echo $data['address3_1']; ?>">
						<div style="display:none;" id="address3container">
 							<input type="text" class="input" name="address3_2" value="<?php echo $data['address3_2']; ?>"><br/>
							<input type="text" class="input" name="address	3_3" value="<?php echo $data['address3_3']; ?>"><br/>
							<input type="text" class="input" name="address3_4" value="<?php echo $data['address3_4']; ?>">
						</div>
					</td>
				</tr>
				
 
								
				<tr id="address3containerPostcode" style="display:none;">
					<td class="label"><label>Postcode</label></td>
					<td><input type="text" class="input postcode" name="postcode_3" value="<?php echo $data['postcode_3']; ?>"></td>
				</tr>
				
				<tr id="address3containerNumber" style="display:none;">
					<td class="label"><label>Delivery Contact No.</label></td>
					<td><input type="text" class="input" name="address3_number" value="<?php echo $data['address3_number']; ?>"></td>
				</tr>
				
				<tr height="40"><td colspan="2"></td></tr>

				
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
					<td><input type="text" class="input" name="customer_email" value="<?php echo $data['customer_email']; ?>"></td>
				</tr>
				
				<tr>
					<td class="label"><label>Salesman</label></td>
					<td><input type="text" class="input" name="salesman" value="<?php echo $data['salesman']; ?>"></td>
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
					<td><input type="text" class="input" name="internal_email" value="<?php echo $data['internal_email']; ?>"></td>
				</tr>
				<tr height="40"><td colspan="2"></td></tr>	
				<tr>
					<td class="label"><label>Credit Terms</label></td>
					<td><input type="text" class="input" name="credit_terms" value="<?php echo $data['credit_terms']; ?>"></td>
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
					<td><input type="text" class="input" name="current_outstanding" value="<?php echo number_format((float)$data['current_outstanding'], 2, '.', ''); ?>"></td>
				</tr>
				<tr>
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
				</tr>
			</table>
		</div>
		
		<div class="fullbox controls">
			<table width="100%">
				<tr>
					<td class="label"><label>Commercial decision</label></td>
					<td>
						<a href="javascript:;" onclick="overrideSales(this,<?php echo $id; ?> )" class="override">Override & allow sales</a>
						
						<div class="override-enabled"  style="<?php if($data['override'] == 1){ ?>display:block;<?php }?>">Enabled</div>
					</td>
				</tr>
				<tr height="140"><td colspan="2"></td></tr> 
			</table>
		</div>
	</div>
	<div id="flexContainerTwo">
    <div class="fullbox controls"></div>
		<div class="fullbox controls">
			<h3>Related Users</h3>
		
			<?php

				$currentUsers = explode(',', $data['users']);
				
				$usersResult = mysqli_query($conn, "SELECT * FROM `users`");

				while($row = mysqli_fetch_array($usersResult)){
				?>
				<div class="userRow" style="padding-bottom:5px;">
					<input type="checkbox" id="user<?php echo $row['id']; ?>" name="users[]" value="<?php echo $row['id']; ?>" <?php if(in_array($row['id'], $currentUsers)){ echo 'checked'; } ?>>
					<label for="user<?php echo $row['id']; ?>"><?php echo $row['name']; ?></label><br>
				</div>
				<?php
				}
			?>
		</div>
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

		<input type="text" id="instantSearch" placeholder="Search.." style="width:260px;height:28px;padding-left:10px;">

		<div id="cutAjax">

		<?php

			$x = "SELECT * FROM `customers`";

			$y = mysqli_query($conn, $x) or die(mysqli_error($conn));

			while($row = mysqli_fetch_array($y)){

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
								<a href="javascript:;" onclick="deleteRow(<?php echo $row['id']; ?>)" style="right:-70px;height:40px;padding-top:6px;top:0px;" id="delete_intake"><i class="fa fa-trash" style="padding-right:5px;" aria-hidden="true"></i></a>
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
</main>

<script type="text/javascript">
	
	$('#address2').click(function(){
		$('#address2container').show();
		$('#address2containerPostcode').show();
		$('#address2containerNumber').show();
	});
	
	$('#address3').click(function(){
		$('#address3container').show();
		$('#address3containerPostcode').show();
		$('#address3containerNumber').show();
	});
	
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
	
  function deleteRow(id){

		if(confirm('Are you sure you want to delete this?')){

			window.location.href = "/scripts/deleteCustomer.php?id=" + id;

			// console.log(id);

		}

	}

	function overrideSales(ele, id){
		$.post("<?php echo $domain; ?>ajax/overrideSales.php",{
			id: id,
		},
		function(data, status){
			alert('done!');
			$('.override-enabled').fadeIn();
		});
 	}
	
</script>

<div id="btm"></div>

</body>

</html>