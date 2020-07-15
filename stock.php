<?php
    include('includes/frontHeader.php');
?>
<script>
   function autoToggleRow(classs,thisclass, productid){
        var ele = $('.' + thisclass);

        toggleRow(classs,ele, productid);
    }
    function toggleRow(classs, ele,intake_id, cut_id, nationality_id){
        $.get( "/scripts/_searchStockNew.php?intake_id="+intake_id+"&cut_id=" + cut_id+"&class=" + classs + "&nationality_id="+nationality_id, function( data ) {
            $(ele).parent().after(data);
            $(ele).next().fadeIn();
            $(ele).remove();

        });
    }

    function toggleVisibleRow(classs){
        $('.' + classs).toggle();
    }

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
            
    

	.rightPanel{
		width:calc(100% - 103px);
	
		float:left;
		padding:50px;
		position:relative;
		margin-top:40px;
	}
	.leftPanel{
		width:calc(100% - 103px);
		height:100%;
		float:left;
		padding:50px;
		border:1px solid #f4f4f4;
		position:relative;
	}
	
	.leftPanel{
		background:#f2f2f2;
	}
	
	.clearfix{
		clear:both;
	}
	
	.inputbox-button{
		width:323px;
		height:34px;
		margin-bottom:10px;
	}
	
	.inputbox{
		width:300px;
		height:34px;
		padding-left:18px;
 
	}
	
	.createCustomerContainer{
		font-weight:700;
		position:absolute;
		top:50px;
		right:30px;
	}
	
	.weightTotal{
		font-weight:700;
		position:absolute;
		top:50px;
		right:30px;
	}
	
	.resultsContainer{
		width: calc(100% - 40px);
		min-height: 100px;
		border: 2px dashed #cacaca;
		padding: 0px;
		margin-top: 20px;
        padding-top: 14px;
        position:relative;
        padding-bottom:80px;
    }
    
    .gifContainer{
        position: absolute;
        bottom: -20px;
        width: 100%;
        text-align: center;
    }
</style>
<div id="top">
	<a href="menu.php" id="menu">MENU</a>
	<a href="logout.php" id="logout">LOGOUT</a>
</div>

