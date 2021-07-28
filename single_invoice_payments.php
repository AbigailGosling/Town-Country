<?php
include('includes/frontHeader.php');

$invoiceID = $_GET['invoice_id'];
$paymentID = $_GET['payment_id'];
$customerID = $_GET['customer_id'];

if (empty($invoiceID)) {
    header('Location: /customer_soa.php?id=' . $customerID);
    die();
}

$invoiceAmount = number_format((float)invoiceTotal($invoiceID), 2, '.', '');

if (!empty($paymentID)) {
    $selectedInvoicePayment = mysqli_query($conn, "SELECT invoice_payments.id, invoice_payments.invoice_id, invoice_payments.payment_method, invoice_payments.meta_data, invoice_payments.created_at,invoice_payments.amount, invoice_payments.payment_recorded_by,users.name FROM `invoice_payments` join users on invoice_payments.payment_recorded_by = users.id WHERE invoice_payments.id = $paymentID AND invoice_payments.invoice_id = $invoiceID");
    $selectedPaymentData = mysqli_fetch_assoc($selectedInvoicePayment);
    //print_r($selectedPaymentData);
}

?>

<link href="/css/bootstrap.min.css" rel="stylesheet" >

<div id="top">
    <a href="menu.php" id="menu">MENU</a>
    <a href="logout.php" id="logout">LOGOUT</a>
</div>
<div class="search">
    <div class="container flex space-between" style="align-items:center">
        <a href="/customer_soa.php?id=<?php echo $customerID; ?>" class="back">BACK</a>
    </div>
</div>
<div class="container">
    <h3>Payments for the invoice ID <?php echo $invoiceID; ?></h3>
    <table class="table table-bordered table-striped" width="100%">
        <thead>
            <tr>
                <th align="left">#</th>
                <th align="left">Invoice ID</th>
                <th align="left">Payment Method</th>
                <th align="left">Paid On</th>
                <th align="left">Payment Entered By</th>
                <th align="left">Edit</th>
                <th align="left">Delete</th>
                <th align="right">Amount</th>
            </tr>
        </thead>
        <tbody>
        <?php

        $invoicePayments = mysqli_query($conn, "SELECT invoice_payments.id, invoice_payments.invoice_id, invoice_payments.payment_method, invoice_payments.created_at,invoice_payments.amount, invoice_payments.payment_recorded_by,users.name FROM `invoice_payments` join users on invoice_payments.payment_recorded_by = users.id WHERE invoice_id = $invoiceID");

        $runningBalance = $invoiceAmount;
        $i = 0;
        while ($invoicePayment = mysqli_fetch_array($invoicePayments)) { ?>
            <tr>
                <td><?php echo $invoicePayment['id']; ?></td>
                <td><?php echo $invoicePayment['invoice_id']; ?></td>
                <td>
                    <?php
                        if($invoicePayment['payment_method'] == 'CREDIT_NOTE'){
                        ?><a target="_blank" href="/ajax/generatePDFcreditnote.php?id=<?php echo $invoiceID; ?>&payment_id=<?php echo $invoicePayment['id']; ?>"><?php echo $invoicePayment['payment_method']; ?></a><?php
                        }else{
                            echo $invoicePayment['payment_method'];
                        }
                        
                    ?>
                </td>
                <td><?php echo $invoicePayment['created_at']; ?></td>
                <td><?php echo $invoicePayment['name']; ?></td>
                <td align="center">
                    <a href="/single_invoice_payments.php?customer_id=<?php echo $_GET['customer_id']; ?>&invoice_id=<?php echo $invoicePayment['invoice_id']; ?>&payment_id=<?php echo $invoicePayment['id']; ?>"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>
                </td>
                <td align="center">
                <form method="POST" action="/scripts/_deleteInvoicePayment.php">
                    <input type="hidden" name="return_url" value="<?php echo "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>">
                    <input type="hidden" name="invoice_id" value="<?php echo $invoicePayment['id']; ?>">

                    <button type="submit" style="border:0px;background:none;"><i class="fa fa-trash" aria-hidden="true" style="color:red;font-size:18px !important"></i></button>
                </form>
                </td>
                <td align="right">
                    <?php
                        if($invoicePayment['payment_method'] == 'CREDIT_NOTE'){
                            $credit_note_total = creditNoteTotal($invoicePayment['id']);
                            $runningBalance -= $credit_note_total;
                            echo '<span style="color:red;font-weight:bold">-</span> £' . number_format($credit_note_total, 2, ".", ",");
                        }else{
                            $runningBalance -= $invoicePayment['amount'];
                            echo '<span style="color:red;font-weight:bold">-</span> £' . number_format($invoicePayment['amount'], 2, ".", ",");
                        }
                    ?>
                </td>
            </tr>
        <?php } ?>
        </tbody>
        <tfoot>
            <tr>
                <td align="right" colspan="7">Balance</td>
                <td align="right" colspan="1">£<?php echo number_format($runningBalance, 2, ".", ","); ?></td>
            </tr>
        </tfoot>
        
    </table>
