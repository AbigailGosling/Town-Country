<?php
	include('functions.php');
?>
<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Town &amp; Country</title>
		<link href="css/style.css" rel="stylesheet" type="text/css">
		<link href="css/font-awesome.css" rel="stylesheet" type="text/css">
		<link href="css/lity.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
		<script src="https://code.jquery.com/jquery-1.12.4.js"></script><script src="https://malsup.github.io/jquery.form.js"></script>
		<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
		<script src="/legacy/js/lity.js"></script>
	</head>
	<body>
	<div id="top">
		<a href="menu.php" id="menu">MENU</a>
		<a href="logout" id="logout">LOGOUT</a>
	</div>
	<main class="int" style="width:100%;max-width:100%;">
		<div class="calendar" id="calendar" style="width:100%"><?PHP include('get_calendar2.php'); ?>
	</main>
	<div id="btm"></div>
		<script>
			$.ajaxSetup({
		headers: { 'X-CSRF-TOKEN': "<?php echo csrf_token();?>" }
	});
			function updateCalendar(year, chilled_filter, week, display_col,site_id){

				$.get("get_calendar2.php?y=" + year + "&w=" + week + '&temperature_id=' + chilled_filter + '&display_col=' + display_col + '&site_id=' + site_id, function(data, status){
					$('#calendar').html(data);
				});

			}
			function updateCalendarByMonth(year, chilled_filter, month, display_col,site_id){

				$.get("get_calendar2.php?y=" + year + "&m=" + month + '&temperature_id=' + chilled_filter + '&display_col=' + display_col + '&site_id=' + site_id, function(data, status){
					$('#calendar').html(data);
				});

			}

		</script>
	</body>
</html>
