<table width="100%" class="slim searchRContent"   style="display:table;">
    <thead>
        <tr class="searchRContent__head">
	        <th class="searchRContent__location">Container ID</th>
            <th class="searchRContent__dropdown"></th>
	        <th class="searchRContent__unit">Units</th>
	        <th class="searchRContent__product">Product</th>
	        <th>Nationality</th>
	        <th>Brand</th>
	        <th class="searchRContent__date-range">ETA</th>
	        <th>Expected KG</th>
	        <th>Cost</th>
	        <th class="searchRContent__plus"></th>
        </tr>
    </thead>
    <tbody>
<?php

use App\Models\CutGroupNationalityDate;
use App\Models\InboundContainer;
use App\Models\Location;
use App\Models\Reservation;
use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

    ini_set('memory_limit','32M');
	require(__DIR__.'/../functions.php');

    $cutgroup_id = request()->input('cutgroup_id');
    $species_id = request()->input('species');
    $initial_pallet_id = $pallet_id = -2;
    $brand_id =  request()->input('brandID');
    $nationality_id =  request()->input('nationalityID');
    $customer_id =  request()->input('customerID');
    $timeSensitivityStatus = (int)request()->input('time',0);
    if ($timeSensitivityStatus == null) $timeSensitivityStatus = 0;

    $totalW = 0;


        if($ubbb == 0){
            $ubtext = 'UB';
        }else if($ubbb == 1){
            $ubtext = 'BB';
        }else{
            $ubtext = 'N/A';
        }
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

        }else if(($species_id != 'null' && !empty($species_id)) && empty($cutgroup_id)){
            array_push($whereArray, "cuts.species_id = ".$species_id);
        }
        else{
            array_push($whereArray, "cuts.species_id NOT IN (11,12,14)");
        }

        array_push($whereArray, "product.pallet_id = -2");

        if ($brand != '' && $brand != null && $brand != 'null'){
            array_push($whereArray, "product.brand_id = ". $brand ."");
        }
        if ($nationality_id != '' && $nationality_id != null && $nationality_id != 'null'){
            array_push($whereArray, "product.nationality_id = ". $nationality_id ."");
        }
        if ($site_id != '' && $site_id != null && $site_id != 'null'){
            $locs = implode(",",array_column(prepareExecuteQuery("SELECT `id` FROM `location` WHERE `site_id` = ? AND id IS NOT NULL",'i',[$site_id])->fetch_all(MYSQLI_ASSOC),"id"));
        }
        else
        {
            $locs = implode(",",array_column(prepareExecuteQuery("SELECT `id` FROM `location` WHERE id IS NOT NULL")->fetch_all(MYSQLI_ASSOC),"id"));
        }

        $whereString = implode(' && ',$whereArray);
        $containers = InboundContainer::where([["admin_approved",true],["arrived",false]])->orderBy('eta')->get();
        foreach ($containers as $container) {
            foreach ($container->getProducts() as $containerProduct){
                //dd(Reservation::where("product_id",$containerProduct->product_id)->get());
                $alreadyReserved = Reservation::where("product_id",$containerProduct->product_id)->get()->sum("target_count");
                $productsX2 = "SELECT SQL_NO_CACHE *, `product`.`comments` as productcomments, `product`.`id` as productid, `cuts`.`name` as cutname, `nationality`.`name` as `local` FROM `product`
                JOIN `cuts` ON `product`.`cut_id` = `cuts`.`id`
                LEFT JOIN `nationality` ON `product`.`nationality_id` = `nationality`.`id`
                WHERE $whereString AND product.id = ".$containerProduct->product_id;
                ####
                $productsY2 = prepareExecuteQuery($productsX2);
                $products2Count = mysqli_num_rows($productsY2);
                ####
                if($products2Count > 0){
                    while($productsRow2 = mysqli_fetch_assoc($productsY2)){

                            if (stripos(Location::find($productsRow2['storage_location'])->name, "coldstore")!=false || $locked){
                                $class = request()->input('class') . " searchAccordTitle locked";
                            }
                            else {
                                $class = request()->input('class');
                            }
                            $temp_id = $productsRow2['cooling_id'];
                            $smallestDate = ($productsRow2['range_extension']!= null && $productsRow2['range_extension']!= '')?$productsRow2['range_extension']:$productsRow2['range_from'];
                            $largestDate = ($productsRow2['range_extension']!= null && $productsRow2['range_extension']!= '')?$productsRow2['range_extension']:$productsRow2['range_to'];
                            $largestDate2 = ($productsRow2['range_extension']!= null && $productsRow2['range_extension']!= '')?"EXTENSION":$productsRow2['range_to'];
                            $pallet_id = $productsRow2['pallet_id'];
                            $product_id = $productsRow2['productid'];
                            $pallet_comments = $productsRow2['productcomments'];
                            if ($pallet_comments==""){
                                $pallet_comments_query_sql = "SELECT `body` FROM `comment_logging` WHERE `type` = 'pallet' AND `entity_id` = $pallet_id ORDER BY `id` DESC LIMIT 1";
                                $pallet_comments_query = mysqli_query($mysqli,$pallet_comments_query_sql);

                                if (mysqli_num_rows($pallet_comments_query) > 0)
                                {
                                    $pallet_comments = mysqli_fetch_assoc($pallet_comments_query);
                                    $pallet_comments = $pallet_comments['body'];
                                }
                            }
                            $this_row_weight = $productsRow2['akg'];

                            $state = 0;
                            if($ubbb != 2 && $temp_id == 1){
                                $toDate = DateTime::createFromFormat('d/m/Y',$smallestDate)->getTimestamp();
                                $toDate2 = DateTime::createFromFormat('d/m/Y',$largestDate)->getTimestamp();
                                if ($toDate2 < $toDate) $toDate = $toDate2;
                                $cutResult= CutGroupNationalityDate::lookupFromProductID($productsRow2['productid']);
                                $bgCol = "";
                                $now = time();
                                if (isset($cutResult['warning']) && $cutResult['warning'] != "")
                                {
                                    $pastWarning1 = $toDate - ($cutResult['warning'] * 86400);
                                    if ($pastWarning1 <= $now)
                                    {
                                        $bgCol = 'style="background-color:#FFBF00"';
                                        $state = 1;
                                    }
                                }
                                if (isset($cutResult['danger']) && $cutResult['danger'] != "")
                                {
                                    $pastWarning2 = $toDate - ($cutResult['danger'] * 86400);
                                    if ($pastWarning2 <= $now)
                                    {
                                        $bgCol = 'style="background-color:red"';
                                        $state = 2;
                                    }
                                }
                                $pastWarning3 = $toDate;
                                if ($pastWarning3 <= $now)
                                {
                                    $bgCol = 'style="background-color:darkred"';
                                    $state = 2;
                                }
                            }
                            if ($timeSensitivityStatus > 0 &&  $state != $timeSensitivityStatus) continue;
                            ?>
                            <tr <?php if(isset($smallestDate)) echo $bgCol; ?>class="subrow <?php echo $class; ?>">
                            <td colspan="1">
                            <?php echo $container->internal_number;?>
                            </td>
                            <td></td>
                            <td colspan="1">
                                <select class="quantitybox" id="quantity-<?php echo $productsRow2['productid']; ?>-<?php echo $productsRow2['pallet_id']; ?>">
                                    <?php for($i=1;$i<$productsRow2['quantity'] - $alreadyReserved+1;$i++){?>
                                        <option value="<?php echo $i; ?>" selected><?php echo $i; ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                            <td class="bold" colspan="1"><?php echo getCut($productsRow2['cut_id']); ?></td>
                            <td colspan="1"><?php echo getNationality($productsRow2['nationality_id']);?></td>
                            <td><?php echo getBrand($productsRow2['brand_id']); ?></td>
                            <td><?php echo $container->eta->format("d/m/Y"); ?></td>

                            <td class="bold"><?php

                            if($productsRow2['grosspallet'] == 1){
                                echo '[GT] ';
                            }
                            if($productsRow2['unit'] == 'PPC'){
                                ?><b>PPC</b><?php
                            }else{
                                echo $this_row_weight*$productsRow2['quantity']  . 'kg';
                            }
                            ?></td>
                            <td></td>
                            <?php if (User::find(Auth::id())->hasPermission("viewcosts")) { ?><td></td><?php } ?>
                            <td>
                            <?php if(stripos(Location::find($productsRow2['storage_location'])->name, "coldstore")==false && $locked != true){ ?>
                                <a href="javascript:;" class="plusButton" onclick="addToSheet('<?php echo $productsRow2['productid']; ?>','<?php echo $productsRow2['pallet_id']; ?>','<?php echo $productsRow2['cut_id']; ?>','<?php echo $class; ?>','<?php echo $largestDate; ?>');"><i class="fa fa-plus" style="font-size:24px;color:#000;"></i></a>
                            <?php } ?>
                            </td>
                        </tr>
                        <?php
                    }
                }
            }
        }
 ?>
 </tbody>
 <script>
    $.ajaxSetup({
		headers: { 'X-CSRF-TOKEN': "<?php echo csrf_token();?>" }
	});
	function saveDeepComment(pallet_id,product_id){
		var currentComment = $('[palletid="'+pallet_id+'-'+product_id+'"]').val();
		var pallet = pallet_id;
		$.ajax({
			method: "POST",
			url: "ajax/saveDeepComment.php",
			data: {body: currentComment, product_id: product_id},
		});
        alert("Done!");
	}
    $(document).ready(function()
    {
        $('select[name="location"]') .each(function()
        {
            $(this).change(function() {

                var location = $(this).parent().find('select[name="location"]').val();
                var pallet = $(this).parent().find('[name="pallet_id"]').val();
                console.log(location);
                $.get("ajax/saveLocation.php?location="+location+'&pallet='+pallet, function(data, status){
                    console.log(data);
                });
            });
        });
    });

 </script>