<div class="leftPanel" style="position:relative;">
    <form method="POST" id="searchForm">
        <select id="SearchSpecies" name="species" style="width:322px;height:40px;">
            <option value="" disabled selected>Select species..</option>
            <?php
                $x = "SELECT * FROM `species`";
                $y = mysqli_query($conn, $x);
                
                while($row = mysqli_fetch_array($y)){
                ?><option value="<?php echo $row['id']; ?>" <?php if($_POST['aspecies'] == $row['id']){ echo 'selected'; } ?>><?php echo $row['name']; ?></option><?php
                }
            ?>
        </select>
        
        <select id="SearchCutgroups" name="cutgroup_id" style="width:322px;height:40px;">
            <option sid="<?php echo $rand; ?>" class="header" value="<?php echo $rand; ?>" selected>Select cut..</option>
            <?php
                $x = "SELECT * FROM `cutgroups`";
                $y = mysqli_query($conn, $x);
                
                $i=0;
                while($row = mysqli_fetch_array($y)){
                    
                    
                    $thisid = $row['species_id'];
                    $y2 = mysqli_query($conn,"SELECT * FROM species WHERE id='$thisid'");
                    $species = mysqli_fetch_array($y2);
                    $rand = 'z' . rand(6000,12212);
                        ?><option style="display:none;" sid="<?php echo $row['id']; ?>" class="allsoption s<?php echo $species['id']; ?>" value="<?php echo $row['id']; ?>"<?php if($_POST['acutgroup_id'] == $row['id']){ echo 'selected'; } ?>><?php echo $row['name']; ?></option><?php
                    }
            ?>
        </select>
        &nbsp;&nbsp;&nbsp;
        <input type="number" name="intake_id" id="IntakeID" placeholder="Intake ID" style="width:100px;height: 33px;padding-left: 10px;">
        <input type="number" name="pallet_id" id="PalletID" placeholder="Pallet ID" style="width:100px;height: 33px;padding-left: 10px;">
        <input type="button" onclick="doSearch()" value="Search" style="height: 39px;width: 80px;">
    </form>
	<div id="loadResults" class="resultsContainer">
        <?php if($_POST['cutgroup_id'] || $_POST['pallet_id'] || $_POST['intake_id']){ ?>        
            <table width="100%" class="slim searchRContent"   style="display:table;">
            <th align="left"></th>
            <th align="left">Intake ID</th>
            <th align="left">Location</th>
            <th align="left">Plt ID</th>
            <th align="left">Unit</th>
            <th align="left">Chilled/Frozen</th>
            <th align="left">Product</th>
            <th align="left">Nationality</th>
            <th align="left" width="20%">Comments</th>
            <th align="left">Brand</th>
            <th align="left">Date Range</th>
            <th align="left">Volume</th>
            <th align="left">Cost</th>
            <th align="left">RRP</th>
            <th align="left"></th>
            
           <?php 
                ?><div class="gifContainer"><center><img src="/img/loading.gif" style="padding-top:40px;padding-bottom:40px;width:40px;text-align:center;"></center></div><?php
                
                $cutgroup_id = $_POST['cutgroup_id'];
                $species_id = $_POST['species'];
                $pallet_id = $_POST['pallet_id'];
                $intake_id = $_POST['intake_id'];
                
                $whereArray = [];

                if($species_id != '' && $cutgroup_id != ''){ # if these two are posted then they've used the species and cutgroup dropdown
                    $ARRAY_CUTS = cutsFromCutGroup($species_id, $cutgroup_id); # get array of all the cut_id's from the cutgroup 
                    $ids = implode(',', $ARRAY_CUTS);

                    array_push($whereArray, 'product.cut_id IN ('.$ids.')');
                }

                if($pallet_id != ''){ # if this is posted then theyve entered a pallet id
                    array_push($whereArray, "pallet.id = '". $pallet_id ."'");
                }

                if($intake_id != ''){ # if this is posted then theyve entered a intake id
                    $ARRAY_PALLET_IDS = palletIDsFromIntakeID($intake_id);
                    $ids = implode(',', $ARRAY_PALLET_IDS);

                    array_push($whereArray, 'pallet.id IN ('.$ids.')');
                }

                foreach($whereArray as $where){
                    $whereString .= $where . ' && ';
                }
                $whereString = substr($whereString, 0, -3);

                $productsX = "SELECT *, product.comments as productcomments, product.id as productid FROM `product` INNER JOIN `pallet` ON product.pallet_id=pallet.id
    WHERE $whereString
    GROUP BY pallet.intake_id,product.cut_id,product.nationality_id ORDER BY product.cut_id DESC";
                $productsY = mysqli_query($conn, $productsX);
                $productsCount = mysqli_num_rows($productsY);

                $totalW = 0;
    
                $products =  mysqli_fetch_all($productsY, MYSQLI_ASSOC);

                $productIDs = array_map(
                    function($product) { return $product['productid']; },
                $products);
        
                // print_r($productIDs);
                $q = "SELECT * from `weights` WHERE status_id != '1' AND product_id IN(" . implode(",", $productIDs) . ")";
                $q = mysqli_query($conn, $q);
                $weights = mysqli_fetch_all($q, MYSQLI_ASSOC);
                    
               
                 
    
                foreach($products as $productsRow){
                    
                    $thisclass = 'thisclass'.rand(1,999999);
                    $class = 'KIS'.rand(1,999999);
                    $pallet_id = $productsRow['pallet_id'];
                    $cut_id = $productsRow['cut_id'];
                    $temp_id = $productsRow['cooling_id'];
                    $ubbb = $productsRow['ubbb'];
                    $smallestDate = $productsRow['range_from'];
                    $largestDate = $productsRow['range_to'];
                    $intake_id = intakeIDfromPalletID($pallet_id);
                    $nationality_id = $productsRow['nationality_id'];
                    $cut = getCut($productsRow['cut_id']);
                    
                    $ARRAY_PALLET_IDS = palletIDsFromIntakeID($intake_id);


                    if($ubbb == 0){
                        $ubtext = 'UB';
                    }else if($ubbb == 1){
                        $ubtext = 'BB';
                    }else{
                        $ubtext = 'N/A';
                    }

                    
                    $productsX2 = "SELECT * , product.id productid
                    FROM `product` 
                    INNER JOIN `pallet` 
                    ON product.pallet_id=pallet.id 
                    WHERE pallet.intake_id='$intake_id' 
                    && product.cut_id = '$cut_id'
                    && product.nationality_id = '$nationality_id'
                    ORDER BY product.cut_id DESC";
                    
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
                    
                    $quantityTotal = countNumProductsForCutOnPalletArrays($ARRAY_PALLET_IDS, [$cut_id], $nationality_id);


                    ###
                    $totalW = 0;
                    
                    $relatedWeights = [];
                    
                    array_map(function($weight) use ($productsRow, &$relatedWeights){
                        
                        if($weight["product_id"] == $productsRow["productid"])
                        {
                            $relatedWeights[] = $weight; 
                        }

                    },$weights);
                    
        
                    array_map(function($weight) use (&$totalW, $productsRow){
                    
                    
                        $w = 0;
                        if($weight['weight_tear'] == $weight['weight_gross']){
                            $w = $weight['weight_gross'];
                        }else{
                            $w = $weight['weight_gross'] - $weight['weight_tear'];
                        }
                        
                        $totalW = $totalW + $w;
                        
                    
                    },$relatedWeights);
                    
                    
               

                    $totalProducts = count($relatedWeights);
                    
                        if($totalProducts >= 1){ ?>
                          <?php
                                if($products2Count > 1){
                                ?><script>// autoToggleRow('<?php echo $class; ?>', '<?php echo $thisclass; ?>','<?php echo $productsRow['productid']; ?>'); </script><?php
                                }
                            ?>
                        <tr class="searchAccordTitle" style="<?php if($products2Count == 1){ } ?>">
                            <td width="40" align="center" class="<?php echo $thisclass; ?>" onclick="toggleRow('<?php echo $class; ?>', this,'<?php echo $intake_id; ?>','<?php echo $productsRow['cut_id']; ?>','<?php echo $nationality_id;?>');"><?php if($products2Count > 0){ ?><i class="fa fa-chevron-down"></i><?php } ?></td>
                            <td width="40" align="center" onclick="toggleVisibleRow('<?php echo $class; ?>')" style="display:none"><?php if($products2Count > 0){ ?><i class="fa fa-chevron-down"></i><?php } ?></td>
                            <td colspan="1">
                                <a href="intake.php?id=<?php echo $intake_id; ?>&ref=salesconfirmationsheet" style="color:#000;text-decoration:underline;">
                                    <b><?php echo $intake_id; ?></b>
                                </a>
                            </td>
                            <td colspan="1">
                            &nbsp;		 
                            </td>
                            <td colspan="1"></td>
                            <td colspan="1"><?php echo $quantityTotal; ?></td>
                            <td align="left" <?php if($temp_id == 1){ echo 'style="background:#c0392b;color:#fff;padding:5px;"'; }else { echo 'style="background:#2980b9;color:#fff;padding:5px;"'; } ?>><?php echo getTemp($temp_id); ?></td>
                            <td colspan="1"><?php echo $cut; ?></td>
                            <td colspan="1"><?php echo getNationality($productsRow['nationality_id']); ?></td>
                            <td colspan="1">
                                <form method="post"><?php # $productsRow['productcomments']; ?>
                                    <textarea name="comments" class="overviewcomment" productid="<?php echo $productsRow['productid']; ?>"><?php echo $productsRow['weightnote']; ?></textarea>
                                    <input type="text" name="pallet_id" class="pallet" value="<?php echo $pallet_id; ?>" style="display:none;">
                                </form>
                            </td>
                            <td align="left"><?php echo getBrand($productsRow['brand_id']); ?></td>
                            <td><?php if($ubbb != 2){ echo $ubtext . ' ' . $smallestDate . ' - ' . $largestDate; }else { echo $ubtext; } ?></td>
                            <td><?php 
                             
                             if($productsRow['akg'] != ''){
                                echo totalWeightOfAdvisedKGProduct($intake_id);
                             }else{
                                echo $totalWeightOfProduct = totalWeightOfProduct($product2_productids);
                             }
                                ?>kg</td>
                            <td><?php  if($productsRow['cost']){ echo '£' . number_format((float)$productsRow['cost'], 2, '.', ''); } ?></td>
                            <td><?php  if($productsRow['price']){ echo '£' . number_format((float)$productsRow['price'], 2, '.', ''); } ?></td>
                        </tr>
                    <?php }?>
            
        <?php
            }
        }
        ?>
        </table>
    </div>
