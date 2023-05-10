<?php
    ini_set('memory_limit','16M');
    include('includes/frontHeader.php');
?>
<script>
    function toggleRow(classs, ele,productid){
        $.get( "scripts/_searchStockNew.php?product_id="+productid+"&class=" + classs, function( data ) {
            //$('.basketTable').append(data);
            $(ele).parent().after(data);
            $(ele).next().fadeIn();
            $(ele).fadeOut();

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
	<a href="logout" id="logout">LOGOUT</a>
</div>

<div class="leftPanel" style="position:relative;">
    <form method="POST" id="searchForm">
        <select id="SearchSpecies" name="species" style="width:322px;height:40px;">
            <option value="" disabled selected>Select species..</option>
            <?php
                $x = "SELECT * FROM `species`";
                $y = prepareExecuteQuery($x);
                
                while($row = mysqli_fetch_array($y)){
                ?><option value="<?php echo $row['id']; ?>" <?php if(request()->input('species') == $row['id']){ echo 'selected'; } ?>><?php echo $row['name']; ?></option><?php
                }
            ?>
        </select>
        
        <select id="SearchCutgroups" name="cutgroup_id" style="width:322px;height:40px;">
            <option sid="<?php echo $rand; ?>" class="header" value="<?php echo $rand; ?>" selected>Select cut..</option>
            <?php
                $x = "SELECT * FROM `cutgroups`";
                $y = prepareExecuteQuery($x);
                
                $i=0;
                while($row = mysqli_fetch_array($y)){
                    
                    
                    $thisid = $row['species_id'];
                    $y2 = mysqli_query($conn,"SELECT * FROM species WHERE id='$thisid'");
                    $species = mysqli_fetch_array($y2);
                    $rand = 'z' . rand(6000,12212);
                        ?><option style="display:none;" sid="<?php echo $row['id']; ?>" class="allsoption s<?php echo $species['id']; ?>" value="<?php echo $row['id']; ?>"<?php if(request()->input('cutgroup_id') == $row['id']){ echo 'selected'; } ?>><?php echo $row['name']; ?></option><?php
                    }
            ?>
        </select>
    </form>	
	<div id="loadResults" class="resultsContainer">
        <?php if(request()->input('cutgroup_id')){ ?>        
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
                ?><div class="gifContainer"><center><img src="https://zippy.gfycat.com/SkinnySeveralAsianlion.gif" style="padding-top:40px;padding-bottom:40px;width:40px;text-align:center;"></center></div><?php
                $cutgroup_id = request()->input('cutgroup_id');
                $species_id = request()->input('species');
                
                $ARRAY_CUTS = cutsFromCutGroup($species_id, $cutgroup_id);

                $ids = implode(',', $ARRAY_CUTS);
                $productsX = "SELECT *, product.comments as productcomments, 
                product.id as productid FROM `product` 
                INNER JOIN `pallet`
                ON product.pallet_id=pallet.id 
                WHERE product.cut_id IN ($ids) 
                GROUP BY pallet.intake_id,product.cut_id,product.nationality_id 
                ORDER BY product.cut_id DESC";
                $productsY = prepareExecuteQuery($productsX);
                $productsCount = mysqli_num_rows($productsY);

                $totalW = 0;
    
                $products =  mysqli_fetch_all($productsY, MYSQLI_ASSOC);

                $productIDs = array();
                foreach ($products as $product) 
                { 
                    $productIDs[]= $product['productid']; 
                }
        
                // print_r($productIDs);
                $q = "SELECT * from `weights` WHERE status_id != '1' AND product_id IN(" . implode(",", $productIDs) . ")";
                $q = prepareExecuteQuery($q);
                $weights = mysqli_fetch_all($q, MYSQLI_ASSOC);
                    
               
                 
    
                foreach($products as $productsRow){
                    

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
                    if($ubbb == 0){
                        $ubtext = 'UB';
                    }else if($ubbb == 1){
                        $ubtext = 'BB';
                    }else{
                        $ubtext = 'N/A';
                    }

                    ####
                    $productsX2 = "SELECT * , product.id productid
                    FROM `product` 
                    INNER JOIN `pallet` 
                    ON product.pallet_id=pallet.id 
                    WHERE pallet.intake_id='$intake_id' 
                    && product.cut_id = '$cut_id' && 
                    product.nationality_id='$nationality_id' 
                    ORDER BY product.cut_id DESC";
                    
                    $productsY2 = prepareExecuteQuery($productsX2) or die(mysqli_error($conn));
                    $products2Count = mysqli_num_rows($productsY2);
                    ####
                    $totalW = 0;
                    
                    $relatedWeights = [];
                    
                    foreach ($weights as $weight){
                        
                        if($weight["product_id"] == $productsRow["productid"])
                        {
                            $relatedWeights[] = $weight; 
                        }

                    }
                    
        
                    foreach ($relatedWeights as $weight){

                        $w = 0;
                        if($weight['weight_tear'] == $weight['weight_gross']){
                            $w = (double)$weight['weight_gross'];
                        }else{
                            $w = (double)$weight['weight_gross'] - (double)$weight['weight_tear'];
                        }
                        
                        $totalW = $totalW + $w;
                        
                    
                    }
  
                    $totalProducts = count($relatedWeights);
                    $numOfWeights = countNumProductsForCutOnPalletThatIsntPicked($pallet_id, $cut_id);

                        if($totalProducts >= 1){ ?>
                        <tr class="searchAccordTitle">
                            <td width="40" align="center" onclick="toggleRow('<?php echo $class; ?>', this,'<?php echo $productsRow['productid']; ?>');"><?php if($products2Count > 0){ ?><i class="fa fa-chevron-down"></i><?php } ?></td>
                            <td width="40" align="center" onclick="toggleVisibleRow('<?php echo $class; ?>')" style="display:none"><?php if($products2Count > 0){ ?><i class="fa fa-chevron-down"></i><?php } ?></td>
                            <td colspan="1">
                                <a href="intake.php?id=<?php echo $intake_id; ?>&ref=salesconfirmationsheet" style="color:#000;text-decoration:underline;">
                                    <b><?php echo $intake_id; ?></b>
                                </a>
                            </td>
                            <td colspan="1">
                            &nbsp;		 
                            </td>
                            <td colspan="1"><?php echo $pallet_id; ?></td>
                            <td colspan="1"><?php echo $totalProducts; ?></td>
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
                             
                                $weightt = $relatedWeights[0];

                                $original_gross = number_format($weightt['original_gross'], 2, '.', '');
                                $num_cartons = number_format($weightt['number_of_cartons'], 2, '.', '');
                                $pallet_tare = number_format($weightt['pallet_tare'], 2, '.', '');
                                $tare_per_carton = number_format($weightt['tare_per_carton'], 2, '.', '');
                                
                                $carton_tare = $num_cartons * $tare_per_carton;
                                
                                $total_tare = $carton_tare + $pallet_tare;
                                
                                $tare = $original_gross - $total_tare;
                                
                                if($row['grosspallet'] == 1){
                                    
                                    echo '[GT] ' . number_format($totalW, 2, '.', '');
                                    $totalW = 0;
                                }else{
                                    echo $totalW;
                                    $totalW = 0;
                                }

                                ?>kg</td>
                            <td><?php  if($productsRow['cost']){ echo 'Â£' . number_format((double)$productsRow['cost'], 2, '.', ''); } ?></td>
                            <td><?php  if($productsRow['price']){ echo 'Â£' . number_format((double)$productsRow['price'], 2, '.', ''); } ?></td>
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
	if(request()->input('msg') != ''){
	?>
	<script type="text/javascript">
		alert('<?php echo request()->input('msg');?>');
	</script>
	<?php	
	}
?>
  
<script type="text/javascript">
    $.ajaxSetup({
		headers: { 'X-CSRF-TOKEN': "<?php echo csrf_token();?>" }
	});
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
        //$('#SearchCutgroups').val($('#SearchCutgroups option.s'+thisval).first().attr('sid'));
        var id = $(this).val();
    });

    $('#SearchCutgroups').change(function(){
        var id = $(this).val();
        var formName = '#searchForm';
			var xhttp = new XMLHttpRequest();
			xhttp.open("POST", $(formName).attr('action'), true);
			xhttp.setRequestHeader('X-CSRF-TOKEN', "<?php echo csrf_token();?>");
			xhttp.send($(formName).serialize());
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