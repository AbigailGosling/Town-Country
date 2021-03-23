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
		<h1 class="int">Purchase LIST</h1>
		<input type="text" id="instantSearch" placeholder="Search.." style="width:260px;height:28px;padding-left:10px;">
		
		<a href="purchaseList.php" class="resetBtn">Clear</a>
		<div class="datesearchcontainer">
			<label>MONTH</label>
			<select id="month">
				<?php for($i=1;$i<13;$i++){ ?>
					<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
				<?php } ?>
			</select>
			 
			<label>YEAR</label>
			<select id="year">
				<?php
					$end = (int) date('Y', strtotime('+5 year'));
					
					for($i=2017;$i<$end;$i++){ ?>
					<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
				<?php } ?>
			</select>
		</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0" id="intakeAjax">
			<?php
				$x = "SELECT * FROM `purchase_form` ORDER BY date_due DESC";
				$y = mysqli_query($conn, $x) or die(mysqli_error($conn));

				$page_limit = 50;
				$num_of_pages = 1;
				$entry_count = 0;
				while($row = mysqli_fetch_array($y)){
					$entry_count++;

					if($entry_count == $page_limit){
						$entry_count = 0;
						$num_of_pages++;
					}

				$id = $row['id'];
				// $ix = "SELECT id from intake WHERE purchase_id='$id'";
				// $iy = mysqli_query($conn, $ix);
				// $irow = mysqli_fetch_array($iy);
				// $iID = $irow['id'];
				
				$x1 = "SELECT * FROM `intake` WHERE purchase_id='$id'";
				$y1 = mysqli_query($conn, $x1);
				$intake = mysqli_fetch_array($y1);
				$intakeCount = mysqli_num_rows($y1);
				
				// if($count == 0){
						
					$date_purchased = date('d/m/Y', strtotime($row['date_due']));
					?>
					<tr class="pages page<?php echo $num_of_pages; ?>"><td align="center" class="pos">
						<a href="createPurchase.php?id=<?php echo $row['id']; ?>" class="intake">
							<table width="100%" border="0">
								<tr>
									<td width="35%" align="left">ID: P-00<?php echo $row['id']; ?> </td>
									<td width="60%" align="left" style="font-size: 16px;">
										<?php if($row['direct_drop'] == 1){ echo '<span style="font-size:12px;">[direct drop]</span>'; } ?>
										<?php echo supplierName($row['supplier_id']); ?>
										<?php if($row['booking_ref_number'] == ''){ ?><span style="color:red;padding-left:5px;font-size:26px;font-weight:700">!</span><?php } ?>
										
										<?php
											$thisid = $row['id'];
											
											$x2 = "SELECT * FROM `intake` WHERE purchase_id='$thisid'";
											$y2 = mysqli_query($conn, $x2);
											$count22 = mysqli_num_rows($y2);
											
											if($intakeCount != 0){
											?> <div class="printedLabel">Intake Created</div> <?php
											}else{
											?>  <?php
											}
										?>
									</td>
									<td width="35%" align="right"><?php echo $date_purchased; ?></td>
									<td width="10%" align="right">
										<i class="peek-products fa fa-product-hunt" aria-hidden="true"></i>
										<div class="tooltip-content">
											<?php
												$species = explode('|', $row['species']);
												$cuts = explode('|', $row['cut']);
												$units = explode('|', $row['units']);
												$prices = explode('|', $row['price']);
												
												$size = sizeof($species);
											?>
											<table>
												<tr>
													<th>Species</th>
													<th>Cuts</th>
													<th>Units</th>
													<th>Prices</th>
												</tr>
												<?php for($i=0; $i < $size; $i++){ ?>
													<tr>
														<td><?php echo $species[$i]; ?></td>
														<td><?php echo $cuts[$i]; ?></td>
														<td><?php echo $units[$i]; ?></td>
														<td><?php echo $prices[$i]; ?></td>
													</tr>
													<?php } ?>
											</table>
										</div>
									</td>
								</tr>
							</table>
						</a>
						
						 
						<a href="javascript:;" onclick="deleteRow('<?php echo $row['id'];?>')" id="delete_intake"><i class="fa fa-times" aria-hidden="true"></i></a>

					</td></tr>
					<?php
				// }
				}
			?>
			<tr>
				<td>
					<div class="pages_container">
						<div class="flex" style="align-items:center;justify-content:flex-end;">
							<p style="color:#fff;padding-right:10px;font-weight:bold">Jump to page</p>
							<?php $num_of_pages_temp = $num_of_pages+1; ?>
							<select style="width:60px;height:30px;" onchange="changePage(this)">
								<?php for($i=1;$i<($num_of_pages_temp); $i++){ ?>
									<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
								<?php } ?>
							</select>
						</div>
					</div>
				</td>
			</tr>
		</table>
		</table>
	</div>
</main>
<div id="btm"></div>
	<script type="text/javascript">

		function changePage(ele){
			var page = $(ele).val();
			$('.pages').hide();
			$('.page' + page).fadeIn();
		}

		function loadPage(page){
			$('.pages').hide();
			$('.page' + page).fadeIn();
		}

		$(document).ready(function(){
			loadPage(1);
			$('#instantSearch').keyup(function(){

				var val = $('#instantSearch').val();
				console.log(val);

				var xhttp = new XMLHttpRequest();
				xhttp.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200) {
					$('#intakeAjax').html(this.responseText);
					loadPage(1);
				}
				};

				xhttp.open("POST", "/ajax/purchasePageList.php", true);
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
			
			$('.peek-products').hover(
				function() {
					$(this).parent().find('.tooltip-content').show();

				},
				function() {
					$(this).parent().find('.tooltip-content').hide();
				}
			);
			
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

			xhttp.open("POST", "/ajax/purchasePageListDate.php", true);
			xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xhttp.send("month=" + month + '&year=' + year);

			
		}
		
		function deleteRow(purchase_id){
			if(confirm('Are you sure you want to delete this?')){
				window.location.href = "/scripts/deletePurchase.php?purchase_id=" + purchase_id;
			}
		}
	</script>
</body>
</html>