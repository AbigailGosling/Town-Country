<?php
    include_once('functions.php');
    
    if($_GET['delid'] != ''){
    

        $delid = mysqli_real_escape_string($conn, $_GET['delid']);


        $picksheetResult = mysqli_query($conn, "UPDATE `pickerSheets` SET deleted=1 WHERE id='$delid'");

        $pickerItemsResult = mysqli_query($conn, "UPDATE `pickerItems` SET deleted=1 WHERE pickersheet_id='$delid'");

        $palletsOutResult = mysqli_query($conn, "SELECT * FROM `palletsOut` WHERE pickersheet_id='$delid'");

        while($palletOut = mysqli_fetch_array($palletsOutResult)){
            $weightIDS = $palletOut['weight_ids'];

            $deleteWeightsResult = mysqli_query($conn, "UPDATE `weights` SET status_id='0' WHERE id IN ($weightIDS)");
        }

        $x = "DELETE FROM `palletsOut` WHERE pickersheet_id='$delid'";
        $y = mysqli_query($conn, $x);

        ?> <script> window.location.href = '/pickSheetList.php'; </script> <?php
    }

?>
<!doctype html>
<html class="int">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Town &amp; Country</title>
<link href="css/style.css" rel="stylesheet" type="text/css">
<link href="css/font-awesome.css" rel="stylesheet" type="text/css">

</head>
<body class="menu">
<div id="top">
	<a href="menu.php" id="menu">MENU</a>
	<a href="logout.php" id="logout">LOGOUT</a>
</div>
<main>
    <h1 class="int">Your Pick Sheets</h1>	
    <br/><br/>
	<div id="menu_wrasp">
 		<?php
		
			session_start();
			
			$userid = $_SESSION['USER'];
			
 			$x = "SELECT * FROM `pickerSheets` WHERE completed='0' && deleted !='1' ORDER BY estimated_delivery_date ASC";
			$y = mysqli_query($conn, $x);
			
			while($row = mysqli_fetch_array($y)){
				$customer_id = $row['customer_id'];
				
				$date = $row['date'];
				
				$date=date_create($date);
				$date = date_format($date,"d.m.Y");
				
				
				$x2 = "SELECT * FROM `customers` WHERE id ='$customer_id'";
				$y2 = mysqli_query($conn, $x2);
				
				$row2 = mysqli_fetch_array($y2);
				
            ?>
            <div class="menuItem">
                <div class="text">[Ord Nr. 0000<?php echo $row['id']; ?>]&nbsp;&nbsp;<?php echo $row2['businessname'] . '&nbsp;&nbsp;(date created ' . $date.')&nbsp;&nbsp;(Delivery Date ' . $row['estimated_delivery_date'].')';?></div>
                <div class="actions">
                    <a href="/viewPickSheet.php?id=<?php echo $row['id']; ?>" class="icon"><i class="fa fa-pencil" style="padding-right:4px;" aria-hidden="true"></i></a>
                    <a href="javascript:;" onclick="if(confirm('Are you sure you want to delete this?')){ window.location.href= '/pickSheetList.php?delid=<?php echo $row['id']; ?>'; }" class="icon"><i class="fa fa-close" style="padding-right:4px;" aria-hidden="true"></i></a>
                </div>
            </div>
            <div style="position:relative;display:none;">
                <a href="viewPickSheet.php?id=<?php echo $row['id']; ?>">[Ord Nr. 0000<?php echo $row['id']; ?>]&nbsp;&nbsp;<?php echo $row2['businessname'] . '&nbsp;&nbsp;(date created ' . $date.')';?></a>
                
                <a href="/manageSuppliers.php?id=<?php echo $row['id']; ?>">
                    <i class="fa fa-pencil" style="padding-right:4px;" aria-hidden="true"></i>
                </a>
            </div>
            
            <?php
			}
        ?>
         
        
	</div>	
</main>
<div id="btm"></div>
</body>
</html>