<?php
include('includes/frontHeader.php');

$invoiceID = request()->input('invoice_id');
$paymentID = request()->input('payment_id');
$customerID = request()->input('customer_id');

if (empty($invoiceID)) {
    header('Location: /customer_soa.php?id=' . $customerID);
    die();
}

$invoiceAmount = number_format((double)invoiceTotal($invoiceID), 2, '.', '');

if (!empty($paymentID)) {
    $selectedInvoicePayment = loggedQuery("SELECT invoice_payments.id, invoice_payments.invoice_id, invoice_payments.payment_method, invoice_payments.meta_data, invoice_payments.created_at,invoice_payments.amount, invoice_payments.payment_recorded_by,users.name FROM `invoice_payments` join users on invoice_payments.payment_recorded_by = users.id WHERE invoice_payments.id = ? AND invoice_payments.invoice_id = ?",'ii',[$paymentID,$invoiceID]);
    $selectedPaymentData = mysqli_fetch_assoc($selectedInvoicePayment);
    //print_r($selectedPaymentData);
}

?>

<link href="css/bootstrap.min.css" rel="stylesheet" >

<div id="top">
    <a href="menu.php" id="menu">MENU</a>
    <a href="logout" id="logout">LOGOUT</a>
</div>
<div class="search">
    <div class="container flex space-between" style="align-items:center">
        <a href="customer_soa.php?id=<?php echo $customerID; ?>" class="back">BACK</a>
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

        $invoicePayments = loggedQuery("SELECT invoice_payments.id, invoice_payments.invoice_id, invoice_payments.payment_method, invoice_payments.created_at,invoice_payments.amount, invoice_payments.payment_recorded_by,users.name FROM `invoice_payments` left join users on invoice_payments.payment_recorded_by = users.id WHERE invoice_id = ?",'i',[$invoiceID]);

        $runningBalance = $invoiceAmount;
        $i = 0;
        while ($invoicePayment = mysqli_fetch_array($invoicePayments)) { ?>
            <tr>
                <td><?php echo $invoicePayment['id']; ?></td>
                <td><?php echo $invoicePayment['invoice_id']; ?></td>
                <td>
                    <?php
                        if($invoicePayment['payment_method'] == 'CREDIT_NOTE'){
                        ?><a target="_blank" href="ajax/generatePDFcreditnote.php?id=<?php echo $invoiceID; ?>&payment_id=<?php echo $invoicePayment['id']; ?>"><?php echo $invoicePayment['payment_method']; ?></a><?php
                        }else{
                            echo $invoicePayment['payment_method'];
                        }
                        
                    ?>
                </td>
                <td><?php echo $invoicePayment['created_at']; ?></td>
                <td><?php echo $invoicePayment['name']; ?></td>
                <td align="center">
                    <a href="single_invoice_payments.php?customer_id=<?php echo request()->input('customer_id'); ?>&invoice_id=<?php echo $invoicePayment['invoice_id']; ?>&payment_id=<?php echo $invoicePayment['id']; ?>"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>
                </td>
                <td align="center">
                <form id="mainForm<?php echo $invoicePayment['id']; ?>" method="POST" action="scripts/_deleteInvoicePayment.php">
                    <input type="hidden" name="return_url" value="<?php echo "https://".request()->server('HTTP_HOST').request()->server('REQUEST_URI'); ?>">
                    <input type="hidden" name="invoice_id" value="<?php echo $invoicePayment['id']; ?>">

                    <button type="button" onclick="mainForm(<?php echo $invoicePayment['id']; ?>)" style="border:0px;background:none;"><i class="fa fa-trash" aria-hidden="true" style="color:red;font-size:18px !important"></i></button>
                </form>
                </td>
                <td align="right">
                    <?php
                        if($invoicePayment['payment_method'] == 'CREDIT_NOTE'){
                            $credit_note_total = creditNoteTotal($invoicePayment['id']);
                            $runningBalance -= $credit_note_total;
                            echo '<span style="color:green;font-weight:bold">+</span> £' . number_format($credit_note_total, 2, ".", ",");
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

    $outpalletResult = loggedQuery("SELECT * FROM `palletsOut` WHERE pickersheet_id=?",'i',[$invoiceID]);
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
            $x = "SELECT * FROM `weights` WHERE id=?";
            $y = loggedQuery($x,'i',[$weightid]);
            $weight = mysqli_fetch_array($y);

            if(!in_array($weight['product_id'], $productIDArray)){
                array_push($productIDArray, $weight['product_id']);
            }

            $queryBits .= ' id = ' . $weightid . ' || ';
        }
        foreach($productIDArray as $productID){
            $kg = 0;
            $x1 = "SELECT * FROM `product` WHERE id=?";
            $y1 = loggedQuery($x1,'i',[$productID]);
            $product = mysqli_fetch_array($y1);


            if($product['unit'] == 'PPC'){
                $ext = ' Cases';
            }else{
                $ext = ' kg';
            }
            $qVars = $weightids;
            array_unshift($qVars , $productID);
            $x2 = "SELECT * FROM `weights` WHERE product_id=? AND id IN (".implode(",",array_fill(0,count($weightids),"?")).")";

            $y2 = loggedQuery($x2,str_repeat('i',count($qVars)),$qVars);
            $count = mysqli_num_rows($y2);
            
             
            
            while($weightRow = mysqli_fetch_array($y2)){               
                if($weightRow['weight_tear'] == $weightRow['weight_gross']){
                    $tw = (double)$weightRow['weight_gross'];
                }else{
                    $tw = (double)$weightRow['weight_gross'] - (double)$weightRow['weight_tear'];
                }
                
                $kg = $kg + $tw;
                
                $kg = number_format($kg, 3, '.', '');
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
                $howManyX = "SELECT * FROM `pickerItems` WHERE pickersheet_id=? AND product_id=?";
                $howManyY = loggedQuery($howManyX,'ii',[$invoiceID,$productID]);
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
             
            <td align="left" class="">£<input type="number" disabled style="outline:none;border:0;border-bottom:1px dashed black;width:100px;margin-left:10px;" value="<?php echo number_format((double)$pickerItem['price'], 2, '.', ''); ?>"></td>
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
            <h2 style="font-size:22px;padding-bottom:10px;"><?php echo (empty(request()->input('payment_id'))) ? 'Add' : 'Edit'; ?> Payment</h2>
        </div>
    </div>
    <form id="payment_entry" method="POST" action="scripts/save_invoice_payment_entry.php">
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
                <input class="form-control" id="amount" type="text" name="amount" value="<?php echo (!empty($selectedPaymentData)) ? $selectedPaymentData['amount'] : number_format($runningBalance, 2, ".", ""); ?>" />
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
                <tr style="border-bottom:1px solid #f1f1f1;display:none;" class="product-custom-return-header">
                    <th align="left">Description</th>
                    <th align="left">Quantity</th>
                    <th align="left"></th>
                </tr>
                <?php
                
                $payment_id = $selectedPaymentData['id'];

                
                $creditNoteResult = loggedQuery("SELECT GROUP_CONCAT(product_id) as product_ids FROM `credit_note_items` WHERE payment_id=?",'i',[$payment_id]);
                $creditNoteData = mysqli_fetch_array($creditNoteResult);
                $productIDs = $creditNoteData['product_ids'];

                $productIDs = explode(',', $productIDs);

                foreach($productIDs as $productID){
                    $i++;
                    
                    $creditNoteResult = loggedQuery("SELECT * FROM `credit_note_items` WHERE product_id=? && payment_id=?",'ii',[$productID,$payment_id]);
                     

                    if($productID == 0){
                        ?>
                        <input type="hidden" name="delete_ids" id="delete_ids">
                        <?php
                        while($creditNoteDetails = mysqli_fetch_array($creditNoteResult)){
                            $rowClass = "customProductRow" . $creditNoteDetails['id'];
                        ?>
                        <script>
                            $('.product-return-header').hide();
                            $('.product-custom-return-header').show();
                        </script>
                        
                        <tr class="<?php echo $rowClass; ?>" style="height:50px;border-bottom:1px solid #f1f1f1;">
                            <td align="left">
                                <input type="hidden" name="product_id[]" value="0">
                                <input type="hidden" name="credit_id[]" value="<?php echo $creditNoteDetails['id']; ?>">
                                <input type="text" name="description[]" value="<?php echo $creditNoteDetails['description']; ?>" required>
                            </td>

                            <td align="left">
                                <input type="text" name="quantity[]" style="width:90px;" value="<?php echo $creditNoteDetails['quantity']; ?>" required>
                            </td>
                            <td align="left" class="">£<input type="text" name="price[]" style="outline:none;border:0;border-bottom:1px dashed black;width:100px;margin-left:10px;" value="<?php echo $creditNoteDetails['price']; ?>" required></td>
                            <td>
                                <a href="javascript:removeProductRow('<?php echo $rowClass; ?>');deleteId(<?php echo $creditNoteDetails['id']; ?>);" class="fa fa-times" style="color:red;text-decoration:none;font-size:22px;"></a>
                            </td>
                        </tr>
                        <?php
                        }
                        break;
                    }else{
                        $creditNoteDetails = mysqli_fetch_array($creditNoteResult);
                        # get number of weights for this product
                        $weightCountResult = loggedQuery("SELECT id FROM `weights` WHERE product_id=?",'i',[$productID]);
                        $count = mysqli_num_rows($weightCountResult);
                        
                        $productResult = loggedQuery("SELECT * FROM `product` WHERE id=?",'i',[$productID]);
                        $product = mysqli_fetch_array($productResult);

                        $rowClass = "customProductRow" . $i;
                    
                ?>
                <tr class="<?php echo $rowClass; ?>" style="height:50px;border-bottom:1px solid #f1f1f1;">
                    <td align="left">
                        <span class=""><?php echo intakeIDfromPalletID($product['pallet_id']); ?></span>
                        <input type="hidden" name="product_id[]" value="<?php echo $product['id']; ?>">
                        <input type="hidden" name="credit_id[]" value="<?php echo $creditNoteDetails['id']; ?>">
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
                        $howManyX = "SELECT * FROM `pickerItems` WHERE pickersheet_id=? AND product_id=?";
                        $howManyY = loggedQuery($howManyX,'ii',[$invoiceID,$productID]);
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
                    <td align="left" class="">£<input type="text" name="price[]" style="outline:none;border:0;border-bottom:1px dashed black;width:100px;margin-left:10px;" value="<?php echo number_format((double)$creditNoteDetails['price'], 2, '.', ''); ?>"></td>
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
                <input class="btn btn-success" type="button" onclick="mainForm2()" value="SUBMIT" />  
            </div>
        </div>
    </form>    
</div>

<div class="clearfix"></div>
<script type="text/javascript">
    
    function deleteId(id){
        var ids = $('#delete_ids').val();
        $('#delete_ids').val(ids + id + ',');
    }
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

			xhttp.open("POST", "ajax/credit_note_product_list.php", true);
			xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhttp.setRequestHeader('X-CSRF-TOKEN', "<?php echo csrf_token();?>");
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
    function mainForm(id){
	$('#mainForm'+id).ajaxSubmit({headers:{'X-CSRF-TOKEN': "<?php echo csrf_token();?>"},success:mainFormSucess});
}
function mainFormSucess(){
	location.reload();
}
function mainForm2(){
	$('#payment_entry').ajaxSubmit({headers:{'X-CSRF-TOKEN': "<?php echo csrf_token();?>"},success:mainFormSucess});
}
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


        if (!isNumber(amount) && $('#payment_method').val() != 'CREDIT_NOTE') {
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
        return !isNaN(parseFloat(n)) && isFinite(n);
    }
</script>