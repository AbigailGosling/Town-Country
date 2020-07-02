<?php
	include('functions.php');
	
	if($_GET['id'] != ''){
		
		$id = mysqli_real_escape_string($conn, $_GET['id']);
		
		$x = "SELECT * FROM `purchase_form` WHERE id='$id'";
		$y = mysqli_query($conn, $x);
		
		$purchase = mysqli_fetch_array($y);
		$edit=true;
		
	}else{ $edit=false; }
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
		<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
		<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
		<script src="/js/lity.js"></script>
	</head>
	<body>
	<div id="top">
		<a href="menu.php" id="menu">MENU</a>
		<a href="logout.php" id="logout">LOGOUT</a>
	</div>
	<main class="int">
		<div class="calendar" id="calendar"><?PHP include('get_calendar2.php'); ?>
	</main>
	<div id="btm"></div>
		<script>
			var count = 0;
			function moveCalendar(ID){
				if(ID == '+1' ){
					count++;
				}else{
					count--;
				}
				
				// console.log('Local Count: ' + count);
				
				$.get("/get_calendar2.php?id=" + count, function(data, status){
					$('#calendar').html(data);
				});
			}
			
			function showEvent(element){
				$('.allevents').fadeOut();
				$('#' + element).fadeIn();
			}
			
				
			function updateCalendar(month, year){
				$.get("/get_calendar2.php?m=" + month + "&y=" + year + "&w=1", function(data, status){
					$('#calendar').html(data);
				});
				console.log('month: ' + month + ' / year:' + year );
			}
		
		</script>
	</body>
</html>
