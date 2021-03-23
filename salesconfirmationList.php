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
		<h1 class="int">Sales Confirmation LIST</h1>
		<input type="text" id="instantSearch" placeholder="Search.." style="width:260px;height:28px;padding-left:10px;">
		
		<a href="salesconfirmationList.php" class="resetBtn">Clear</a>
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
            
            <span style="position: absolute;right: -109px;color: #fff;font-weight: bold;top: 10px;">SENT</span>
		</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0" id="intakeAjax">
			<?php
				$x = "SELECT * FROM `pickerSheets` ORDER BY date DESC";
				$y = mysqli_query($conn, $x) or die(mysqli_error($conn));

				$page_limit = 50;
				$num_of_pages = 1;
				$entry_count = 0;
				while($picksheet = mysqli_fetch_array($y)){
					$entry_count++;
					if($entry_count == $page_limit){
						$entry_count = 0;
						$num_of_pages++;
					}

					$date_purchased = date('d/m/Y', strtotime($picksheet['date']));
				?>
					<tr class="pages page<?php echo $num_of_pages; ?>"><td align="center" class="pos">
						<a href="viewSalesconfirmation.php?id=<?php echo $picksheet['id']; ?>" class="intake">
							<table width="100%" border="0">
								<tr>
									<td width="25%" align="left">ID: P-00<?php echo $picksheet['id']; ?> </td>
									<td align="left" style="font-size: 14px;">
										<?php
										
											$customer_id = $picksheet['customer_id'];
											$x1 = "SELECT * from `customers` WHERE id='$customer_id'";
											$y1 = mysqli_query($conn, $x1);
											
											$customer = mysqli_fetch_array($y1);
										
										?>
										<?php echo $customer['businessname'] . '  <span style="text-transform:lowercase;">t/a</span>  ' . $customer['tradingas']; ?>

										<?php
											if($picksheet['deleted'] == 1 && $picksheet['completed'] == 0){
												echo "(VOID)";
												if($picksheet['deleted_by_user_id'] != ''){
													echo " - " . getUsername($picksheet['deleted_by_user_id']);
												}
											}
										?>
									</td>
									<td width="25%" align="right"> created <?php echo $date_purchased; ?></td>
								</tr>
							</table>
						</a>
						 
                     
            
			            <div class="sendcontainer">
                            <div class="active" picksheetid="<?php echo $picksheet['id']; ?>" <?php if($picksheet['sent'] == 0){ echo 'style="display:none;"'; }?>>
                                <i class="fa fa-check" aria-hidden="true"></i>
                            </div>
                        </div>

					</td></tr>
					<?php
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

            $('.sendcontainer').click(function(){
                var value = 0;
                
                if($(this).find('.active').css('display') == 'none'){ 
                    value = 1;
                }else{
                    value = 0;
                }

                var picksheetid = $(this).find('.active').attr('picksheetid');
                
                $.get("/ajax/togglePicksheetSent.php?picksheet=" + picksheetid + '&status=' + value, function(data, status){
                });

                $(this).find('.active').toggle();
            });

			$('#instantSearch').keyup(function(){

				var val = $('#instantSearch').val();
				console.log(val);


				var request = $.ajax({
					type: "POST",
					url: "ajax/salesConfirmationList.php",
					data: {
						searchterm: val
					},
					dataType: "html"
				});

				request.done(function(data) {
					$('#intakeAjax').html(data);
					loadPage(1);
				});

				request.fail(function(jqXHR, textStatus) {
					// alert( "Request failed: " + textStatus );
				});
			
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

			xhttp.open("POST", "/ajax/salesConfirmationListDate.php", true);
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