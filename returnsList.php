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
	</script>
</head>
<body class="menu">
<div id="top">
	<a href="menu.php" id="menu">MENU</a>
	<a href="logout.php" id="logout">LOGOUT</a>
</div>
<main>
	<div id="intakelist">
		<h1 class="int">Returns Intake LIST</h1>
		<input type="text" id="instantSearch" placeholder="Search.." style="width:260px;height:28px;padding-left:10px;">
		
		<a href="intakeList.php" class="resetBtn">Clear</a>
		<div class="datesearchcontainer">
			<label>MONTH</label>
			<select id="month">
				<?php for($i=1;$i<13;$i++){ ?>
					<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
				<?php } ?>
			</select>
			 
			<label>YEAR</label>
			<select id="year">
				<?php for($i=2018;$i<2020;$i++){ ?>
					<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
				<?php } ?>
			</select>
						
		</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0" id="intakeAjax">
			<?php
				$x = "SELECT * FROM `intake` WHERE returned=1 ORDER BY date_received DESC";
				$y = mysqli_query($conn, $x) or die(mysqli_error($conn));
				while($row = mysqli_fetch_array($y)){
				
				
				
				$date_received = date('d/m/Y', strtotime($row['date_received']));
				?>
				<tr><td align="center" class="pos">

					<a href="intake.php?id=<?php echo $row['id']; ?>" class="intake">
						<table width="100%" border="0">
							<tr>
								<?php
									$customer = getCustomer($row['supplier_id']);
								?>
								<td width="100" align="left">ID: I-0000<?php echo $row['id']; ?></td>
								<td align="center" style="font-size: 18px;"><?php echo $customer['businessname']; ?></td>
								<td width="100" align="right"><?php echo $date_received; ?></td>
							</tr>
						</table>
					</a>
					
					 
					<a href="javascript:;" onclick="deleteRow('<?php echo $row['id'];?>')" id="delete_intake"><i class="fa fa-times" aria-hidden="true"></i></a>

				</td></tr>
				<?php
				}
			?>
		</table>
	</div>
</main>
<div id="btm"></div>
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

				xhttp.open("POST", "/ajax/returnsPageList.php", true);
				xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				xhttp.send("searchterm=" + val);
			
			});
			
			
			$('#month').change(function(){
				
				month = $('#month').val();
				year = $('#year').val();
				
				loadSearchDate(month, year);
				
			});
			
			$('#year').change(function(){
				
				month = $('#month').val();
				year = $('#year').val();
				
				loadSearchDate(month, year);
				
			});
			
		});
		
		function loadSearchDate(month, year){
			
			$('#instantSearch').val('');
			
			console.log('month: ' + month);
			console.log('year: ' + year);
			
			var xhttp = new XMLHttpRequest();
			xhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
			  $('#intakeAjax').html(this.responseText);
			}
			};

			xhttp.open("POST", "/ajax/intakePageListDate.php", true);
			xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xhttp.send("month=" + month + '&year=' + year);

			
		}
		
		function deleteRow(intake_id, pallet_id){
			if(confirm('Are you sure you want to delete this?')){
				window.location.href = "/scripts/deleteIntake.php?intake_id=" + intake_id;
				// console.log(intake_id + '  ' + pallet_id);
			}
		}
	</script>
</body>
</html>