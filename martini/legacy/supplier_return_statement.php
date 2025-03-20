<?php
include_once('includes/frontHeader.php');
include_once('ajax/customer_soa_results_function.php');
$serverRoot = request()->server("SERVER_NAME");
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.0/jspdf.umd.min.js"></script>
<style id="mainStyle">
@media print {
    .printemailbuttons{
        display: none !important;
    }

   .noprint {
    display: none;
   }
   .printme{
    height: 100% !important;
    page-break-inside: avoid;
   }
   .int{
       border-bottom: 0px solid black !important;
   }
}
.topReturn{
    page-break-before: avoid;
    page-break-after: always !important;
    page-break-inside:always;
}
.noprint{
    display: none;
    padding:5px;
    width:50px;
    height:50px !important;
    background: #F44336;
    color: white;
    font-size:22px;
    text-align:center;
    font-size:16pt;
    }

.noprint a{
    color: white;
}
    .printme{
        top: 0px;

    }

    .loadingContainer {
        display: none;
        vertical-align: center;
        background-color: rgba(255,255,255,0.5);
        top: 0;
        left: 0;
        height: 100%;
        width: 100%;
        text-align: center;
        z-index: 20;
    }

    .loadMore {
        text-align: center;
        padding: 15px 20px;
        cursor: pointer;
        margin-top: 7px;
        font-weight: bold;
    }

    .loadMore:hover {
        background: #f7f7f7;
    }

    .mp {
        float: right;
        margin-bottom: 10px;
    }

    .search {
        background: #f8f8f8;
        padding: 10px;
    }

    .back {
        font-size: 18px;
        text-decoration: none;
        color: #888;
        font-weight: bold;
    }

    .table {
        margin-top: 10px;

    }

    .table td {
        height: 30px;
        font-size: 16px;
    }

    tr.heading,
    tr.last {
        font-size: 18px;
        background: #e2e2e2;
        height: 30px;
    }

    tr.even {
        background: #f7f7f7;
    }

    .datePicker {
        width: 150px;
        height: 30px;
    }

    .searchbtn {
        height: 32px;
    }

    .dataTables_length,
    .dataTables_info,
    .dataTables_paginate,
    #soaTable_filter {
        display: none;
    }

    .ingrid{
        display: inline-grid;
        float: right;
    }
    .mainstatement{
        page-break-before: avoid;
    }
</style>
<div id="top" class="printhide">
    <a href="menu.php" id="menu">MENU</a>
    <a href="logout" id="logout">LOGOUT</a>
</div>
<a href="../supplierreturnstatements" class="backbtn">&lt; Back</a>