</div>

<?php if($runningBalance != 0 || !empty($paymentID)){ ?>
<div class="container container--pt">
    <div style="background:#f2f2f2;padding:10px;">
<h2 style="font-size:22px;padding-bottom:10px;">Original Invoice</h2>
<table width="100%" border="0">
<tr style="border-bottom:1px solid #f1f1f1;">
    <th align="left">Intake ID</th>
    <th align="left">Pallet ID</th>
    <th align="left">Product</th>
    <th align="left">Quantity</th>
    <th align="left">Unit</th>
    <th align="left">Weight</th>
    <th align="left">Price</th>
    <th align="left"></th>
</tr>
<?php

    $outpalletResult = mysqli_query($conn, "SELECT * FROM `palletsOut` WHERE pickersheet_id='$invoiceID'");
    $outpalletCount = mysqli_num_rows($outpalletResult);

    $total_weight_count = 0;
    $total_case_count = 0;
    $rowCount = 0;

    while($outpallet = mysqli_fetch_array($outpalletResult)){
        $rowCount++;
        $rowClass = "productRow" . $rowCount;

        $weightids = explode(',', $outpallet['weight_ids']);

        $productIDArray = array();

        foreach($weightids as $weightid){
            $x = "SELECT * FROM `weights` WHERE id='$weightid'";
            $y = mysqli_query($conn, $x);
            $weight = mysqli_fetch_array($y);

            if(!in_array($weight['product_id'], $productIDArray)){
                array_push($productIDArray, $weight['product_id']);
            }

            $queryBits .= ' id = ' . $weightid . ' || ';
        }
        $kg = 0;
        foreach($productIDArray as $productID){

            $x1 = "SELECT * FROM `product` WHERE id='$productID'";
            $y1 = mysqli_query($conn, $x1);
            $product = mysqli_fetch_array($y1);


            if($product['unit'] == 'PPC'){
                $ext = ' Cases';
            }else{
                $ext = ' kg';
            }

            $x2 = "SELECT * FROM `weights` WHERE ";

            foreach($weightids as $weightid){
                $x2 .= "product_id='$productID' && id='$weightid' || ";
            }

            $x2 = rtrim($x2," || ");
            $y2 = mysqli_query($conn, $x2);
            $count = mysqli_num_rows($y2);
            
             
            
            while($weightRow = mysqli_fetch_array($y2)){               
                if($weightRow['weight_tear'] == $weightRow['weight_gross']){
                    $tw = $weightRow['weight_gross'];
                }else{
                    $tw = $weightRow['weight_gross'] - $weightRow['weight_tear'];
                }
                
                $kg = $kg + $tw;
                
                $kg = number_format($kg, 2, '.', '');
            }

        ?>
        <tr class="<?php echo $rowClass; ?>" style="height:50px;border-bottom:1px solid #f1f1f1;">
            <td align="left"><span class=""><?php echo intakeIDfromPalletID($product['pallet_id']); ?></span></td>
            <td align="left"><span class=""><?php echo $product['pallet_id']; ?></span></td>
            <td align="left">                    
                <span class=""><?php echo getNationality($product['nationality_id']); ?></span>
                <span class=""><?php echo getTemp($product['cooling_id']); ?></span>
                <b class=""><?php echo getSpeciesFromCutID($product['cut_id']); ?></b>
                <b class=""><?php echo getCut($product['cut_id']); ?></b>
                <b class=""><?php echo getBrand($product['brand_id']); ?></b>
            </td>

            <?php
                $productID = $product['id'];
                $howManyX = "SELECT * FROM `pickerItems` WHERE pickersheet_id='$invoiceID' AND product_id='$productID'";
                $howManyY = mysqli_query($conn, $howManyX);
                $pickerItem = mysqli_fetch_array($howManyY);
                $howMany = mysqli_num_rows($howManyY);
            ?>
            <td align="left"><b class="">
                <b><?php echo $count; ?></b>
            </td>
            <td align="left">
                <b class="s">
                <?php

                    if($product['unit'] == 'C'){
                        $unit = 'Cases';
                    }else if($product['unit'] == 'PPC'){
                        $unit = 'Per Case';
                    }else if($product['unit'] == 'P'){
                        $unit = 'Pallet';
                    }else if($product['unit'] == 'KG'){
                        $unit = 'Kilo';
                    }else{
                        $unit = 'Cases';
                    }

                    echo $unit;
                ?>
                </b>
            </td>
            <td>
                <?php echo $kg; ?> kg
            </td>
             
            <td align="left" class="">£<input type="number" disabled style="outline:none;border:0;border-bottom:1px dashed black;width:100px;margin-left:10px;" value="<?php echo number_format((float)$pickerItem['price'], 2, '.', ''); ?>"></td>
            <td>
            </td>
        </tr>
        <?php
            }
        }
    ?>
</table> 
</div>
<br/>

    <div class="row">
        <div class="col">
            <h2 style="font-size:22px;padding-bottom:10px;"><?php echo (empty($_GET['payment_id'])) ? 'Add' : 'Edit'; ?> Payment</h2>
        </div>
    </div>
    <form id="payment_entry" method="POST" action="/scripts/save_invoice_payment_entry.php">
        <div class="row">
            <div class="col">
                <label for="invoice_id">Invoice ID</label>
                <input class="form-control" id="invoice_id" type="text" name="invoice_id" value="<?php echo $invoiceID; ?>" />
            </div>
            <div class="col">
                <label for="payment_method">Payment Method</label>
                <select class="form-select" id="payment_method" name="payment_method">
                    <?php foreach (PAYMENT_METHODS as $paymentMethod) {

                        if ((!empty($selectedPaymentData)) && $selectedPaymentData['payment_method'] == $paymentMethod) {
                            echo '<option value="' . $paymentMethod . '" selected>' . $paymentMethod . '</option>';
                        } else {
                            echo '<option value="' . $paymentMethod . '" >' . $paymentMethod . '</option>';
                        }
                    } ?>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col" id="amountContainer">
                <label for="amount">Amount</label>
                <input class="form-control" id="amount" type="text" name="amount" value="<?php echo (!empty($selectedPaymentData)) ? $selectedPaymentData['amount'] : $runningBalance; ?>" />
            </div>
            <div class="col">
                <label for="meta_data">Additional Notes</label>
                <input class="form-control" id="meta_data" name="meta_data" type="text" placeholder="Cheque No., Bank Transaction No." value="<?php echo (!empty($selectedPaymentData)) ? $selectedPaymentData['meta_data'] : ''; ?>" />
            </div>
        </div>
        <div class="row">
            <div class="products_container">
                 <?php
            if ((!empty($selectedPaymentData)) && $selectedPaymentData['payment_method'] == 'CREDIT_NOTE'){
            ?>
            <script>
                $('#amount').val(1);
                $('#amountContainer').hide();
            </script>
            <table width="75%" border="0">
                <tr style="border-bottom:1px solid #f1f1f1;" class="product-return-header">
                    <th align="left">Intake ID</th>
                    <th align="left">Pallet ID</th>
                    <th align="left">Product</th>
                    <th align="left">Quantity</th>
                    <th align="left">Unit</th>
                    <th align="left">Weight</th>
                    <th align="left">Price</th>
                    <th align="left"></th>
                </tr>
                <?php
                
                $payment_id = $selectedPaymentData['id'];

                
                $creditNoteResult = mysqli_query($conn, "SELECT GROUP_CONCAT(product_id) as product_ids FROM `credit_note_items` WHERE payment_id=$payment_id");
                $creditNoteData = mysqli_fetch_array($creditNoteResult);
                $productIDs = $creditNoteData['product_ids'];

                $productIDs = explode(',', $productIDs);

                foreach($productIDs as $productID){
                    $i++;
                    $rowClass = "productRow" . $i;
                    
                    $creditNoteResult = mysqli_query($conn, "SELECT * FROM `credit_note_items` WHERE product_id=$productID && payment_id=$payment_id");
                    $creditNoteDetails = mysqli_fetch_array($creditNoteResult);

                    if($productID == 0){
                    ?>
                    <script> $('.product-return-header').hide(); </script>
                    <tr style="border-bottom:1px solid #f1f1f1;">
                        <th align="left">Description</th>
                        <th align="left">Quantity</th>
                        <th align="left"></th>
                    </tr>
                    <tr class="" style="height:50px;border-bottom:1px solid #f1f1f1;">
                        <td align="left">
                            <input type="hidden" name="product_id[]" value="0">
                            <input type="text" name="description[]" value="<?php echo $creditNoteDetails['description']; ?>">
                        </td>

                        <td align="left">
                            <input type="text" name="quantity[]" style="width:90px;" value="<?php echo $creditNoteDetails['quantity']; ?>">
                        </td>
                        <td align="left" class="">£<input type="text" name="price[]" style="outline:none;border:0;border-bottom:1px dashed black;width:100px;margin-left:10px;" value="<?php echo $creditNoteDetails['price']; ?>"></td>
                        <td>
                            <a href="javascript:removeProductRow('<?php echo $rowClass; ?>');" class="fa fa-times" style="color:red;text-decoration:none;font-size:22px;"></a>
                        </td>
                    </tr>
                    <?php
                    }else{

                    # get number of weights for this product
                    $weightCountResult = mysqli_query($conn, "SELECT id FROM `weights` WHERE product_id=$productID");
                    $count = mysqli_num_rows($weightCountResult);
                    
                    $productResult = mysqli_query($conn, "SELECT * FROM `product` WHERE id='$productID'");
                    $product = mysqli_fetch_array($productResult);
                    
                ?>
                <tr class="<?php echo $rowClass; ?>" style="height:50px;border-bottom:1px solid #f1f1f1;">
                    <td align="left">
                        <span class=""><?php echo intakeIDfromPalletID($product['pallet_id']); ?></span>
                        <input type="hidden" name="product_id[]" value="<?php echo $product['id']; ?>">    
                    </td>
                    <td align="left"><span class=""><?php echo $product['pallet_id']; ?></span></td>
                    <td align="left">                    
                        <span class=""><?php echo getNationality($product['nationality_id']); ?></span>
                        <span class=""><?php echo getTemp($product['cooling_id']); ?></span>
                        <b class=""><?php echo getSpeciesFromCutID($product['cut_id']); ?></b>
                        <b class=""><?php echo getCut($product['cut_id']); ?></b>
                        <b class=""><?php echo getBrand($product['brand_id']); ?></b>
                    </td>
                    
                    <?php
                        $productID = $product['id'];
                        $howManyX = "SELECT * FROM `pickerItems` WHERE pickersheet_id='$invoiceID' AND product_id='$productID'";
                        $howManyY = mysqli_query($conn, $howManyX);
                        $pickerItem = mysqli_fetch_array($howManyY);
                        $howMany = mysqli_num_rows($howManyY);
                    ?>
                    <td align="left"><b class="">
                        <select style="width:55px;height:30px;" name="quantity[]">
                            <?php
                                $tempcount = $count+1;
                                for($i=1;$i<$tempcount;$i++) { ?>
                                <option value="<?php echo $i; ?>" <?php if($i == $creditNoteDetails['quantity']){ echo 'selected'; } ?>><?php echo $i; ?></option>
                            <?php } ?>
                        </select>
                    </td>
                    <td>
                    <?php
                        if($product['unit'] == 'C'){
                            $unit = 'Cases';
                        }else if($product['unit'] == 'PPC'){
                            $unit = 'Per Case';
                        }else if($product['unit'] == 'P'){
                            $unit = 'Pallet';
                        }else if($product['unit'] == 'KG'){
                            $unit = 'Kilo';
                        }else{
                            $unit = 'Cases';
                        }

                        echo $unit;
                    ?>
                </td>
                <td><?php echo weightFromProductIDArray([$product['id']]); ?> kg</td>
                    <td align="left" class="">£<input type="number" name="price[]" style="outline:none;border:0;border-bottom:1px dashed black;width:100px;margin-left:10px;" value="<?php echo number_format((float)$creditNoteDetails['price'], 2, '.', ''); ?>"></td>
                    <td>
                     </td>
                </tr>
                <?php
                    }
                }
            }
        ?>
        </table>
            </div>
        </div>
        <br/>
        
        <div class="row">
            <div class="col d-flex justify-content-start">
                <input type="hidden" name="customer_id" value="<?php echo $customerID; ?>" />
                <input type="hidden" name="payment_id" value="<?php echo (!empty($selectedPaymentData)) ? $selectedPaymentData['id'] : ''; ?>" />
                <input class="btn btn-success" type="submit" value="SUBMIT" />
            </div>
        </div>
    </form>    
</div>
<?php } ?>

