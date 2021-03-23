<?php
    include('functions.php');
    
    $limit = (isset($_GET['limit'])) ? $_GET['limit'] : '100';
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
	<script type="text/javascript" src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
</head>
<body class="menu">
<div id="top">
	<a href="menu.php" id="menu">MENU</a>
	<a href="logout.php" id="logout">LOGOUT</a>
</div>
<main>
	<div id="intakelist">
		<h1 class="int">Intake LIST</h1>
		<input type="text" id="instantSearch" placeholder="Search.." style="width:260px;height:28px;padding-left:10px;">
		
		<a href="intakeList.php" class="resetBtn">Clear</a>
		<div class="datesearchcontainer">
			<label>MONTH</label>
			
			<select id="month">
				
				<?php for($i=1;$i<13;$i++){

					if(date("n") == $i) { ?>

						<option value="<?php echo $i; ?>" selected><?php echo $i; ?></option>

					<?php }else{ ?>

						<option value="<?php echo $i; ?>"><?php echo $i; ?></option>

				<?php 
					}
				} 
				?>
			</select>
			 
			<label>YEAR</label>
			<select id="year">
				<?php
				$y = date('Y');
				
				for($i = 0; $i < 3; $i++){ ?>
					<option value="<?php echo $y; ?>"><?php echo $y; ?></option>
				<?php $y--; } ?>

			</select>
						
		</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0" id="intakeAjax">
			<?php
				$queryResult = mysqli_query($conn, "SELECT * FROM `intake` ORDER BY date_received DESC, id DESC LIMIT $limit");

				while($intake = mysqli_fetch_array($queryResult)){
					$date_received = date('d/m/Y', strtotime($intake['date_received']));
					?>
					<tr><td align="center" class="pos">

						<a href="intake.php?id=<?php echo $intake['id']; ?>" class="intake">
							<table width="100%" border="0">
							<tr>
								<?php
									$r = intakePriceComplete($intake['id']);    
								?>
								<td width="30%" align="left">
									ID: I-0000<?php echo $intake['id'];?></td>
								<td align="left" style="font-size: 18px;" class="<?php if($r == 1){ echo 'flex space-between v-center'; } ?>">
									<?php

										if($intake['returned'] == '1'){
											$cusDetails =  getCustomer($intake['supplier_id']);
											if(!empty($cusDetails) && isset($cusDetails['businessname'])){
												echo $cusDetails['businessname'];
											}else{
												echo 'No Customer Data';
											}

										}else{
											echo supplierName($intake['supplier_id']);
										}
										if($intake['returned'] == '1'){ echo ' <small class="return-highlight">return entry</small>'; }

										if($r == 1){
										?><i class="fa fa-check" aria-hidden="true" style="margin-left:10px;"></i><?php
										}
									?>
								</td>
								<td width="30%" align="right"><?php echo $date_received; ?></td>
							</tr>
							</table>
						</a>
						<a href="javascript:;" onclick="deleteRow('<?php echo $intake['id'];?>')" id="delete_intake"><i class="fa fa-times" aria-hidden="true"></i></a>
					</td></tr>
					<?php
                }
                
                if($limit == 100){
                ?>
                <tr><td align="center" class="pos">

                <a href="intakeList.php?limit=99999999" class="intake">
                    <table width="100%" border="0">
                        <tr>
                            <td width="100" align="center">Load All</td>
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
	<script type="text/javascript">

                
        function doSearch(){
            console.log('doSearch..');
            var val = $('#instantSearch').val();
            

            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                $('#intakeAjax').html(this.responseText);
            }
            };

            xhttp.open("POST", "/ajax/intakePageList.php", true);
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhttp.send("searchterm=" + val);
        }

		$(document).ready(function(){

			$('#instantSearch').keyup(function(){
				doSearch();
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
		
		function deleteRow(intake_id){
			swal({
				title: "Are you sure?",
				text: "Intake #"+ intake_id + " will be deleted",
				icon: "warning",
				buttons: true,
				dangerMode: true,
			})
			.then((confirmed) => {
				if (confirmed) {
					window.location.href = "/scripts/deleteIntake.php?intake_id=" + intake_id;
				}
			});
		}
	</script>
</body>
</html>