<div id="printDiv" class="container">

    <?php
    if (request()->input('id') != '') {

        $supplier = prepareExecuteQuery("SELECT * FROM `supplier` WHERE `id` = ?",'i',[request()->input('id')])->fetch_assoc();
    ?>
    <div class="topheading">

    <div class="topReturn">
    <div class="headerinfo">
        <div class="logocontainer" style="text-align: center; line-height: 13px; font-size: 10px; padding-top:10px;">
        <div align="center"><img align="center" class="logo" style="width: 330px;" src="https:<?php echo $domain ?>images/tandclogo.jpg"></div><br/>
            13-17 Landport Ind. Est. Landport Road<br/>
            Wolverhampton WV2 2QJ<br/>
            <span>Vat. No: 701 075 285</span><br/>
            <span>Company Reg. No. 12192223</span><br/>
            <b>01902 457924</b><br/>
        </div>
		<table style="width: 100%;">
        <tr>
        <td>
		<div class="invoice">
			<b style="font-size:10px;color:#8c8c8c;">Return Address</b>
			<div class="invoicebox">
				<p>
					<?php echo $supplier['name']; ?><br/>
					<?php echo $supplier['address_1']; ?><br/>
					<?php echo $supplier['address_2']; ?><br/>
					<?php echo $supplier['address_3']; ?><br/>
                    <?php echo $supplier['address_4']; ?><br/>
                    <?php echo $supplier['postcode']; ?><br/>
                    Supplier ID: <?php echo str_pad($supplier['id'], 4, '0', STR_PAD_LEFT); ?><br/>
                    <?php echo $supplier['email']; ?><br/>
				</p>
			</div>
		</div>
        </td>
        <td align="right">
		<div class="invoice">
			<b style="font-size:10px;color:#8c8c8c;">Supplier Notes</b>
			<div class="invoicebox">
                <form method="POST" action="scripts/saveReturnNotes.php" enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="<?php echo csrf_token();?>">
			        <input type="hidden" name="supplier_id" value="<?php echo $supplier['id']; ?>">
                    <textarea name="return_notes"><?php echo $supplier['return_notes']; ?></textarea>
                    <input type="submit" value="Save">
                </form>
			</div>
		</div>
        </td>
        </tr>
        </table>
        </div>

        <h4><?php echo $supplier['name'];?> (ID: <?php echo $supplier['id'];?>)<br>
        Statement of account as at: <?php echo date('d/m/Y @ H:i');?>
        </h4>

        <div class="loadingContainer" style="display: none;">
            <div class="loadericoncenter">
            <img src="img/loading.gif" alt="">
        </div>
        </div>
        </div>
        <div class="mainstatement">
        <table id="soaTable" class="table" width="100%" style="font-size:10pt;">
            <thead>
                <tr class="heading">
                    <th align="left" >Return ID</th>
                    <th align="left" data-orderable="false" >Date</th>
                    <th align="right" >Value</th>
                    <th align="right" >Paid</th>
                    <th align="right" >Outstanding</th>
                </tr>
            </thead>
            <tbody id="dataResults">
<?php
$supplierPicksheets = prepareExecuteQuery("SELECT * FROM `pickerSheets` WHERE `is_return_to_supplier` = 1 AND `customer_id` = ?",'i',[$supplier['id']])->fetch_all(MYSQLI_ASSOC);
$totalOutstanding = 0;
$totalValue = 0;
$totalPaid = 0;
for ($i = 0; $i < count($supplierPicksheets);$i++) {
    $internalOutstanding = 0;
    $internalValue = 0;
    $internalPaid = 0;
    $picksheet = $supplierPicksheets[$i];
    $returnProducts = prepareExecuteQuery("SELECT *,COUNT(product_id) as `count` FROM `pickeritems` WHERE `pickersheet_id` = ".$picksheet['id']);
    $quickWeightLookup = prepareExecuteQuery("SELECT GROUP_CONCAT(`weight_ids`) as `weight_ids` FROM `palletsout` WHERE `pickersheet_id` = ".$picksheet['id'])->fetch_assoc()['weight_ids'];

    while($returnProduct= mysqli_fetch_assoc($returnProducts))
    {
        if ($returnProduct['unit']=="PPC")
        {
            $itemCost = $returnProduct["price"] * $returnProduct["count"];
        }
        else
        {
            $tear = prepareExecuteQuery("SELECT SUM(`weight_tear`) as `tear` FROM `weights` WHERE id IN (".$quickWeightLookup.") AND `product_id` = ".$returnProduct['product_id'])->fetch_assoc()['tear'];
            $itemCost = $returnProduct["price"] * $tear;
        }
        $internalValue += $itemCost;
    }
    $internalPaid = prepareExecuteQuery("SELECT SUM(`amount`) as `amount` FROM `invoice_payments` WHERE `invoice_id`= ".$picksheet['id'])->fetch_assoc()['amount'];
    if ($internalPaid==null)$internalPaid=0;
    $internalOutstanding = ($internalValue - $internalPaid);
    $totalPaid += $internalPaid;
    $totalValue += $itemCost;
    $totalOutstanding += $internalOutstanding;
?>
    <tr class="<?php if($i%2 == 0){ echo 'odd'; }else{ echo 'even'; } ?>">
        <td <?php if ($i != 0) { ?>style="border-top:1px solid lightgray"<?php } ?> data-order="<?php echo $picksheet['id']; ?>"><a href="single_invoice_payments.php?return=y&customer_id=<?php echo $picksheet['customer_id']; ?>&invoice_id=<?php echo $picksheet['id']; ?>"><?php echo "RN: ".$picksheet['id']; ?>
            <?php
                if(!$picksheet['hasCreditNote']){
                    if($picksheet['hasReturns']){
                        ?><div class="soa_cr_label">CR</div><?php
                    }
                }

            ?>
        </a></td>
            <?php
            $sortableDateFormat = date('d-m-Y',$date);
            //Calculate the Due Date
            ?>
        <td <?php if ($i != 0) { ?>style="border-top:1px solid lightgray"<?php } ?> data-sort="<?php echo $picksheet['sortableDueDateFormat']; ?>" width="100"><?php echo $picksheet['date']; ?></td>
        <td <?php if ($i != 0) { ?>style="border-top:1px solid lightgray"<?php } ?> align="right" width="100" class="digit_value" value="<?php echo number_format($internalValue,2,".",""); ?>"><?php echo '£' . number_format($internalValue,2,".",","); ?></td>
        <td <?php if ($i != 0) { ?>style="border-top:1px solid lightgray"<?php } ?> align="right" width="100" class="digit_paid" value="<?php echo number_format($internalPaid,2,".",""); ?>"><?php echo '£' . number_format($internalPaid, 2, ".", ",");?></td>
        <td style="<?php if ($i != 0) { ?>border-top:1px solid lightgray;<?php }if($internalOutstanding < 0) { echo ' color:red;'; } ?>" align="right" width="100" class="digit_outstanding" value="<?php echo number_format($internalOutstanding, 2, ".", ""); ?>"><?php if(number_format($internalOutstanding, 2, ".", ",") != 0){ echo '£' . number_format($internalOutstanding, 2, ".", ",");}?></td>
    </tr>
    <?php
    }
?>
            </tbody>
            <tfoot class="last">
                <tr>
                    <th align="right"></th>
                    <th align="right">Total:</th>
                    <th align="right" width="120" class="total_digit_value"><?php echo '£' . number_format($totalValue,2,".",","); ?></th>
                    <th align="right" width="120" class="total_digit_paid"><?php echo '£' . number_format($totalPaid,2,".",","); ?></th>
                    <th align="right" width="120" class="total_digit_outstanding"><?php echo '£' . number_format($totalOutstanding,2,".",","); ?></th>
                </tr>
            </tfoot>
        </table>
        </div>
        </div>
    </div>
    <?php
    }
    ?>
</div>
<div class="clearfix"></div>
<script type="text/javascript">
var returnCount = 0;
var crCount = -1;
var renderCompleted = false;
    $.ajaxSetup({
		headers: { 'X-CSRF-TOKEN': "<?php echo csrf_token();?>" }
	});
    function isNumber(n) {
        return !isNaN(parseFloat(n)) && isFinite(n);
    }
    $(document).ready(function() {
        table = $('#soaTable').DataTable({
            "pageLength": -1,
            "order": [[ 0, "ASC" ]],

        });
    });
</script>
