<?php
	require('functions.php');
    ini_set('max_execution_time','256');
	ini_set('memory_limit', '1536M');
	
	require_once 'vendor/autoload.php';
		
	$mpdf = new \Mpdf\Mpdf([
        'tempDir' => __DIR__ . '/docs',
        'mode' => 'utf-8',
        'format' => 'A4-L',
		'setAutoTopMargin' => 'stretch',
        'autoMarginPadding' => 0,
        'bleedMargin' => 0,
        'crossMarkMargin' => 0,
        'cropMarkMargin' => 0,
        'nonPrintMargin' => 0,
        'margBuffer' => 0,
        'collapseBlockMargins' => true,
    ]);
	
	$header .= '<link href="https://fonts.googleapis.com/css?family=Roboto:300,400,700&display=swap" rel="stylesheet">';
	$header .= '<link href="https://fonts.googleapis.com/css?family=Handlee&display=swap" rel="stylesheet">';
	
    $border = 0;

	$css ="
		body{
			font-family: 'Roboto', sans-serif;
			margin:0px;
		}

        .headerBg{
            background:#B4C7E7;
        }

        .underline{
            text-decoration:underline;
        }

        .intakeid{
            width:80px;
            font-size:8px;
            color:grey;
            text-align:left;
        }

        .palletid{
            width:80px;
            
            font-size:8px;
            color:grey;
            text-align:left;
        }

        .quantity{
            width:40px;
            font-size:11px;
            font-weight:bold;
            text-align:center;
            color:black;
        }

        .temp{
            width:70px;
            font-size:8px;
            text-align:center;
        }

        .species{
            font-size:8px;
            color:grey;
            text-align:center;
            width:80px;
        }

        .product{
            width:200px;
            font-size:11px;
            font-weight:bold;
            color:black;
            text-align:left;
        }

        .nationality{
            width:100px;
            font-size:8px;
            color:grey;
            text-align:left;
        }

        .brand{
            width:70px;
            font-size:8px;
            color:grey;
            text-align:left;
        }

        .dateRange{
            width:110px;
            font-size:8px;
            color:grey;
            text-align:left;
        }

        .unit{
            width:80px;
            font-weight:bold;
            font-size:11px;
            color:black;
            text-align:center;
        }

        .cost{
            width:110px;
            text-align:right;
            font-size:8px;
            color:grey;
        }

        .price{
            width:110px;
            text-align:right;
            font-size:8px;
            color:grey;
        }

        .ppc{
            width:50px;
        }

        .tempFrozen{
            background:#2980B9;
            color:#fff;
        }

        .tempFresh{
            background:#C0392B;
            color:#fff;
        }
        
        .tempFresh/Frozen{
            background:grey;
            color:#fff;     
        }

        .tempMixed{
            background:grey;
            color:#fff;
        }
	";
	
	$mpdf->WriteHTML($css,\Mpdf\HTMLParserMode::HEADER_CSS);
 
    
    $header = '<table width="100%" class="headerBg"><tr><td><h2 class="underline">Stock Report</h2></td></tr></table>';

    $header .= '<table width="100%" class="headerBg" border="'. $border .'">
                <tr>
                    <td class="heading" width="80">Intake ID</td>
                    <td class="heading" width="80">Plt ID</td>
                    <td class="heading" width="40">Unit</td>
                    <td class="heading" width="70">Chill/Frz</td>
                    <td class="heading" width="80">Species</td>
                    <td class="heading" width="200">Product Name</td>
                    <td class="heading" width="100">Nationalities</td>
                    <td class="heading" width="70">Brands</td>
                    <td class="heading" width="110">Date Range</td>
                    <td class="heading" width="50"></td>
                    <td class="heading" width="80">Volume Kg</td>
                    <td class="heading" width="110" align="right">Cost</td>
                    <td class="heading" width="110" align="right">RRP</td>
                </tr>
                </table>';


    $productsX = "SELECT *, product.brand_id, product.comments as productcomments, product.id as productid, cuts.name as cutname, brands.name as brandname, nationality.name as local FROM `product` INNER JOIN `pallet` ON product.pallet_id=pallet.id
