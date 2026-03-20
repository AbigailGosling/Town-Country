<?php

use App\Models\User;

	include_once('functions.php');
    $userModel = User::find($user['id']);
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
	<a href="logout" id="logout">LOGOUT</a>
</div>
<main>
	<h1 class="int">Town &amp; Country 3</h1>
	<div id="menu_wrap">
		<?php
			$ids = $user['pages'];
			if(count(explode(",",$ids)) == 0){ header('location:/logout'); exit; die();}
			$resultsColumn1 = prepareExecuteQuery("SELECT * FROM `page_permissions` WHERE `column` = 1 && `id` IN ($ids)");
			$count = mysqli_num_rows($resultsColumn1);

			if($count > 0){
				?><div class="col"><h1>Sales & Purchasing</h1><?php
				while($page = mysqli_fetch_array($resultsColumn1)){
					?><a href="<?php echo $page['file']; ?>"><?php echo $page['name']; ?></a><?php
                    if ($page['file'] =="productpicker.php") { ?>
                    <a href="../shortstock">Short Dated Stock</a>
                    <?php
                    }
                    if ($page['file'] =="containerreservation.php") { ?>
                        <a href="reservationList.php">Reservations</a>
                    <?php
                    }
				}

				?></div>
                <?php
			}


			$resultsColumn2 = prepareExecuteQuery("SELECT * FROM `page_permissions` WHERE `column` = 2 && `id` IN ($ids)");
			$count = mysqli_num_rows($resultsColumn2);

			if($count > 0){
				?><div class="col"><h1>Goods in/out</h1><?php
				while($page = mysqli_fetch_array($resultsColumn2)){
					?><a href="<?php echo $page['file']; ?>"><?php echo $page['name']; ?></a><?php
                    if ($page['file'] == "../outgoing-pallets/") {
                        ?>
                        <a href="../outgoing-pallets/loading"><span class="small">Outgoing</span> Pallet Loading</a>
                        <?php
                    }
				}

				?>
                </div><?php
			}


			$resultsColumn3 = prepareExecuteQuery("SELECT * FROM `page_permissions` WHERE `column` = 3 && `id` IN ($ids)");
			$count = mysqli_num_rows($resultsColumn3);

			if($count > 0){
				?><div class="col"><h1>Admin. Tools</h1><?php
				while($page = mysqli_fetch_array($resultsColumn3)){
					if($page['file'] == 'exportstock.php'){
						?><a onclick="exportstock(this,'<?php echo htmlspecialchars($page['name']); ?>')" href="<?php echo $page['file']; ?>"><?php echo $page['name']; ?></a><?php
					}else if($page['file'] == 'exportStockPDF.php'){
						?><a onclick="exportstockPDF(this,'<?php echo htmlspecialchars($page['name']); ?>')" href="<?php echo $page['file']; ?>"><?php echo $page['name']; ?></a><?php
                    }else{
                        if ($page['file'] == "../supplierreturnstatements/") continue;
						?><a href="<?php echo $page['file']; ?>"><?php echo $page['name']; ?></a><?php
                        if ($userModel->hasPermission("supplierreturnstatements") && $page['file'] == "manageSuppliers.php") {
                            ?>
                            <a href="../supplierreturnstatements/"><span class="small">Supplier</span> Rtn/Crds Statement</a>
                            <a href="../supplierreturnstatements/?history=1"><span class="small">Supplier</span> Return History</a>
                            <?php
                        }
                        if ($page['file'] == '../users') {
                            ?><a href="../bulkpermissions/"><span class="small">Bulk</span> Permission Management</a><?php
                        }
					}
				}
                if (in_array($userModel->id,[54,11,99]))
                {
                    ?><a href="https://wolverhamptonitservices-poc.sharefile.eu/home/shared/fo850975-7286-4c8e-9850-851b58f0d8ae">Management Spreadsheet</a><?php
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
</html>
