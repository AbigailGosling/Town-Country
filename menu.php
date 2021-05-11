<?php
	include_once('functions.php');
?>
<!doctype html>
<html class="int">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Town &amp; Country 3</title>
	<link href="css/style.css" rel="stylesheet" type="text/css">
	<link href="css/responsive.css" rel="stylesheet" type="text/css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.0/jquery.min.js"></script>
	<script type="text/javascript" src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>		
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
</head>
<body class="menu">
<div id="top">
	<a href="logout.php" id="logout">LOGOUT</a>
</div>
<main>
	<h1 class="int">Town &amp; Country 3</h1>	
	<div id="menu_wrap">
		<?php
			$ids = $user['pages'];
		?>

		<?php
			$resultsColumn1 = mysqli_query($conn, "SELECT * FROM `page_permissions` WHERE `column` = 1 && `id` IN ($ids)");
			$count = mysqli_num_rows($resultsColumn1);

			if($count > 0){
				?><div class="col"><h1>Sales & Purchasing</h1><?php
				while($page = mysqli_fetch_array($resultsColumn1)){
					?><a href="<?php echo $page['file']; ?>"><?php echo $page['name']; ?></a><?php
				}

				?></div><?php
			}

 
			$resultsColumn2 = mysqli_query($conn, "SELECT * FROM `page_permissions` WHERE `column` = 2 && `id` IN ($ids)");
			$count = mysqli_num_rows($resultsColumn2);

			if($count > 0){
				?><div class="col"><h1>Goods in/out</h1><?php
				while($page = mysqli_fetch_array($resultsColumn2)){
					?><a href="<?php echo $page['file']; ?>"><?php echo $page['name']; ?></a><?php
				}

				?></div><?php
			}


			$resultsColumn3 = mysqli_query($conn, "SELECT * FROM `page_permissions` WHERE `column` = 3 && `id` IN ($ids)");
			$count = mysqli_num_rows($resultsColumn3);

			if($count > 0){
				?><div class="col"><h1>Admin. Tools</h1><?php
				while($page = mysqli_fetch_array($resultsColumn3)){
					if($page['file'] == 'exportstock.php'){
						?><a onclick="exportstock(this,'<?php echo htmlspecialchars($page['name']); ?>')" href="<?php echo $page['file']; ?>"><?php echo $page['name']; ?></a><?php
					}else if($page['file'] == 'exportStockPDF.php'){
						?><a onclick="exportstockPDF(this,'<?php echo htmlspecialchars($page['name']); ?>')" href="<?php echo $page['file']; ?>"><?php echo $page['name']; ?></a><?php
					}else{
						?><a href="<?php echo $page['file']; ?>"><?php echo $page['name']; ?></a><?php
					}
				}

				?></div><?php
			}
		?>
	</div>

	<script>
		function exportstock(ele, original_name){
			$(ele).html('<img src="img/loading.gif" height="30" style="margin-top:5px"> <span>Please wait..</span>');

			setTimeout(() => {
				$(ele).html(original_name);
			}, 15000);
		}

		function exportstockPDF(ele, original_name){
			$(ele).html('<img src="img/loading.gif" height="30" style="margin-top:5px"> <span>Please wait..</span>');

			setTimeout(() => {
				$(ele).html(original_name);
 			}, 15000);
		}

	</script>
	<style>
		#menu_wrap{
			display:flex !important;
			justify-content:justify-content;
		}
		
		#menu_wrap .col h1{
			color: #fff;
			width: 100%;
			font-size: 20px;
		}
		
		#menu_wrap .col{
			width:100%;
		}
	</style>
</main>
<div id="btm"></div>
</body>
<?php
	if($_GET['msg'] != ''){
	?>
	<script type="text/javascript">
		alert('<?php echo $_GET['msg']?>');
	</script>
	<?php
	}
?>

</html>