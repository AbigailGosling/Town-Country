<?php

use Carbon\Carbon;

	include('includes/frontHeader.php');

	$picksheetid = request()->input('id');

	$x = "SELECT * FROM `pickerSheets` WHERE id =?";
	$y = prepareExecuteQuery($x,'i',[$picksheetid]);

	$pickerSheet = mysqli_fetch_array($y);


	$customerName = customerName($pickerSheet['customer_id']);

?>
<div id="top">
	<a href="menu.php" id="menu">MENU</a>
	<a href="logout" id="logout">LOGOUT</a>
</div>
<script type="text/javascript">

    function printStuff(){
        $('#top').hide();
        $('.printhide').hide();
        $('.formBackButton').hide();
        $('.backbtn').hide();
        $('main').css('padding','0px')

        window.print();
	}

	function printCompleted() {
		$('#top').show();
		$('.printhide').show();
		$('.formBackButton').show();
		$('.backbtn').show();
		$('main').removeAttr("style")
	}
</script>
<style>
    @media print {

        .product_block{
            break-inside: avoid;
        }
    }

</style>
<main class="int">

	<a href="<?php echo $domain; ?>completedPickerSheets.php" class="backbtn">< Back</a>


    <h1>PICKSHEET <?php echo '#' . str_pad($picksheetid, 4, "0", STR_PAD_LEFT); ?></h1>

    <div class="flex space-between">
        <div class="picksheet_buttons printhide">
            <a class="picksheet_btn" href="javascript:;" onclick="printStuff()">Print</a>
            <a class="picksheet_btn" href="viewCompletedPickSheet.php?id=<?php echo $pickerSheet['id']; ?>">Pick Note</a>
            <a class="picksheet_btn" href="deliverynote.php?id=<?php echo $pickerSheet['id']; ?>">Delivery Note</a>
            <a class="picksheet_btn" href="invoice.php?id=<?php echo $pickerSheet['id']; ?>">Invoice</a>
        </div>
        <?php if($pickerSheet['completedby_userid'] != ''){ ?>
        <h4 class="completedby-tag">
        <?php
            	$pickersQ = "SELECT GROUP_CONCAT(picker_ids) as pickers FROM `pickWeightOut` WHERE pickersheet_id =?";
                $pickersR = prepareExecuteQuery($pickersQ,'i',[$picksheetid]);

                $pickersA = mysqli_fetch_assoc($pickersR);
                $pickersA = explode(",",$pickersA['pickers']);
                $pickersA[] = $pickerSheet['completedby_userid'];
                $pickersA = array_unique($pickersA);
                $pickersB = array();
                foreach ($pickersA as $picker)
                {
                    $pickersB[] = getUsername($picker);
                }
        ?>
            <i class="fa fa-check" aria-hidden="true" style="margin-right:10px;"></i>
            Sale Made: <?php echo Carbon::createFromFormat('Y-m-d H:i:s',$pickerSheet['date'])->format('d/m/Y H:i') ?></br>
            Pickers: <?php echo implode(", ",$pickersB); ?></br>
            Picksheet completed by: <?php echo getUsername($pickerSheet['completedby_userid']); ?></br>
            Pick Completed: <?php echo Carbon::createFromFormat('Y-m-d H:i:s',$pickerSheet['date_completed'])->format('d/m/Y H:i') ; ?></br>
            Estimated Delivery Date: <?php echo $pickerSheet['estimated_delivery_date']; ?></h4>

        <?php } ?>
    </div>

    <div>
            <?php
                $outpalletQuery = "SELECT * FROM `pickWeightOut` WHERE pickersheet_id=?";
                $outpalletResult2 = prepareExecuteQuery($outpalletQuery,'i',[$picksheetid]);

                $outpalletCount = mysqli_num_rows($outpalletResult2);

                while($outpallet = mysqli_fetch_array($outpalletResult2)){
                    $weightids = explode(',', $outpallet['weight_ids']);
                    ?><h3 style="text-align:left;">Outgoing Pallet: <?php echo str_pad($outpallet['id'], 5, '0', STR_PAD_LEFT); ?></h3><?php

                    $productIDArray = array();

                    foreach($weightids as $weightid){
                        $x = "SELECT * FROM `weights` WHERE id=?";
                        $y = prepareExecuteQuery($x,'i',[$weightid]);
                        $weight = mysqli_fetch_array($y);

                        if(!in_array($weight['product_id'], $productIDArray)){
                            array_push($productIDArray, $weight['product_id']);
                        }

                        $queryBits .= ' id = ' . $weightid . ' || ';
                    }

                    foreach($productIDArray as $productID){

                        $x1 = "SELECT * FROM `product` WHERE id='$productID'";
                        $y1 = prepareExecuteQuery($x1,'i',[]);
                        $product = mysqli_fetch_array($y1);


                        if($product['unit'] == 'PPC'){
                            $ext = ' Cases';
                        }else{
                            $ext = ' kg';
                        }

                        $x2 = "SELECT * FROM `weights` WHERE product_id='$productID' AND id IN (".implode(",",array_fill(0,count($weightids),"?")).")";

                        $y2 = prepareExecuteQuery($x2,str_repeat('i',count($weightids)),$weightids);
                        $count = mysqli_num_rows($y2);

						$k = 0;
                        while($weight = mysqli_fetch_array($y2)){

                            if($weight['weight_tear'] == $weight['weight_gross']){
                                (double)$w = (double)$weight['weight_gross'];
                            }else{
                                (double)$w = (double)$weight['weight_gross'] - (double)$weight['weight_tear'];
                            }

                            $k = $k + $w;
                        }
						?>
						<div class="product_block">
						<div><?php echo $count; ?> <?php echo getSpeciesFromCutID($product['cut_id']); ?> - <?php echo getCut($product['cut_id']); ?> [<?php echo $k . $ext; $k = 0; ?>] <?php echo '[Plt. ID : '.$product['pallet_id'].']'; ?></div>
						<?php

						?>
						<div class="pickerSheetType_content" style="position:relative;">
						<div class="picksheetPalletDetail" style="display:block;padding-left:0px;">
						<div class="row">
                        <?php
                        $k = 0;
						$y22 = prepareExecuteQuery($x2,str_repeat('i',count($weightids)),$weightids);
                        while($weight2 = mysqli_fetch_array($y22)){

                            if($weight2['weight_tear'] == $weight2['weight_gross']){
                                $w = (double)$weight2['weight_gross'];
                            }else{
                                $w = (double)$weight2['weight_gross'] - (double)$weight2['weight_tear'];
                            }

							?><div class="weightbox"><?php echo $w; ?></div><?php
                            $k = $k + $w;
                        }
						?>
						</div>
						</div>
						</div>
						</div>
						<?php
                    }
                }
            ?>
        </div>
</main>
</body>
</html>
