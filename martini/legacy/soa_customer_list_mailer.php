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
	<script src="https://code.jquery.com/jquery-1.12.4.js"></script><script src="https://malsup.github.io/jquery.form.js"></script> 
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<script type="text/javascript" src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
</head>
<body class="menu">
<div id="top">
	<a href="menu.php" id="menu">MENU</a>
	<a href="logout" id="logout">LOGOUT</a>
</div>
<main>
	<div id="intakelist">
		<h1 class="int">Customers S.O.A 2</h1>
        <input type="text" id="instantSearch" placeholder="Search.." style="width:260px;height:28px;padding-left:10px;" enterkeyhint="go">
		<input type="hidden" id="toSkipCount" value="0">
		<input type="hidden" id="totalRowsCount" value="0">
		<a href="soa_customer_list_mailer.php" class="resetBtn">Clear</a>
		<a style="float:right" href="#" id="bulk-sender" class="resetBtn">Send</a>
		<table width="100%" border="0" cellpadding="0" cellspacing="0" id="intakeAjax">
			 
		</table>
		<div class="loadMoreBtn">Loading Please Wait...</div>
	</div>
</main>
<div id="btm"></div>
	<script type="text/javascript">
		$.ajaxSetup({
		headers: { 'X-CSRF-TOKEN': "<?php echo csrf_token();?>" }
	});
		$(document).ready(function(){

			// load initial 80 rows
			loadRows();

			$('#instantSearch').on('keypress',function(e) {
				if(e.which == 13) {
					doSearch();
				}
			});	

		});
		$('#bulk-sender').on('click',
				function(e) {
					$('#intakeAjax').hide();
					$('.loadMoreBtn').show();
					processToSend();
				}
			);	
		var toSend = new Array();
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
						$('i.img-mail-selector').hide();
						$('a.mail-selector').click(function(e) {
							var t = $( "#img-"+this.id);
							var s = this.id.replace("mail-selector-","");
							var i = toSend.indexOf(s);
							if ($(t).is(":hidden"))
							{
								if (i == -1) toSend.push(s);											
								t.show();
							}
							else
							{
								if (i > -1) toSend.splice(i,1);
								t.hide();
							}
						});
					}else{
						$('.loadMoreBtn').show();
					}
				}, 1000);
			}
			};

			xhttp.open("POST", "ajax/page-list/SOAM_CustomerList.php", true);
			xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xhttp.setRequestHeader('X-CSRF-TOKEN', "<?php echo csrf_token();?>");
			xhttp.send("toSkip=" + toSkip + "&showBal=1");
		}
        function doSearch(){
            var value = $('#instantSearch').val();
			
			var xhttp = new XMLHttpRequest();

			xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    $('#intakeAjax').html(this.responseText);
					$('i.img-mail-selector').hide();
					$('a.mail-selector').click(function(e) {
							var t = $( "#img-"+this.id);
							var s = this.id.replace("mail-selector-","");
							var i = toSend.indexOf(s);
							if ($(t).is(":hidden"))
							{
								if (i == -1) toSend.push(s);											
								t.show();
							}
							else
							{
								if (i > -1) toSend.splice(i,1);
								t.hide();
							}
							
						});
                }
            };

            xhttp.open("POST", "ajax/page-list/SOAM_CustomerList.php", true);
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xhttp.setRequestHeader('X-CSRF-TOKEN', "<?php echo csrf_token();?>");
            xhttp.send("searchterm=" + value + "&showBal=1");
        }
		function processToSend(){
			
			if (toSend.length > 0){
				
				$.post("ajax/generatePDFstatement.php", {ids: toSend},looper);
				for (const element of toSend) {
					$('#img-mail-selector-'+element).hide();
				}
				toSend = [];
			}
			else
			{
				$('#intakeAjax').show();
				$('.loadMoreBtn').hide();
			}

		}
		function looper(data, status){
			$('#intakeAjax').show();
			$('.loadMoreBtn').hide();
		}
	</script>
</body>
</html>