</div>


<div class="clearfix"></div>
<?php 
	if($_GET['msg'] != ''){
	?>
	<script type="text/javascript">
		alert('<?php echo $_GET['msg'];?>');
	</script>
	<?php	
	}
?>
  
<script type="text/javascript">
    
    $('.gifContainer').hide();
    $('.resultsContainer').css('padding-bottom','30px');
    setTimeout(function(){
        $('.select2-container').css('display', 'none');
        $('.select2-container').first().css('display', 'inline-block');
    }, 10);

    $('#SearchSpecies').change(function(){
        var thisval = $(this).val();
        $('#SearchCutgroups option.allsoption').hide();
        $('#SearchCutgroups option.header').show();
        $('#SearchCutgroups option.s'+thisval).show();
        $('#SearchCutgroups').val($('#SearchCutgroups option.header').first().attr('sid'));
       
        // iOS fix - display:none doesn't work on select options
		$('#SearchCutgroups option.allsoption').unwrap('span');
        $('#SearchCutgroups option.allsoption').wrap('<span/>');
        $('#SearchCutgroups option.s'+thisval).unwrap();
       
        //$('#SearchCutgroups').val($('#SearchCutgroups option.s'+thisval).first().attr('sid'));
        var id = $(this).val();
    });

    $('#SearchCutgroups').change(function(){
        var id = $(this).val();

        // $('#searchForm').submit();
    });


    function doSearch(){
 
  		var species = $('#SearchSpecies').val();
		var cutgroup_id = $('#SearchCutgroups').val();
  		var intakeID = $('#IntakeID').val();
 		var palletID = $('#PalletID').val();
		
		if(cutgroup_id != '' && species != '' || intakeID != '' || palletID != ''){
            $('#searchForm').submit();
        }else{
			alert('Please fill out the form before searching');
		}

    }


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
	 
		
	
</script>

<style type="text/css">
input[type='number'] {
    -moz-appearance:textfield;
}
/* Webkit browsers like Safari and Chrome */
input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.select2-container--default .select2-selection--single{
    height:40px;
    border-radius:0px;
}

.select2-container--default .select2-selection--single .select2-selection__rendered{
    line-height:41px;
}

.select2-results__option:first-child{ display:none; }
</style>