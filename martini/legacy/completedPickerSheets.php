<?php

use Illuminate\Support\Facades\Log;

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
	<a href="logout" id="logout">LOGOUT</a>
</div>
<main>
	<div id="intakelist">
		<h1 class="int">Completed Picksheets</h1>
		<input type="text" id="instantSearch" placeholder="Search.." style="width:260px;height:28px;padding-left:10px;display:none;" enterkeyhint="go">
		<table width="100%" border="0" cellpadding="0" cellspacing="0" class="intakeAjax">
			<?php
			
				session_start();
				
				$userid = $_SESSION['USER'];
				
				$x = "SELECT * FROM `pickerSheets` WHERE completed='1' ORDER BY `date_completed` DESC";
				$y = $mysqli->prepare($x);
				$y->execute();
				$y = $y->get_result();
				while($row = $y->fetch_assoc()){
					$customer_id = $row['customer_id'];
					
					$date = $row['estimated_delivery_date'];
					
					$date=date_create($date);
					if ($date == false)$date=DateTime::createFromFormat('d/m/Y',"".$row['estimated_delivery_date']);
					if ($date == false) continue;
					$date = date_format($date,"d/m/Y");
					
					$x2 = "SELECT * FROM `customers` WHERE id ='$customer_id'";
					$y2 = $mysqli->prepare($x2);
					$y2->execute();
					$y2 = $y2->get_result();
					$row2 = $y2->fetch_assoc();
					
				?>
				<tr><td align="center" class="pos">
				<a href="viewCompletedPickSheet.php?id=<?php echo $row['id']; ?>" class="intake" style="padding-left:10px;padding-right:10px;">
					<table width="100%" border="0">
						<tr>
							<td width="35%" align="left">ID: <?php echo $row['id']; ?></td>
							<td align="left" style="font-size: 18px;"><?php echo $row2['businessname']; ?></td>
                            <td width="35%" align="right"><?php
                            $date = str_replace('/', '-', $row['date_completed']);
                            echo $assemblydate = date('d/m/Y', strtotime($date));
                            ?></td>
						</tr>
					</table>
				</a>
				</td></tr>
				<?php
				}
			?>
			
		</table>	
	</div>	
</main>
<div id="btm"></div>
</body>
</html>