<div class="clearfix"></div>
<script type="text/javascript">
    
    function removeProductRow(rowClass){
        $('.' + rowClass).remove();
    }

    $('#payment_method').change(function(){
        var payment_type = $(this).val(); 
 
        if(payment_type == 'CREDIT_NOTE'){
            $('.products_container').fadeIn();
            $('#amountContainer').hide();

            var xhttp = new XMLHttpRequest();
			xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    $('.products_container').html(this.responseText);
                }
			};

			xhttp.open("POST", "/ajax/credit_note_product_list.php", true);
			xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xhttp.send("invoiceID=" + <?php echo $invoiceID; ?>);
        }else{
            $('.products_container').fadeOut();
            $('#amountContainer').show();
        }

    });
</script>

<style type="text/css">
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

    .error {
        border: 1px solid red;
    }
</style>

<script>
    $(document).ready(function() {
        $('#payment_entry').submit(function() {
            return validateForm();
        });
    });


    function validateForm() {

        var _invoice_id = '' + <?php echo intval($invoiceID); ?>;
        $('#invoice_id').removeClass('error');
        $('#amount').removeClass('error');

        var isValid = true;
        var invoice_id = $('#invoice_id').val();
        var amount = parseFloat($('#amount').val());


        if (!isNumber(amount)) {
            isValid = false;
            $('#amount').addClass('error');
        }

        if (_invoice_id != invoice_id) {
            isValid = false;
            $('#invoice_id').addClass('error');
        }

        return isValid;
    }

    function isNumber(n) {
        return !isNaN(parseFloat(n)) && isFinite(n) && n > 0;
    }
</script>