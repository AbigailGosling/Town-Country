<?php
    require('functions.php');


    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=data.csv');

    $output = fopen('php://output', 'w');

    $headings = array();
   
    array_push($headings, 'Intake ID');
    array_push($headings, 'Pallet ID');
    array_push($headings, 'Unit');
    array_push($headings, 'Chill/Frz');
    array_push($headings, 'Species');
    array_push($headings, 'Product Name');
    array_push($headings, 'Nationalities');
    array_push($headings, 'Brands');
    array_push($headings, 'Date Range');
    array_push($headings, 'Volume');
    array_push($headings, 'Cost');
    array_push($headings, 'RRP');
    fputcsv($output, $headings);



    $productsX = "SELECT *, product.brand_id, species.id as species_id, product.comments as productcomments, product.id as productid, cuts.name as cutname, brands.name as brandname, nationality.name as local FROM `product` INNER JOIN `pallet` ON product.pallet_id=pallet.id
    INNER JOIN `weights` ON product.id = weights.product_id
    JOIN `cuts` ON product.cut_id = cuts.id
    JOIN `nationality` ON product.nationality_id = nationality.id
    JOIN `brands` ON product.brand_id = brands.id
    JOIN `species` ON cuts.species_id = species.id
    WHERE weights.status_id != 1
    GROUP BY pallet.intake_id, product.cut_id,product.nationality_id ORDER BY species.name, cuts.name ASC";
    
    $productsY = mysqli_query($conn, $productsX);
    $productsCount = mysqli_num_rows($productsY);
     
    $totalW = 0;
    
    $products = mysqli_fetch_all($productsY, MYSQLI_ASSOC);
    
    foreach($products as $productsRow){
        $single_row = array();

        $pallet_id = $productsRow['pallet_id'];
        $cut_id = $productsRow['cut_id'];
        $ubbb = $productsRow['ubbb'];


        $intake_id = $productsRow['intake_id'];
        $nationality_id = $productsRow['nationality_id'];
        $local = $productsRow['local'];
        $brandname = $productsRow['brandname'];
        $cut = $productsRow['cutname'];
        $species_name = getSpecies($productsRow['species_id']);
        if($ubbb == 0){
            $ubtext = 'UB';
        }else if($ubbb == 1){
            $ubtext = 'BB';
        }else{
            $ubtext = 'N/A';
        }

        
        $productsX2 = "SELECT product.cut_id, product.range_from, product.range_to, product.cooling_id, product.brand_id, product.pallet_id, product.id productid FROM `product` INNER JOIN `pallet` ON product.pallet_id=pallet.id WHERE pallet.intake_id='$intake_id' && product.cut_id = '$cut_id' && product.nationality_id='$nationality_id' ORDER BY product.cut_id DESC";
        $productsY2 = mysqli_query($conn, $productsX2) or die(mysqli_error($conn));
        $products2Count = mysqli_num_rows($productsY2);
        
        
        $products2 =  mysqli_fetch_all($productsY2, MYSQLI_ASSOC);

        $product2_palletids = array();
        $product2_cutids = array();
        $product2_productids = array();
        $product2_brands = array();
        $product2_nationalities = array();
        $product2_temperatures = array();
        $product2_dateranges = array();

         
        array_map(
            function($product2) {
                global $product2_palletids;
                global $product2_cutids;
                global $product2_productids;
                global $product2_brands;
                global $product2_nationalities;
                global $product2_temperatures;
                global $product2_dateranges;

                array_push($product2_palletids, $product2['pallet_id']);
                array_push($product2_cutids, $product2['cut_id']);
                array_push($product2_productids, $product2['productid']);

                // $numOfWeights = numWeightsAvailableFromProductID($product2['productid']);

                // if($numOfWeights > 0){
                    array_push($product2_brands, $product2['brand_id']);
                    array_push($product2_nationalities, $product2['nationality_id']);
                    array_push($product2_temperatures, $product2['cooling_id']);
                    array_push($product2_dateranges, $product2['range_from'] .'-'. $product2['range_to']);
                // }

            },
        $products2);
        
        $uniqueBrands = count(array_unique($product2_brands));
        $uniqueNationalities = count(array_unique($product2_nationalities));
        $uniqueTemperatures = count(array_unique($product2_temperatures));
        $uniqueDateranges = count(array_unique($product2_dateranges));

        $quantityTotal = countNumProductsForCutOnPalletArrays($product2_palletids, [$product2_cutids[0]], $nationality_id);
        
        if($quantityTotal < 1){continue;}
        ###
       
        $totalW += weightSoldFromProductID($productsRow['productid']);           
        $totalProducts = weightsAvailableOnProduct($productsRow['productid']);
       
        
        array_push($single_row, $intake_id);
        array_push($single_row, $pallet_id);
        array_push($single_row, $quantityTotal);

        if($uniqueTemperatures > 1){
            array_push($single_row, 'Mixed');
        }else{
            array_push($single_row, getTemp($product2_temperatures[0]));
        }

        array_push($single_row, $species_name);
        array_push($single_row, $cut);

        if($uniqueNationalities > 1){
            array_push($single_row, 'Various');
        }else{
            array_push($single_row, $local);
        }

        if($uniqueBrands > 1){
            array_push($single_row, 'Various');
        }else{
            array_push($single_row, $brandname);
        }

 
        if($uniqueDateranges > 1){
            array_push($single_row, '--');
        }else{
            if($ubbb != 2){
                array_push($single_row, $ubtext . ' ' . $product2_dateranges[0]);
            }else{
                array_push($single_row, $ubtext);
            }
        }


        if($productsRow['unit'] == 'PPC'){
            array_push($single_row, 'PPC');
        }else{
            if($productsRow['akg'] != ''){
                array_push($single_row, totalWeightOfAdvisedKGProduct($intake_id, $productsRow['nationality_id']) . 'kg');
            }else{
                array_push($single_row, totalWeightOfProduct($product2_productids) . 'kg');
            }
        }


        array_push($single_row, '' . number_format((float)$productsRow['cost'], 2, '.', ''));
        array_push($single_row, '' . number_format((float)$productsRow['price'], 2, '.', ''));

        fputcsv($output, $single_row);
    }

?>