<script type="text/javascript">
    function autoToggleRow(classs,thisclass, productid){
        
        var ele = $('.' + thisclass);

        toggleRow(classs,ele, productid);
    }

    function toggleRow(classs, ele,intake_id,cut_id,nationality_id){
        $.get( "/scripts/_searchPickerNew.php?intake_id="+intake_id+"&cut_id=" + cut_id+"&class=" + classs + "&nationality_id=" + nationality_id, function( data ) {
            $(ele).parent().after(data);
            $(ele).next().fadeIn();
            $(ele).remove();
        });
    }

    function toggleVisibleRow(classs){
        $('.' + classs).toggle();
    }
</script>
<table width="100%" class="slim searchRContent"   style="display:table;">
    <thead>
        <tr class="searchRContent__head">
	        <th>Intake ID</th>
	        <th>Location</th>
	        <th>Plt ID</th>
            <th></th>
	        <th>Unit</th>
	        <th>Chilled/Frozen</th>
	        <th>Product</th>
	        <th>Nationality</th>
	        <th width="20%">Comments</th>
	        <th>Brand</th>
	        <th>Date Range</th>
	        <th>Volume</th>
	        <th>Cost</th>
	        <th>RRP</th>
	        <th></th> 
        </tr>
    </thead>
<?php
    $time1 = microtime(true);
	require('../functions.php');
	
	$cutgroup_id = $_GET['cutgroup_id'];
	$species_id = $_GET['species'];
	$temperatureID = $_GET['temperatureID'];
	$pallet_id = $_GET['palletID'];
	$intake_id = $_GET['intakeID'];
    
     
    $ARRAY_CUTS = array();

    // ??: Gets the same cuts twice here #1
    $ARRAY_CUTS = cutsFromCutGroup($species_id, $cutgroup_id);

    $whereArray = [];

    if($species_id != '' && $cutgroup_id != ''){ # if these two are posted then they've used the species and cutgroup dropdown
        // ??: and here #2
        // $ARRAY_CUTS = cutsFromCutGroup($species_id, $cutgroup_id); # get array of all the cut_id's from the cutgroup 
        $ids = implode(',', $ARRAY_CUTS);

        if(count($ARRAY_CUTS) > 0){ # seems to still get here if i dont do this if??
            array_push($whereArray, 'product.cut_id IN ('.$ids.')');
        }
    }

    if($pallet_id != ''){ # if this is posted then theyve entered a pallet id
        array_push($whereArray, "pallet.id = '". $pallet_id ."'");
    }

    if($intake_id != ''){ # if this is posted then theyve entered a intake id
        $ARRAY_PALLET_IDS = palletIDsFromIntakeID($intake_id); # get array of all the cut_id's from the cutgroup 
        $ids = implode(',', $ARRAY_PALLET_IDS);

        array_push($whereArray, 'pallet.id IN ('.$ids.')');
    }

    // array_push($whereArray, "product.status='0'");

    array_push($whereArray, "product.cost != '0.00'");

    
    foreach($whereArray as $where){
        $whereString .= $where . ' && ';
    }
    $whereString = substr($whereString, 0, -3);


    $productsX = "SELECT *, product.comments as productcomments, product.id as productid FROM `product` INNER JOIN `pallet` ON product.pallet_id=pallet.id
    WHERE $whereString
    GROUP BY pallet.intake_id, product.cut_id,product.nationality_id ORDER BY product.cut_id DESC";
    
    $productsY = mysqli_query($conn, $productsX);
    $productsCount = mysqli_num_rows($productsY);
     
    $totalW = 0;
    
    $products = mysqli_fetch_all($productsY, MYSQLI_ASSOC);
    
    foreach($products as $productsRow){

        $thisclass = 'thisclass'.rand(1,999999);
        $class = 'KIS'.rand(1,999999);
        $pallet_id = $productsRow['pallet_id'];
        $cut_id = $productsRow['cut_id'];
        $temp_id = $productsRow['cooling_id'];
        $ubbb = $productsRow['ubbb'];
        $smallestDate = $productsRow['range_from'];
        $largestDate = $productsRow['range_to'];
        // ??: Don't we already have the intake_id from the query?
        $intake_id = intakeIDfromPalletID($pallet_id);
        $nationality_id = $productsRow['nationality_id'];
        $cut = getCut($productsRow['cut_id']);
        if($ubbb == 0){
            $ubtext = 'UB';
        }else if($ubbb == 1){
            $ubtext = 'BB';
        }else{
            $ubtext = 'N/A';
        }

        
        $productsX2 = "SELECT product.cut_id, product.pallet_id, product.id productid FROM `product` INNER JOIN `pallet` ON product.pallet_id=pallet.id WHERE pallet.intake_id='$intake_id' && product.cut_id = '$cut_id' && product.nationality_id='$nationality_id' ORDER BY product.cut_id DESC";
        $productsY2 = mysqli_query($conn, $productsX2) or die(mysqli_error($conn));
        $products2Count = mysqli_num_rows($productsY2);
        
        
        ###
        $products2 =  mysqli_fetch_all($productsY2, MYSQLI_ASSOC);

        $product2_palletids = array();
        $product2_cutids = array();
        $product2_productids = array();
        
        array_map(
            function($product2) {
                global $product2_palletids;
                global $product2_cutids;
                global $product2_productids;

                array_push($product2_palletids, $product2['pallet_id']);
                array_push($product2_cutids, $product2['cut_id']);
                array_push($product2_productids, $product2['productid']);
            },
        $products2);
        
        $quantityTotal = countNumProductsForCutOnPalletArrays($product2_palletids, [$product2_cutids[0]], $nationality_id);
        
        if($quantityTotal < 1){continue;}
        ###
       
        $totalW += weightSoldFromProductID($productsRow['productid']);           
        $totalProducts = weightsAvailableOnProduct($productsRow['productid']);
        //$numOfWeights = countNumProductsForCutOnPalletThatIsntPicked($pallet_id, $cut_id);
       
        ?>
        <tr class="searchAccordTitle">
            
            
            <td colspan="1">
                <a href="intake.php?id=<?php echo $intake_id; ?>&ref=salesconfirmationsheet" style="color:#000;text-decoration:underline;">
					<b><?php echo $intake_id; ?></b>
				</a>
			</td>
            <td colspan="1">
             &nbsp;		 
            </td>
            <td colspan="1"  onclick=""></td>
           <td width="40" align="center" class="<?php echo $thisclass; ?>" onclick="toggleRow('<?php echo $class; ?>', this,'<?php echo $intake_id; ?>','<?php echo $productsRow['cut_id']; ?>','<?php echo $nationality_id;?>');"><?php if($products2Count > 0){ ?><i class="fa fa-chevron-down"></i><?php } ?></td>
            <td width="40" align="center" onclick="toggleVisibleRow('<?php echo $class; ?>')" style="display:none"><?php if($products2Count > 0){ ?><i class="fa fa-chevron-down"></i><?php } ?></td>
            <td class="bold" colspan="1"><?php echo $quantityTotal; ?></td>
            <!---
            // ??: No need to call the database on every loop.
            // ??: The temperatures are just a few entries.
            // ??: Better to get all the entries in the beginning
            -->
            <td <?php if($temp_id == 1){ echo 'style="background:#c0392b;color:#fff;padding:5px;"'; }else { echo 'style="background:#2980b9;color:#fff;padding:5px;"'; } ?>><?php echo getTemp($temp_id); ?></td>
            <td class="bold" colspan="1"><?php echo $cut; ?></td>
            <!--
            // ??: Same as with temperatures - get all entries in the beginning
            -->
			<td colspan="1"><?php echo getNationality($productsRow['nationality_id']); ?></td>
			<td colspan="1">
				<form method="post">
					<textarea name="comments" class="overviewcomment" productid="<?php echo $productsRow['productid']; ?>"><?php echo $productsRow['weightnote']; ?></textarea>
					<input type="text" name="pallet_id" class="pallet" value="<?php echo $pallet_id; ?>" style="display:none;">
				</form>
			</td>
			<td><?php echo getBrand($productsRow['brand_id']); ?></td>
			<td><?php if($ubbb != 2){ echo $ubtext . ' ' . $smallestDate . ' - ' . $largestDate; }else { echo $ubtext; } ?></td>
            <td class="bold"><?php 
                
                if($productsRow['akg'] != ''){
                   echo totalWeightOfAdvisedKGProduct($intake_id);
                }else{
                    echo $totalWeightOfProduct = totalWeightOfProduct($product2_productids);
                }

 				?>kg</td>
			<td class="bold"><?php  if($productsRow['cost']){ echo '£' . number_format((float)$productsRow['cost'], 2, '.', ''); } ?></td>
			<td class="bold"><?php  if($productsRow['price']){ echo '£' . number_format((float)$productsRow['price'], 2, '.', ''); } ?></td>
        </tr>
    <?php  ?>

    <?php 
    }
 ?>


