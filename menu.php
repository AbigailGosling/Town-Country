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
</head>
<body class="menu">
<div id="top">
	<a href="logout.php" id="logout">LOGOUT</a>
</div>
<main>
	<h1 class="int">Town &amp; Country 3</h1>	
	<div id="menu_wrap">
		
		<?php if($user['type'] == 1 || $user['type'] == 3 || $user['type'] == 4){ ?>
		<div class="col">
			<h1>Sales & Purchasing</h1>
			<a href="productpicker.php"><span class="small">create</span> Sale</a>
			<a href="salesconfirmationList.php">Sales Confirmation</a>
			<a href="createPurchase.php"><span class="small">create</span> Purchase</a>
 			<a href="purchaseList.php">Purchases</a>
			<a href="intakeList.php"><span class="small">edit</span> Intake</a>
			<a href="calendar.php">Expected Arrivals</a>
		</div>
		<?php } ?>
		
		<?php if($user['type'] == 2 || $user['type'] == 3|| $user['type'] == 4){ ?>
		<div class="col">
			<h1>Goods in/out</h1>
			<a href="newDelivery.php"><span class="small">create</span>  Intake</a>
			<a href="newReturn.php"><span class="small">create</span>  Return</a>
			<a href="calendar.php">Expected Arrivals</a>
			<a href="pickSheetList.php">Pick Sheets</a>
			<a href="completedPickerSheets.php">Completed Pick Sheets</a>
			<a href="deliverynoteList.php"><span class="small">print</span> Delivery Notes</a>
			<a href="intakeList.php"><span class="small">edit</span> Intake</a>
		</div>
		<?php } ?>
		
		<?php if($user['type'] == 3){ ?>		
		<div class="col">
			<h1>Admin. Tools</h1>
			<a href="manageCutgroups.php"><span class="small">edit</span>  Cut Groups</a>
			<a href="manageCuts.php"><span class="small">edit</span>  Cut</a>
 			<a href="manageNationalities.php"><span class="small">edit</span> Nationality</a>
 			<a href="manageSuppliers.php"><span class="small">edit</span> Supplier</a>
 			<a href="manageBrands.php"><span class="small">edit</span> Brand</a>
 			<a href="manageCustomers.php"><span class="small">edit</span> Customer</a>
  			<a href="intakeList.php"><span class="small">edit</span> Intake</a>
  			<a href="stock.php"><span class="small">edit</span> Stock</a>
  			<a href="returnsList.php"><span class="small">edit</span> Returns</a>
  			<a href="invoiceList.php"><span class="small">edit</span> Invoices</a>
  			<a href="editUsers.php"><span class="small">edit</span> Users</a>
		</div>
		<?php } ?>
		
		<div style="display:none;">
  		</div>
		<?php if($userid == 1){ ?>
			<a href="completedPurchase.php" style="display:none;">Completed purchase</a>
		<?php } ?>
	</div>
	
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