<?php
include('includes/frontHeader.php');


$customerID = request()->input('customer_id');

if (empty($customerID)) {
    header('Location: /');
    die();
}

$customer = getCustomer($customerID);

?>

<link href="css/bootstrap.min.css" rel="stylesheet" >
<script src="/legacy/js/jquery.numeric.js"></script>

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

    <div class="row">
        <div class="col">
            <h3>Payments for the <?php echo $customer['businessname']; ?></h3>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <div class="row">
                <div class="col">
                    <label for="invoices">Select Invoices</label>
                    <select class="form-control" id="invoices" name="invoices">
                        <?php
                        $customerPicksheets = prepareExecuteQuery("SELECT pickerSheets.*, SUM(invoice_payments.amount) as paid, GROUP_CONCAT(invoice_payments.id) as payment_ids FROM `pickerSheets` left join invoice_payments on pickerSheets.id = invoice_payments.invoice_id WHERE (pickerSheets.completed = 1 AND pickerSheets.is_return_to_supplier = 0 AND pickerSheets.customer_id=?)  AND `invoice_payments`.`deleted`=0 GROUP by pickerSheets.id",'i',[$customerID]);
                        ini_set('memory_limit',(min(3072,$customerPicksheets->num_rows*0.3))."M");
                        while ($picksheet = mysqli_fetch_assoc($customerPicksheets)) {
                            $this_price = invoiceTotal($picksheet['id']);
                            $creditVal = 0;
                            if ($picksheet['payment_ids']!="")
                            {
                                $pickSheetCredits = prepareExecuteQuery("SELECT payment_id FROM credit_note_items WHERE payment_id IN (".$picksheet['payment_ids'].") AND `credit_note_items`.`deleted` = 0");
                                while ($credit = $pickSheetCredits->fetch_assoc())
                                {
                                    $creditVal = $creditVal + creditNoteTotal($credit['payment_id']);
                                }
                            }
                            $epsilon = 0.00001;
                            if(($this_price - ($picksheet['paid'] + $creditVal)) > $epsilon){
                                echo '<option id="' . $picksheet['id'] . '" data-tot-val="' . $this_price . '" value="' . $picksheet['id'] . '">' . $picksheet['id'] . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="col align-self-end">
                    <button class="btn btn-success" id="add-invoice">Add Invoice</button>
                </div>
            </div>
        </div>
    </div>

    <div class="row container--pt">
        <div class="col">
            <table class="table table-bordered table-striped" width="100%">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Amount Paying</th>
                        <th>Remove</th>
                    </tr>
                </thead>

                <tbody id="invoice_entries">

                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="1">Total</td>
                        <td align="right" colspan="1" id="inv-total">£0</td>
                        <td colspan="1"></td>
                    </tr>
                </tfoot>

                </tfoot>
            </table>
        </div>
    </div>
    <form id="payment_entry" method="POST" action="scripts/save_multi_invoice_payment_entry.php">
        <div class="row container--pt">
            <div class="col">
                <label for="payment_method">Payment Method</label>
                <select class="form-select" id="payment_method" name="payment_method">
                    <?php foreach (PAYMENT_METHODS as $paymentMethod) {
                        echo '<option value="' . $paymentMethod . '" >' . $paymentMethod . '</option>';
                    } ?>
                </select>
            </div>
            <div class="col">
                <label for="meta_data">Additional Notes</label>
                <input class="form-control" id="meta_data" name="meta_data" type="text" placeholder="Cheque No., Bank Transaction No." />
            </div>

        </div>
        <div class="row">
            <div class="col">
                <p class="error_info"></p>
            </div>
            <div class="col d-flex justify-content-end">
                <input type="hidden" id="payment_data" name="payment_data" value="" />
                <input type="hidden" name="customer_id" value="<?php echo $customerID; ?>" />
                <input class="btn btn-success" type="button" onclick="mainForm()" value="SUBMIT" />
            </div>
        </div>
    </form>
</div>

<div class="clearfix"></div>
<script type="text/javascript">
    function mainForm(){
        genereatePaymentData();
        if (validateForm()) {$('#payment_entry').ajaxSubmit({headers:{'X-CSRF-TOKEN': "<?php echo csrf_token();?>"},success:mainFormSucess});}
}
function mainFormSucess(){
	location.reload();
}
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

    .invoice-amount {
        text-align: right;
    }
</style>

<script>
    var totalSelectedInvoiceAmount = 0.0;

    $(document).ready(function() {

        $('#invoices').select2();

        $('#add-invoice').click(function() {
            var invoice_id = $("#invoices option:selected").text();
            var amount = $('#' + invoice_id).data('tot-val').toFixed(2);
            $('#' + invoice_id).prop('disabled', !$('#' + invoice_id).prop('disabled'));
            $('#invoices').select2().val("0").trigger("change");
            $('#invoice_entries').append('<tr><td>' + invoice_id + '</td><td align="right"><input class="form-control invoice-amount" type="text" value="' + amount + '" data-inv="' + invoice_id + '"/></td><td align="center"><i data-inv="' + invoice_id + '" class="remove-inv fa fa-ban" aria-hidden="true"></i></td></tr>')
            calculateTotalPayment();
            $('.invoice-amount').numeric();
        });

        $('#invoice_entries').on('change', '.invoice-amount', function() {
            calculateTotalPayment();
        });

        $('#invoice_entries').on('click', '.remove-inv', function() {

            $('#' + $(this).data('inv')).prop('disabled', false);
            $(this).parent().parent().remove();
            calculateTotalPayment();
        });
    });

    function calculateTotalPayment() {
        var sum = 0;
        $('.invoice-amount').each(function() {
            sum += parseFloat(this.value);
        });
        totalSelectedInvoiceAmount = sum.toFixed(2);
        $('#inv-total').text('£' + totalSelectedInvoiceAmount);
    }

    function genereatePaymentData(){

        var payment_info_string = '';

        $('.invoice-amount').each(function() {
            payment_info_string += $(this).data('inv') + '|' + parseFloat(this.value).toFixed(2) + ',';
        });
        payment_info_string = payment_info_string.replace(/,\s*$/, "");
        $('#payment_data').val(payment_info_string);
    }

    function validateForm() {

        $('.error_info').text('');
        var isValid = true;
        var payment_data = $('#payment_data').val();


        if (payment_data == '') {
            isValid = false;
            $('.error_info').text('Please add one or more invoice payments');
        }

        return isValid;
    }

    function isNumber(n) {
        return !isNaN(parseFloat(n)) && isFinite(n) && n > 0;
    }
</script>
