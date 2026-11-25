<?php
require(__DIR__.'/../functions.php');

$invoice_id = request()->input('invoice_id');
if ($invoice_id == null || $invoice_id == '') {
    echo '<div class="alert alert-info">No invoice selected.</div>';
    exit;
}

$picksheet = prepareExecuteQuery("SELECT * FROM `pickerSheets` WHERE `id` = ? AND `deleted` = 0 LIMIT 1", 'i', [$invoice_id]);
if (mysqli_num_rows($picksheet) == 0) {
    echo '<div class="alert alert-warning">No invoice found.</div>';
    exit;
}

$picksheet_row = mysqli_fetch_array($picksheet);
$items = prepareExecuteQuery("SELECT * FROM `pickerItems` WHERE `pickersheet_id` = ?", 'i', [$invoice_id]);
$product_ids = [];
while ($item = mysqli_fetch_array($items)) {
    $product_ids[] = $item['product_id'];
}

if (count($product_ids) == 0) {
    echo '<div class="alert alert-info">This invoice has no items recorded.</div>';
    exit;
}

$placeholders = implode(',', array_fill(0, count($product_ids), '?'));
$products = prepareExecuteQuery("SELECT * FROM `product` WHERE `id` IN ($placeholders)", str_repeat('i', count($product_ids)), $product_ids);
?>
<div style="margin-top:20px; position:relative;">
    <h4 style="margin-bottom:15px;">Invoice Items</h4>
    <table width="100%" class="basketTable2" id="invoice_items_table">
        <tr align="left" style="background:#3FADDD;height:30px;color:#FFF;">
            <th>Intake ID</th>
            <th>Plt ID</th>
            <th width="45%">Product</th>
            <th align="right">Dated</th>
            <th>Nationality</th>
            <th>Brand</th>
            <th>Quantity</th>
            <th>Unit</th>
            <th>Price</th>
            <th>Add</th>
        </tr>
        <?php while ($product = mysqli_fetch_array($products)) { ?>
            <?php
                $thispalletid = $product['pallet_id'];
                $pallet = mysqli_fetch_array(prepareExecuteQuery("SELECT * FROM `pallet` WHERE `id` = ?", 'i', [$thispalletid]));
                $smallestDate = ($product['range_extension'] != null && $product['range_extension'] != '') ? 'EXTENSION' : $product['range_from'];
                $largestDate = ($product['range_extension'] != null && $product['range_extension'] != '') ? $product['range_extension'] : $product['range_to'];
                $pickerItemRows = prepareExecuteQuery("SELECT * FROM `pickerItems` WHERE `pickersheet_id` = ? AND `product_id` = ?", 'ii', [$invoice_id, $product['id']]);
                $pickerItem = mysqli_fetch_array($pickerItemRows);
                $howMany = mysqli_num_rows($pickerItemRows);
                $productName = trim(getSpeciesFromCutID($product['cut_id']).' '.getCut($product['cut_id']));
                $dated = $smallestDate.'-'.$largestDate;
                $nationality = getNationality($product['nationality_id']);
                $brand = getBrand($product['brand_id']);
                $unit = 'p/KG';
                if ($product['unit'] == 'C') $unit = 'p/KG';
                else if ($product['unit'] == 'PPC') $unit = 'Per Case';
                else if ($product['unit'] == 'P') $unit = 'Pallet';
                else if ($product['unit'] == 'KG') $unit = 'Kilo';
            ?>
            <tr class="productsRow">
                <td align="left"><span class="intakeid"><?php echo $pallet['intake_id']; ?></span></td>
                <td align="left"><span class="palletid"><?php echo $product['pallet_id']; ?></span></td>
                <td align="left"><b class="species"><?php echo $productName; ?></b></td>
                <td align="right"><b class="species"><?php echo $dated; ?></b></td>
                <td align="left"><span class="chilled"><?php echo $nationality; ?></span></td>
                <td align="left"><b class="brand"><?php echo $brand; ?></b></td>
                <td align="left"><b class="howmany"><?php echo $howMany; ?></b></td>
                <td align="left"><b class="unit"><?php echo $unit; ?></b></td>
                <td><?php echo '£'. number_format((double)$pickerItem['price'], 2, '.', ''); ?></td>
                <td>
                    <select class="invoice-item-quantity" style="width:70px; margin-right:6px;">
                        <?php for ($qty = 1; $qty <= max((int)$howMany, 1); $qty++) { ?>
                            <option value="<?php echo $qty; ?>"><?php echo $qty; ?></option>
                        <?php } ?>
                    </select>
                    <button
                        type="button"
                        class="add-invoice-item-btn"
                        data-intake-id="<?php echo htmlspecialchars((string)$pallet['intake_id'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-pallet-id="<?php echo htmlspecialchars((string)$product['pallet_id'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-product-id="<?php echo (int)$product['id']; ?>"
                        data-product="<?php echo htmlspecialchars($productName, ENT_QUOTES, 'UTF-8'); ?>"
                        data-dated="<?php echo htmlspecialchars($dated, ENT_QUOTES, 'UTF-8'); ?>"
                        data-nationality="<?php echo htmlspecialchars($nationality, ENT_QUOTES, 'UTF-8'); ?>"
                        data-brand="<?php echo htmlspecialchars($brand, ENT_QUOTES, 'UTF-8'); ?>"
                        data-quantity="<?php echo (int)$howMany; ?>"
                        data-unit="<?php echo htmlspecialchars($unit, ENT_QUOTES, 'UTF-8'); ?>"
                        data-price="<?php echo (float)$pickerItem['price']; ?>"
                        data-target-weight="<?php echo (float)($pickerItem['target_weight'] ?? 0); ?>"
                    >Add</button>
                </td>
            </tr>
        <?php } ?>
    </table>
</div>
