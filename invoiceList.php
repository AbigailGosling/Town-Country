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
		<h1 class="int">Invoice List</h1>
		<input type="text" id="instantSearch" placeholder="Search.." style="width:260px;height:28px;padding-left:10px;">
		<table width="100%" border="0" cellpadding="0" cellspacing="0" id="intakeAjax">
			<?php
			
				session_start();
				
				$userid = $_SESSION['USER'];
				
				$x = "SELECT * FROM `pickerSheets` WHERE completed='1' ORDER BY `id` DESC";
				$y = mysqli_query($conn, $x);
				
				$page_limit = 50;
				$num_of_pages = 1;
				$entry_count = 0;
				while($row = mysqli_fetch_array($y)){
					$entry_count++;

					if($entry_count == $page_limit){
						$entry_count = 0;
						$num_of_pages++;
					}
					$customer_id = $row['customer_id'];
					
					$date = $row['estimated_delivery_date'];
					
					$date=date_create($date);
					$date = date_format($date,"d/m/Y");
					
					$x2 = "SELECT * FROM `customers` WHERE id ='$customer_id'";
					$y2 = mysqli_query($conn, $x2);
					$row2 = mysqli_fetch_array($y2);
					
				?>
				<tr class="pages page<?php echo $num_of_pages; ?>"><td align="center" class="pos">
				<a href="invoice.php?id=<?php echo $row['id']; ?>" class="intake" style="padding-left:10px;padding-right:10px;">
					<table width="100%" border="0">
						<tr>
							<td width="100" align="left">ID: 0000<?php echo $row['id']; ?></td>
							<td align="center" style="font-size: 18px;"><?php echo $row2['businessname']; ?></td>
							<td width="100" align="right"><?php echo $row['estimated_delivery_date']; ?></td>
						</tr>
					</table>
				</a>

                <div class="sendcontainer sendcontainer--invoice-list">
                    <div class="active" picksheetid="<?php echo $row['id']; ?>" <?php if($row['invoicesent'] == 0){ echo 'style="display:none;"'; }?>>
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
                
                $.get("/ajax/toggleInvoiceSent.php?picksheet=" + picksheetid + '&status=' + value, function(data, status){
                    //alert("Data: " + data + "\nStatus: " + status);
                });

                $(this).find('.active').toggle();
            });

			$('#instantSearch').on('keypress',function(e) {
				if(e.which == 13) {
					doSearch();
				}
			});
        });

		function doSearch(){
			var val = $('#instantSearch').val();

			var request = $.ajax({
				type: "POST",
				url: "ajax/invoicePageList.php",
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
		}
    </script>
</main>
<div id="btm"></div>
</body>
</html>