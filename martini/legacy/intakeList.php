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
		<input type="text" id="instantSearch" placeholder="Search.." style="width:260px;height:28px;padding-left:10px;" enterkeyhint="go">
		<input type="hidden" id="toSkipCount" value="0">
		<input type="hidden" id="totalRowsCount" value="0">
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
			 
		</table>
		<div class="loadMoreBtn" onclick="loadRows()">Load More</div>
	</div>
</main>
<div id="btm"></div>
	<script type="text/javascript">

                
        function doSearch(){
			
			$('.loadMoreBtn').hide();

            console.log('doSearch..');
            var val = $('#instantSearch').val();
            

            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                $('#intakeAjax').html(this.responseText);
            }
            };

            xhttp.open("POST", "ajax/intakePageList.php", true);
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xhttp.setRequestHeader('X-CSRF-TOKEN', "<?php echo csrf_token();?>");
            xhttp.send("searchterm=" + val);
        }

		$(document).ready(function(){

			// load initial 80 rows
			loadRows();

			$('#instantSearch').on('keypress',function(e) {
				if(e.which == 13) {
					doSearch();
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

			xhttp.open("POST", "ajax/page-list/intakeList.php", true);
			xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xhttp.setRequestHeader('X-CSRF-TOKEN', "<?php echo csrf_token();?>");
			xhttp.send("toSkip=" + toSkip);
		}

		function loadSearchDate(month, year){
			
			$('.loadMoreBtn').hide();

			$('#instantSearch').val('');
			
			console.log('month: ' + month);
			console.log('year: ' + year);
			
			var xhttp = new XMLHttpRequest();
			xhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
			  $('#intakeAjax').html(this.responseText);
			}
			};

			xhttp.open("POST", "ajax/intakePageListDate.php", true);
			xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xhttp.setRequestHeader('X-CSRF-TOKEN', "<?php echo csrf_token();?>");
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
		<?php
		$ucheck = prepareExecuteQuery("SELECT `user_type` FROM `users` WHERE `id` = ?",'i',[$userid]);
		$ucheck = $ucheck->fetch_assoc();
        if ($ucheck['user_type'] == "A")
        {
		?>
		function date_paid_changed(dateText,inst)
		{
			var id = inst.id.toString().replace("date_paid_","");
			$.post( "ajax/updateIntakeDatePaid.php", { date: dateText, id: id } );
		}
		<?php
        }
    	?>
	</script>
</body>
</html>