<script type="text/javascript">

 
function getCookie(name) {
var value = "; " + document.cookie;
var parts = value.split("; " + name + "=");
if (parts.length == 2) return parts.pop().split(";").shift();
}

$('.searchRHeading').click(function(){
$(this).next('.searchRContent').toggle();
});

function addToSheet(product_id, pallet_id, cut_id, theClass){

var q = $('#quantity-' + product_id + '-' + pallet_id).val();
var comment = $('#comment-' + product_id + '-' + pallet_id).val();


// console.log(comment);

var COOKIE_NAME = "quantity-"+product_id+"-"+pallet_id;
// console.log('Looking for cookie......:' + COOKIE_NAME);


if(getCookie(COOKIE_NAME)){
    // console.log('we got cookie');
    
    var howMany = getCookie(COOKIE_NAME);
    
    var x = Number(howMany)+Number(q);
    document.cookie = COOKIE_NAME + "=" + x;
    // console.log(howMany);
    
}else{
    // console.log('setting cookie!');
    document.cookie = COOKIE_NAME + "=" + q;
}
     
var howManyBefore = $('#quantity-' + product_id + '-' + pallet_id).children('option').length;
if(howManyBefore > q){
    for(i=0; i < q; i++){
        $("#quantity-" + product_id + "-" + pallet_id + " option:last").remove();
    }
}else{
    for(i=0; i < q; i++){
        $("#quantity-" + product_id + "-" + pallet_id + " option:last").remove();
        $("#quantity-" + product_id + "-" + pallet_id).parent().parent().css('opacity','0.6');
        $("#quantity-" + product_id + "-" + pallet_id).parent().parent().css('pointer-events','none');
    }
}

var howManyAfter = $('#quantity-' + product_id + '-' + pallet_id).children('option').length;

$('#quantity-' + product_id + '-' + pallet_id).val($('#quantity-' + product_id + '-' + pallet_id + ' option:last').val());

$.get( "/scripts/getBasketItem.php?product_id="+product_id+"&pallet_id="+pallet_id+"&cut_id="+cut_id+"&q="+q+"&comment="+comment, function( data ) {
    $('.basketTable').append(data);
});

//$('#loadResults').html('');
// $('#KIS428319').toggle();
}
 
