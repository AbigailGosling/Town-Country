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
		
		<input type="hidden" id="toSkipCount" value="0">
		<input type="hidden" id="totalRowsCount" value="0">

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
		</table>
		<div class="loadMoreBtn" onclick="loadRows()">Load More</div>
	</div>
</main>

 
<div id="btm"></div>
	<script type="text/javascript">
		
		$(document).ready(function(){
			
			// load initial 80 rows
			loadRows();

			$('#instantSearch').keypress(function(){
				if (event.keyCode === 13)
				{
					var val = $('#instantSearch').val();
					var request = $.ajax({
						type: "POST",
						url: "ajax/page-list/salesConfirmationList.php",
						data: {
							searchterm: val
						},
						dataType: "html"
					});

					request.done(function(data) {
						$('#intakeAjax').html(data);
					});

					request.fail(function(jqXHR, textStatus) {
						// alert( "Request failed: " + textStatus );
					});
				}			
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
		
		function loadRows(){

			var toSkip = $('#toSkipCount').val();

			var xhttp = new XMLHttpRequest();
			xhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
				$('#intakeAjax').append(this.responseText);


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

				setTimeout(() => {
					var toSkip = parseInt($('#toSkipCount').val());
					var totalRowsCount = parseInt($('#totalRowsCount').val());

					if(toSkip >= totalRowsCount){
						$('.loadMoreBtn').hide();
					}else{
						$('.loadMoreBtn').show();
					}
				}, 1000);
			}
			};

			xhttp.open("POST", "/ajax/page-list/salesConfirmationList.php", true);
			xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xhttp.send("toSkip=" + toSkip);
		}
		
		function loadSearchDate(month, year){
			
			$('#instantSearch').val('');
			
			console.log('month: ' + month);
			console.log('year: ' + year);
			
			var xhttp = new XMLHttpRequest();
			xhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
			  $('#intakeAjax').html(this.responseText);
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
			}
			};

			xhttp.open("POST", "/ajax/page-list/salesConfirmationListDate.php", true);
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