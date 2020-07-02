<?php
	include_once('functions.php');
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
	</script>
</head>
<body class="menu">
<div id="top">
	<a href="menu.php" id="menu">MENU</a>
	<a href="logout.php" id="logout">LOGOUT</a>
</div>
<main>
	<div id="intakelist">
		<h1 class="int">Delivery Notes</h1>
		<input type="text" id="instantSearch" placeholder="Search.." style="width:260px;height:28px;padding-left:10px;">
		<table width="100%" border="0" cellpadding="0" cellspacing="0" id="intakeAjax">
			<?php
			
				session_start();
				
				$userid = $_SESSION['USER'];
				
				$x = "SELECT * FROM `pickerSheets` WHERE completed='1' ORDER BY `id` DESC";
				$y = mysqli_query($conn, $x);
				
				while($row = mysqli_fetch_array($y)){
					$customer_id = $row['customer_id'];
					
					$date = $row['estimated_delivery_date'];
					
					$date=date_create($date);
					$date = date_format($date,"d/m/Y");
					
					$x2 = "SELECT * FROM `customers` WHERE id ='$customer_id'";
					$y2 = mysqli_query($conn, $x2);
					$row2 = mysqli_fetch_array($y2);
					
				?>
				<tr><td align="center" class="pos">
				<a href="deliverynote.php?id=<?php echo $row['id']; ?>" class="intake" style="padding-left:10px;padding-right:10px;">
					<table width="100%" border="0">
						<tr>
							<td width="100" align="left">ID: 0000<?php echo $row['id']; ?></td>
							<td align="center" style="font-size: 18px;"><?php echo $row2['businessname']; ?> 
								<?php if($row['deliverynote_printed'] == 1){ ?>
									<div class="printedLabel">Printed</div>
								<?php } ?>
							</td>

							<td width="100" align="right"><?php echo $row['estimated_delivery_date']; ?></td>
						</tr>
					</table>
				</a>
				</td></tr>
				<?php
				}
			?>
			
		</table>	
    </div>	
    <script type="text/javascript">
		$(document).ready(function(){
			$('#instantSearch').keyup(function(){

				var val = $('#instantSearch').val();
				console.log(val);

				var xhttp = new XMLHttpRequest();
				xhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        $('#intakeAjax').html(this.responseText);
                    }
				};

				xhttp.open("POST", "/ajax/deliverynotePageList.php", true);
				xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				xhttp.send("searchterm=" + val);
			
			});
        });
    </script>
</main>
<div id="btm"></div>
</body>
</html>