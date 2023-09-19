<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

    include_once('includes/frontHeader.php');
    if(request()->input('delid') != ''){
        $delid = request()->input('delid');
        $picksheetResult = prepareExecuteQuery("UPDATE `pickerSheets` SET deleted=1, deleted_by_user_id=? WHERE id=?",'ii',[$userid,$delid]);
        $pickerItemsResult = prepareExecuteQuery("UPDATE `pickerItems` SET deleted=1 WHERE pickersheet_id=?",'i',[$delid]);
        $palletsOutResult = prepareExecuteQuery("SELECT * FROM `palletsOut` WHERE pickersheet_id=?",'i',[$delid]);
            $deleteWeightsResult = prepareExecuteQuery("UPDATE `weights` SET status_id='0' WHERE id IN ($weightIDS)");
        $x = "DELETE FROM `palletsOut` WHERE pickersheet_id=?";
        $y = prepareExecuteQuery($x,'i',[$delid]);

        ?> <script> window.location.href = '/pickSheetList.php'; </script> <?php
    }
?>
<!doctype html>
<html class="int">
<head>
<meta charset="utf-8">
<title>Town &amp; Country</title>
<link href="css/style.css" rel="stylesheet" type="text/css">
<link href="css/font-awesome.css" rel="stylesheet" type="text/css">

</head>
<body class="menu">
<div id="top">
	<a href="menu.php" id="menu">MENU</a>
	<a href="logout" id="logout">LOGOUT</a>
</div>
<main>
    <h1 class="int">Your Pick Sheets</h1>	
    <br/><br/>
	<div id="menu_wrasp" style="width:95%;">
 		<?php
		
			session_start();session_write_close();
			
			$userid = $_SESSION['USER'];
 			$x = "SELECT * FROM `pickerSheets` WHERE completed='0' && deleted !='1' ORDER BY STR_TO_DATE(estimated_delivery_date,'%d/%m/%y') ASC";
			$y = prepareExecuteQuery($x);
			
			while($row = mysqli_fetch_assoc($y)){
                $product_ids = array();
                
                $picksheetid = $row['id'];

                $result_product = prepareExecuteQuery("SELECT product_id FROM `pickerItems` WHERE pickersheet_id='$picksheetid' GROUP BY product_id");
                while($product = mysqli_fetch_assoc($result_product)){
                    $product_ids[]=$product['product_id'];
                } 
                             
                if (count($product_ids)>0)
                {
                    $product_ids = implode(',', $product_ids);
                    $result_fresh = prepareExecuteQuery("SELECT id FROM `product` WHERE id IN ($product_ids) && cooling_id='1' LIMIT 1");
                    $count_fresh = mysqli_num_rows($result_fresh);
                    if ($count_fresh>0)
                    {
                        $result_location_fresh= prepareExecuteQuery("SELECT GROUP_CONCAT(DISTINCT pallet.storage_location) as loc FROM `product` INNER JOIN pallet ON product.pallet_id = pallet.id WHERE product.id IN ($product_ids) && product.cooling_id IN (1) LIMIT 1");
                        $location_fresh = mysqli_fetch_assoc($result_location_fresh);
                        $location_fresh = $location_fresh["loc"];
                    }

                    // 2 is frozen
                    // 3 is fresh/frozen

                    $result_frozen= prepareExecuteQuery("SELECT id FROM `product` WHERE id IN ($product_ids) && cooling_id IN (2,3) LIMIT 1");
                    $count_frozen = mysqli_num_rows($result_frozen);
                    
                    if ($count_frozen>0)
                    {
                        $result_location_frozen= prepareExecuteQuery("SELECT GROUP_CONCAT(DISTINCT pallet.storage_location) as loc FROM `product` INNER JOIN pallet ON product.pallet_id = pallet.id WHERE product.id IN ($product_ids) && product.cooling_id IN (2,3) LIMIT 1");
                        $location_frozen = mysqli_fetch_assoc($result_location_frozen);
                        $location_frozen = $location_frozen["loc"];
                    }
                }
                else
                {
                    $count_frozen = 0;
                    $count_fresh = 0;
                }
                    
                    
                $customer_id = $row['customer_id'];
				
				$date = $row['date'];
				
				$date=date_create($date);
				$date = date_format($date,"d/m/Y");
				
				
				$x2 = "SELECT * FROM `customers` WHERE id ='$customer_id'";
				$y2 = prepareExecuteQuery($x2);
				
				$row2 = mysqli_fetch_assoc($y2);
				
            ?>
            <?php if($count_fresh == 1 && $row['completed_fresh'] == '0'){ rowprinter(false,$row,$row2,$date,explode(",",$location_fresh));?>

            <?php } ?>

            <?php if($count_frozen == 1 && $row['completed_frozen'] == '0'){ rowprinter(true,$row,$row2,$date,explode(",",$location_frozen)); ?>
                
            <?php } ?>


            <?php
			}
            function rowprinter($isFrozen,$row,$row2,$date,$locs)
            {
                $t = "FRESH";
                if ($isFrozen == true) $t = "FROZEN";
                if (count($locs) > 2) $loc = $locs[0] . "<br/>" . $locs[1] . "<br/>+ More...";
                else if (count($locs) > 1) $loc = implode("<br/>",$locs);
                else $loc = $locs[0];
            ?>
              <div class="menuItem">
                    <div>
                        <table style="width:95%;height:52px;">
                            <tr style="height:52px;">
                                <td align="left" style="width:85px;white-space: nowrap;" onclick="location.href='viewPickSheet.php?type=<?php echo strtolower($t);?>&id=<?php echo $row['id']; ?>';" ><div class="tag <?php echo strtolower($t);?>"><?php echo $t;?></div>
                                <td align="left" style="height:52px;font-size:12px;width:53px;white-space: nowrap;" onclick="location.href='viewPickSheet.php?type=<?php echo strtolower($t);?>&id=<?php echo $row['id']; ?>';">Ord: <?php echo $row['id']; ?></td>
                                <td align="left" style="width:460px;height:52px;font-size:18px;white-space: nowrap;" onclick="location.href='viewPickSheet.php?type=<?php echo strtolower($t);?>&id=<?php echo $row['id']; ?>';"><?php echo $row2['businessname'];?></td>
                                <td align="left" style="height:52px;font-size:8px;width:50px;white-space: nowrap;">(Created <?php echo $date;?>)</td>
                                <td align="left" style="width:14%;height:52px;font-size:18px;white-space: nowrap;">(Delv <?php echo $row['estimated_delivery_date'];?>)</td>
                                <td align="right" style="height:52px;font-size:12px;width:70px;white-space: nowrap;line-height:1"><?php echo $loc;?></td>
                            </tr>
                        </table>
                    </div>
                    <?php
                        $currentUser = User::find(Auth::id());
                        if ($currentUser->hasPermission("deletePick")){
                    ?>
                     <div class="actions">
                        <a href="javascript:;" onclick="if(confirm('Are you sure you want to delete this?')){ doDelete(<?php echo $row['id']; ?>); }" class="icon"><i class="fa fa-close" style="padding-right:4px;" aria-hidden="true"></i></a>
                    </div>
                    <?php
                        }
                    ?>
                </div>
            <?php
            }
        ?>
         
        
	</div>	
</main>
<div id="btm"></div>
<style>
    .tag{
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
<script>
    $.ajaxSetup({
		headers: { 'X-CSRF-TOKEN': "<?php echo csrf_token();?>" }
	});
    function doDelete(id){
        $.post("ajax/deletePick.php", {'id':id}, results);
    }
    function results(){
        location.reload();
    }
</script>
</body>
</html>