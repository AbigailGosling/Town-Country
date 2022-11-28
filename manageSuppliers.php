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



	<link href="css/font-awesome.css" rel="stylesheet" type="text/css">

	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>

	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

	<script>

	$( function() {

		$( "#datepicker" ).datepicker();

	});

	   
        function blockSpecialChar(e) {
            var k = e.keyCode;
            return ((k > 64 && k < 91) || (k > 96 && k < 123) || k == 8  ||  k == 67 ||  k == 32 || k == 190 || (k >= 48 && k <= 57));
        }
 
    </script>

</head>

<body class="menu">

<div id="top">

	<a href="menu.php" id="menu">MENU</a>

	<a href="logout.php" id="logout">LOGOUT</a>

</div>

<main>

	<div id="intakelist">

		<?php

			if($_GET['id'] != ''){

				

				$id = $_GET['id'];

				

				$x2 = "SELECT * FROM supplier WHERE id = '$id'";

				$yy2 = mysqli_query($conn, $x2);

				

				$data = mysqli_fetch_array($yy2);

			}

		?>

		<form method="POST" action="<?php if($_GET['id'] != ''){ echo '/scripts/updateSupplier.php'; } else { echo '/scripts/addSupplier.php'; } ?>">

		<table width="100%" border="0" cellpadding="0" cellspacing="0">

			<tr><td colspan="3"><h1 class="int"><?php if($_GET['id'] != ''){ echo 'UPDATE'; } else { echo 'ADD'; } ?> SUPPLIER</h1></td></tr>

			<tr>

				<td style="width:49%">

					<label>Name</label>

					<input type="text" name="id" value="<?php echo $data['id']; ?>" style="display:none;">

					<input type="text" id="supname" name="name" value="<?php echo $data['name']; ?>" required>

				</td>
				<td style="width:20px">
				</td>
				<td style="width:49%"></td>
			</tr>

			<tr>

				<td>

					<label>Address</label>

					<textarea style="height:200px;width:98%;" name="postcode"><?php echo $data['postcode']; ?></textarea>

				</td>

			</tr>

			<tr>

				<td>

					<label>Contact Name</label>

					<input type="text" name="contact_name" value="<?php echo $data['contact_name']; ?>">

				</td>
				<td style="width:20px">
				</td>
				<td>
					<label>Contact Number</label>

					<input type="text" name="contact_number" value="<?php echo $data['contact_number']; ?>">

				</td>

			</tr>
			
			<tr>

				<td>

					<label>Town & Country Contact</label>
					<select name="user_id" style="height:29px;">
						<option disabled selected>Select a salesman</option>
						<?php
							$usersResult = mysqli_query($conn, "SELECT id,`name` FROM users");
							while($user = mysqli_fetch_array($usersResult)){
						?>
						<option value="<?php echo $user['id']; ?>" <?php if($data['user_id'] == $user['id']){ echo 'selected'; } ?>><?php echo $user['name']; ?></option>
						<?php } ?>
					</select>
				</td>
				<td style="width:20px">
				</td>
				<td>
					<label>Number</label>

					<input type="text" name="internal_number" value="<?php echo $data['internal_number']; ?>">

				</td>

			</tr>

			<tr>

				<td colspan="3">
				<input type="submit" disabled style="display: none" aria-hidden="true" />
					<?php

					if($_GET['id'] != ''){

					?><input type="submit" value="Update Supplier"><?php

					}else{

					?><input type="submit" value="Add Supplier"><?php

					}

					?>

				</td>

			</tr>

		</table>

		</form>

		<h1 class="int">SUPPLIER LIST</h1>

		<input type="text" id="instantSearch" placeholder="Search.." style="width:260px;height:28px;padding-left:10px;" enterkeyhint="go">

		<div id="cutAjax">

		<?php

			$x = "SELECT * FROM `supplier` ORDER BY name ASC";

			$y = mysqli_query($conn, $x) or die(mysqli_error($conn));

			while($row = mysqli_fetch_array($y)){

			?>

			<table width="100%" border="0" cellpadding="0" cellspacing="0">

			<tr><td align="center" class="pos">
				<a href="manageSuppliers.php?id=<?php echo $row['id']; ?>" class="intake">
					<table width="100%" border="0">
						<tr>
							<td width="100" align="left">ID: <?php echo $row['id']; ?></td>
							<td align="center" style="font-size: 18px;"><?php echo $row['name']; ?></td>
							<td width="100" align="right">
 								<a href="/manageSuppliers.php?id=<?php echo $row['id']; ?>" id="edit"><i class="fa fa-pencil" style="color:red;padding-right:4px;" aria-hidden="true"></i></a>
 								<a href="javascript:;" onclick="deleteRow(<?php echo $row['id']; ?>)" id="close"><i class="fa fa-times" style="color:red;padding-right:4px;" aria-hidden="true"></i></a>
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

	$(document).ready(function(){

		

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

				xhttp.open("POST", "/ajax/suppliersPageList.php", true);

				xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

				xhttp.send("searchterm=" + val);

			

		});

		

		$('.speciesName').click(function(){

			$(this).next('.cutsContainer').toggle();

			console.log(1);

		});

	});

	

	

	function deleteRow(id){

		if(confirm('Are you sure you want to delete this?')){

		window.location.href = "/scripts/deleteSupplier.php?id=" + id;

			// console.log(id);

		}

	}

	

</script>

<div id="btm"></div>

</body>

</html>