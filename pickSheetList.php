<?php
    include_once('functions.php');
    
    if($_GET['delid'] != ''){
    

        $delid = mysqli_real_escape_string($conn, $_GET['delid']);
        
        $picksheetResult = mysqli_query($conn, "UPDATE `pickerSheets` SET deleted=1, deleted_by_user_id=$userid WHERE id='$delid'");

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
                $product_ids = array();
                
                $picksheetid = $row['id'];

                $result_product = mysqli_query($conn, "SELECT product_id FROM `pickerItems` WHERE pickersheet_id='$picksheetid' GROUP BY product_id");
                while($product = mysqli_fetch_array($result_product)){
                    array_push($product_ids, $product['product_id']);
                } 

                $product_ids = implode(',', $product_ids);

                // 1 is fresh
                $result_fresh = mysqli_query($conn, "SELECT id FROM `product` WHERE id IN ($product_ids) && cooling_id='1' LIMIT 1");
                $count_fresh = mysqli_num_rows($result_fresh);

                // 2 is frozen
                // 3 is fresh/frozen

                $result_frozen= mysqli_query($conn, "SELECT id FROM `product` WHERE id IN ($product_ids) && cooling_id IN (2,3) LIMIT 1");
                $count_frozen = mysqli_num_rows($result_frozen);
                
                
                $customer_id = $row['customer_id'];
				
				$date = $row['date'];
				
				$date=date_create($date);
				$date = date_format($date,"d.m.Y");
				
				
				$x2 = "SELECT * FROM `customers` WHERE id ='$customer_id'";
				$y2 = mysqli_query($conn, $x2);
				
				$row2 = mysqli_fetch_array($y2);
				
            ?>
            <?php if($count_fresh == 1 && $row['completed_fresh'] == '0'){ ?>
                <div class="menuItem">
                    <div class="tag fresh">FRESH</div>
                    <div class="text">[Ord Nr. 0000<?php echo $row['id']; ?>]&nbsp;&nbsp;<?php echo $row2['businessname'] . '&nbsp;&nbsp;(date created ' . $date.')&nbsp;&nbsp;(Delivery Date ' . $row['estimated_delivery_date'].')';?></div>
                    <div class="actions">
                        <a href="/viewPickSheet.php?type=fresh&id=<?php echo $row['id']; ?>" class="icon"><i class="fa fa-pencil" style="padding-right:4px;" aria-hidden="true"></i></a>
                        <a href="javascript:;" onclick="if(confirm('Are you sure you want to delete this?')){ window.location.href= '/pickSheetList.php?delid=<?php echo $row['id']; ?>'; }" class="icon"><i class="fa fa-close" style="padding-right:4px;" aria-hidden="true"></i></a>
                    </div>
                </div>
            <?php } ?>

            <?php if($count_frozen == 1 && $row['completed_frozen'] == '0'){ ?>
                <div class="menuItem">
                    <div class="tag frozen">FROZEN</div>
                    <div class="text">[Ord Nr. 0000<?php echo $row['id']; ?>]&nbsp;&nbsp;<?php echo $row2['businessname'] . '&nbsp;&nbsp;(date created ' . $date.')&nbsp;&nbsp;(Delivery Date ' . $row['estimated_delivery_date'].')';?></div>
                    <div class="actions">
                        <a href="/viewPickSheet.php?type=frozen&id=<?php echo $row['id']; ?>" class="icon"><i class="fa fa-pencil" style="padding-right:4px;" aria-hidden="true"></i></a>
                        <a href="javascript:;" onclick="if(confirm('Are you sure you want to delete this?')){ window.location.href= '/pickSheetList.php?delid=<?php echo $row['id']; ?>'; }" class="icon"><i class="fa fa-close" style="padding-right:4px;" aria-hidden="true"></i></a>
                    </div>
                </div>
            <?php } ?>


            <?php
			}
        ?>
         
        
	</div>	
</main>
<div id="btm"></div>
<style>
    .tag{
        position:absolute;
        left:10px;
        padding:2px 5px;
        color:#fff;
        font-size: 16px;
        height: 30px;
        line-height: 30px;
        top: 8px;
        width: 70px;
    }
    .tag.fresh{ background:#c0392b; }
    .tag.frozen{ background:#2980b9; }
</style>
</body>
</html>