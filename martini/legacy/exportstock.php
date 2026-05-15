<?php

use App\Models\Brand;
use App\Models\Intake;
use App\Models\Location;
use App\Models\Nationality;
use App\Models\Temperature;

    require('functions.php');

    $brands = Brand::all()->keyBy('id');
    $locations = Location::all()->keyBy('id');
    $nationalities = Nationality::all()->keyBy('id');
    $temperatures = Temperature::all()->keyBy('id');
    $intakes = [];

    $headings = array();

    $headings[] = 'Intake ID';
    $headings[] = 'Pallet ID';
    $headings[] = 'Storage Location';
    $headings[] = 'Unit';
    $headings[] = 'Chill/Frz';
    $headings[] = 'Species';
    $headings[] = 'Cut Group';
    $headings[] = 'Product Name';
    $headings[] = 'Nationalities';
    $headings[] = 'Brands';
    $headings[] = 'Received Date';
    $headings[] = 'Date';
    $headings[] = 'Range';
    $headings[] = '';
    $headings[] = 'Volume';
    $headings[] = 'Cost Per Unit';
    $final_array = array();
    $final_array[] = $headings;

    $productsX = "SELECT *, product.range_from, product.range_to, product.range_extension, product.brand_id, species.id as species_id, product.comments as productcomments, product.id as productid, cuts.name as cutname, cutgroups.name as cutgroup, species.name as species_name FROM `product` INNER JOIN `pallet` ON product.pallet_id=pallet.id
    INNER JOIN `weights` ON product.id = weights.product_id
    JOIN `cuts` ON product.cut_id = cuts.id
    JOIN `cutgroups` ON cuts.cutgroup_id = cutgroups.id
    JOIN `species` ON cuts.species_id = species.id
    WHERE weights.status_id != 1 GROUP BY product.id
    ORDER BY species.name, cuts.name ASC";

    $productsY = prepareExecuteQuery($productsX);

    $totalW = 0;

    $TOTAL_QUANTITY = 0;
    $TOTAL_COST = 0;
    $TOTAL_PRICE = 0;

    $types = Array('UB','BB','N/A','PB','EX');
    //$products = mysqli_fetch_all($productsY, MYSQLI_ASSOC);

    while($productsRow = $productsY->fetch_assoc()){
        $single_row = array();

        $pallet_id = $productsRow['pallet_id'];
        $cut_id = $productsRow['cut_id'];
        $ubbb = $types[$productsRow['ubbb']]; ;


        $intake_id = $productsRow['intake_id'];
        $nationality_id = $productsRow['nationality_id'];
        $local = $nationalities[$nationality_id]->name;
        $brandname = $brands[$productsRow['brand_id']]->name;
        $cut = $productsRow['cutname'];
        $cutgroup = $productsRow['cutgroup'];
        $species_name = $productsRow['species_name'];

        $ubtext = $ubbb;

        $quantityTotal = numWeightsAvailableFromProductID($productsRow['productid']);

        if($quantityTotal < 1){continue;}
        ###

        $single_row[] = $intake_id;
        $single_row[] = $pallet_id;
        $single_row[] = $locations[$productsRow['storage_location']]->name;
        $single_row[] = $quantityTotal;


        $single_row[] = trim($temperatures[$productsRow['cooling_id']]->temperature);


        $single_row[] = $species_name;
        $single_row[] = $cutgroup;
        $single_row[] = $cut;

        $single_row[] = $local;

        $single_row[] = $brandname;

        if (!in_array($intake_id, $intakes)) {
            $intakes[$intake_id] = Intake::find($intake_id)->date_received->format('d/m/Y') ?? 'N/A';
        }
        $single_row[] = $intakes[$intake_id];

        $range_from = ($productsRow['range_from'] != '')?$productsRow['range_from']:'N/A';
        $range_to = ($productsRow['range_to'] != '')?$productsRow['range_to']:'N/A';
        if ($productsRow['range_extension'] != null && $productsRow['range_extension'] != '')$range_from = $range_to = $productsRow['range_extension'];
        $single_row[] = $ubtext;
        $single_row[] = $range_from;
        $single_row[] = $range_to;

        if($productsRow['unit'] == 'PPC'){
            $this_total_cost = (double)$productsRow['cost']*$quantityTotal;
            $single_row[] = 'PPC';
        }else{
            if($productsRow['akg'] != ''){
                $weight_value = totalWeightOfAdvisedKGProduct($intake_id, $productsRow['nationality_id']);
            }else{
                $weight_value = totalWeightOfProduct(array($productsRow['productid'])) ;
            }
            if ($weight_value > 0.9)
            {
                $this_total_cost = (double)$productsRow['cost']*$weight_value;
                $single_row[] = $weight_value . 'kg';
                $TOTAL_WEIGHT += $weight_value;
                $TOTAL_QUANTITY += $quantityTotal;
            }
            else
            {
                continue;
            }
        }
        $single_row[] = "£" . number_format((double)$productsRow['cost'], 2, '.', ',');
        $TOTAL_COST += $this_total_cost;
        $final_array[] = $single_row;
    }

    $final_row = array();
    $final_row[] = '';
    $final_row[] = '';
    $final_row[] = '';
    $final_row[] = $TOTAL_QUANTITY;
    $final_row[] = '';
    $final_row[] = '';
    $final_row[] = '';
    $final_row[] = '';
    $final_row[] = '';
    $final_row[] = '';
    $final_row[] = '';
    $final_row[] = '';
    $final_row[] = '';
    $final_row[] = '';
    $final_row[] = number_format((double)$TOTAL_WEIGHT, 3, '.', ',') . 'kg';
    $final_row[] = "£" . number_format((double)floorDec($TOTAL_COST), 2, '.', ',');
    $final_array[] = $final_row;

    require('../../vendor/shuchkin/simplexlsxgen/src/SimpleXLSXGen.php');
    use Shuchkin\SimpleXLSXGen;

    $xlsx = Shuchkin\SimpleXLSXGen::fromArray( $final_array );
    $xlsx->downloadAs('data.xlsx');
?>