INNER JOIN `weights` ON product.id = weights.product_id
    JOIN `cuts` ON product.cut_id = cuts.id
    JOIN `nationality` ON product.nationality_id = nationality.id
    JOIN `brands` ON product.brand_id = brands.id
    JOIN `species` ON cuts.species_id = species.id
    WHERE weights.status_id != 1
    GROUP BY pallet.intake_id, product.cut_id,product.nationality_id ORDER BY species.id, cuts.name ASC";
    
    $productsY = prepareExecuteQuery($productsX);
    $productsCount = $productsY->num_rows;
        
    $totalW = 0;
    
    $products = $productsY->fetch_all(MYSQLI_ASSOC);
    
    $total_quantity = 0;
    $total_cost = 0;
    $total_price = 0;
    $total_weight = 0;
    
    $html .= '<table width="100%" border="'. $border .'" class="row">';

    foreach($products as $productsRow){        
        $pallet_id = $productsRow['pallet_id'];
        $cut_id = $productsRow['cut_id'];
        $ubbb = $productsRow['ubbb'];
        
        $html .= '<tr>';

        $intake_id = $productsRow['intake_id'];
        $nationality_id = $productsRow['nationality_id'];
        $local = $productsRow['local'];
        $brandname = $productsRow['brandname'];
        $species = getSpeciesFromCutID($cut_id);
        $cut = $productsRow['cutname'];
        if($ubbb == 0){
            $ubtext = 'UB';
        }else if($ubbb == 1){
            $ubtext = 'BB';
        }else{
            $ubtext = 'N/A';
        }

        
        $productsX2 = "SELECT product.cut_id, product.range_from, product.range_to, product.cooling_id, product.brand_id, product.pallet_id, product.id productid FROM `product` INNER JOIN `pallet` ON product.pallet_id=pallet.id WHERE pallet.intake_id='$intake_id' && product.cut_id = '$cut_id' && product.nationality_id='$nationality_id' ORDER BY product.cut_id DESC";
        $productsY2 = prepareExecuteQuery($productsX2);
        $products2Count = $productsY2->fetch_assoc();
        
        
        $products2 =  $productsY2->fetch_all(MYSQLI_ASSOC);

        $product2_palletids = array();
        $product2_cutids = array();
        $product2_productids = array();
        $product2_brands = array();
        $product2_nationalities = array();
        $product2_temperatures = array();
        $product2_dateranges = array();

            
        foreach ($products2 as $product2) {
            array_push($product2_palletids, $product2['pallet_id']);
            array_push($product2_cutids, $product2['cut_id']);
            array_push($product2_productids, $product2['productid']);


            array_push($product2_brands, $product2['brand_id']);
            array_push($product2_nationalities, $product2['nationality_id']);
            array_push($product2_temperatures, $product2['cooling_id']);
            array_push($product2_dateranges, $product2['range_from'] .'-'. $product2['range_to']);

        }
        
        $uniqueBrands = count(array_unique($product2_brands));
        $uniqueNationalities = count(array_unique($product2_nationalities));
        $uniqueTemperatures = count(array_unique($product2_temperatures));
        $uniqueDateranges = count(array_unique($product2_dateranges));

        $quantityTotal = countNumProductsForCutOnPalletArrays($product2_palletids, [$product2_cutids[0]], $nationality_id);
        
        if($quantityTotal < 1){continue;}
        
        $totalW += weightSoldFromProductID($productsRow['productid']);           
        $totalProducts = weightsAvailableOnProduct($productsRow['productid']);
        
        
        $html .= '<td class="cell intakeid">' . $intake_id . '</td>';
        $html .= '<td class="cell palletid">' . $pallet_id . '</td>';
        $html .= '<td class="cell quantity">' . $quantityTotal . '</td>';
        
        $total_quantity += $quantityTotal;

        if($uniqueTemperatures > 1){
            $html .= '<td class="cell temp tempMixed">Mixed</td>';
        }else{
            $html .= '<td class="cell temp temp'. getTemp($product2_temperatures[0]) .'">' . getTemp($product2_temperatures[0]) . '</td>';
            
        }
        $html .= '<td class="cell species">' . $species . '</td>';
        $html .= '<td class="cell product">' . $cut . '</td>';

        if($uniqueNationalities > 1){
            $html .= '<td class="cell nationality">Various</td>';
        }else{
            $html .= '<td class="cell nationality">' . $local . '</td>';
        }

        if($uniqueBrands > 1){
            $html .= '<td class="cell brand">Various</td>';
        }else{
            $html .= '<td class="cell brand">' . $brandname . '</td>';
        }

    
        if($uniqueDateranges > 1){
            $html .= '<td class="cell daterange"> -- </td>';
        }else{
            if($ubbb != 2){
                $html .= '<td class="cell daterange">' . $ubtext . ' ' . $product2_dateranges[0] . '</td>';
            }else{
                $html .= '<td class="cell daterange">' . $ubtext . '</td>';
            }
        }


        if($productsRow['unit'] == 'PPC'){
            $html .= '<td class="cell ppc">PPC</td>';
            $html .= '<td class="cell unit"></td>';
        }else{
            $html .= '<td class="cell ppc"></td>';

            if($productsRow['akg'] != ''){
                $temp_weight = totalWeightOfAdvisedKGProduct($intake_id, $productsRow['nationality_id']);
            }else{
                $temp_weight = totalWeightOfProduct($product2_productids);
            }

            $html .= '<td class="cell unit">' . number_format((double)$temp_weight, 3, '.', '') . '</td>';
            $total_weight += $temp_weight;
        }

        $total_cost += number_format((double)$productsRow['cost'], 2, '.', '');
        $total_price += number_format((double)$productsRow['price'], 2, '.', '');

        $html .= '<td class="cell cost">£' . number_format((double)$productsRow['cost'], 2, '.', '') . '</td>';
        $html .= '<td class="cell price">£' .  number_format((double)$productsRow['price'], 2, '.', '') . '</td>';
        $html .='</tr>';
    }
    $html .= '<tr>';
    $html .= '<td class="cell intakeid"><b style="color:black;font-size:11px;">Totals</b></td>';
    $html .= '<td class="cell palletid"></td>';
    $html .= '<td class="cell quantity">' . $total_quantity . ' </td>';
    $html .= '<td class="cell temp"></td>';
    $html .= '<td class="cell species"></td>';
    $html .= '<td class="cell product"></td>';
    $html .= '<td class="cell nationality"></td>';
    $html .= '<td class="cell brand"></td>';
    $html .= '<td class="cell daterange"></td>';
    $html .= '<td class="cell ppc"></td>';
    $html .= '<td class="cell unit">'. number_format((double)$total_weight, 3, '.', ',') .'</td>';
    $html .= '<td class="cell cost"><b style="font-size:8px;">£' . $total_cost . '</b></td>';
    $html .= '<td class="cell price"><b style="font-size:8px;">£' . $total_price . '</b></td>';
    $html .='</tr></table>';



	$mpdf->SetHTMLHeader($header);


    $mpdf->AddPage();
    $mpdf->WriteHTML($html);
  
	
	
  
 
 
 	$filename2 = 'Export_Stock.pdf';
	$filename = 'PDF/' . $filename2;
	
 	
	$mpdf->Output(__DIR__."/".$filename,'F');
?>
<script> window.location.href = '/<?php echo $filename; ?>'; </script>