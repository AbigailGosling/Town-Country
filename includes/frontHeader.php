<?php
	include('./functions.php');
	
?>

<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Town &amp; Country</title>
		<link href="css/style.css" rel="stylesheet" type="text/css">
		<link href="css/font-awesome.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="css/toastr.css">
		<link href="css/lity.css" rel="stylesheet" type="text/css">
	
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.0/jquery.min.js"></script>
		<script src="https://code.jquery.com/ui/jquery-ui-git.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
		
		<script type="text/javascript" src="//cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
		<script type="text/javascript" src="/js/jquery.ui.touch-punch.min.js"></script>
		<script type="text/javascript" src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>		
		
		<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
		<link rel="stylesheet" href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">
		
		<link href="https://fonts.googleapis.com/css?family=Handlee&display=swap" rel="stylesheet">
		<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
		<script src="js/lity.js"></script>
		<script>
			
		</script>
	</head>
<body onafterprint="printCompleted()">

<div id="networkError">
	<img src="/images/wifilogo.png"><br/>
	Please check your internet connection
</div>

<script type="text/javascript">
	function goBack() {
		window.history.back();
	}
</script>