function toggleWeight(weightdiv){
if($(weightdiv).hasClass('activeWeight')){
    var weight = $(weightdiv).attr('weight');
    var product_id = $(weightdiv).attr('product_id');
    calculateWeight(-weight);
    removeFromList(product_id);
    
}else{
    var weight = $(weightdiv).attr('weight');
    var product_id = $(weightdiv).attr('product_id');
    calculateWeight(weight);
    addToList(product_id);
}

$(weightdiv).toggleClass('activeWeight');

}

function calculateWeight(value){
var currentWeight = $('.weightVal').text();

var newWeight = parseFloat(currentWeight) + parseFloat(value);

$('.weightVal').text(newWeight);

}

$(document).ready(function(){
 
$.each(document.cookie.split(/; */), function()  {
  var splitCookie = this.split('=');

    
    if(splitCookie[0].includes('quantity-')){
        console.log(splitCookie[0]);
        var q = splitCookie[1];
        
        var howManyBefore = $('#' + splitCookie[0]).children('option').length;
        
        if(howManyBefore > q){
            for(i=0; i < q; i++){
                $('#' + splitCookie[0] + " option:last").remove();
            }
        }else{
            for(i=0; i < q; i++){
                $('#' + splitCookie[0] + " option:last").remove();
                $('#' + splitCookie[0]).parent().parent().css('opacity','0.3');
                $('#' + splitCookie[0]).parent().parent().css('pointer-events','none');
            }
        }	
    }
});

 
 

$('.overviewcomment').each(function(){
    $(this).on('keypress', function(e){
        if(e.which == 13){
            var currentComment = $(this).val();
            //currentComment += "#";
            $(this).val(currentComment);
            
            console.log('CurrentComment: ' + currentComment);
             // var pallet = $(this).parent().find('.pallet').val();
            
            var productid = $(this).attr('productid');
            // var productid = 10;
            
            // $.get("<?php echo $domain; ?>ajax/saveCommentPicker.php?comment="+currentComment+'&productid=1'+productid, function(data, status){
                // console.log(data);
            // });
            
            $.ajax({
                method: "POST",
                url: "<?php echo $domain; ?>ajax/saveCommentPicker.php",
                data: {
                    comment:currentComment,
                    productid:productid
                },
            }).done(function( result ) {
                console.log(result);
            });
            

        }
    });
});

$('.quantitybox').change(function(){
                 
     $('.subrow').removeClass('activeRedRow');
    $(this).parent().parent().addClass('activeRedRow');
 });
});
</script>
<style type="text/css">
.weightbox{
padding:10px;
border:2px solid #cacaca;
display:inline-block;
cursor:pointer;
margin-bottom:5px; 
}
.activeWeight { background:#3faddd !important; color:#fff !important}
.weightbox:hover{
background:#cacaca;
}
</style>

<?php 
function perfcheck()
{
    global $time1;
    $time2 = microtime(true);
    echo '<br>script execution time: ' . ($time2 - $time1);
}
// perfcheck();

?>