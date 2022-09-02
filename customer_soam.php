<?php
include_once('includes/frontHeader.php');
$serverRoot = $_SERVER["SERVER_NAME"];
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
.topInvoice{
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
    <a href="logout.php" id="logout">LOGOUT</a>
</div>


<div id="printDiv" class="container" style=""> 
    
    <?php
    if ($_GET['id'] != '') {

        $customer = getCustomer($_GET['id']);
    ?>
    <div class="topheading">

    <div class="topInvoice">
    <div class="headerinfo">
        <div class="logocontainer" style="text-align: center; line-height: 13px; font-size: 10px; padding-top:10px;">
        <img class="logo" style="width: 330px;" src="<?php echo $domain ?>images/tandclogo.jpg"><br/>
            13-17 Landport Ind. Est. Landport Road<br/>
            Wolverhampton WV2 2QJ<br/>
            <span>Vat. No: 701 075 285</span><br/>
            <span>Company Reg. No. 12192223</span><br/>
            <b>01902 457924</b><br/>
        </div>
		
		<div class="invoice">
			 
			<b style="font-size:10px;color:#8c8c8c;">Invoice address</b>
			<div class="invoicebox">
				<p>
					<?php echo $customer['businessname']; ?><br/>
					t/a <?php echo $customer['tradingas']; ?><br/>
					<?php echo $customer['accounts_address_1']; ?><br/>
					<?php echo $customer['accounts_address_2']; ?><br/>
					<?php echo $customer['accounts_address_3']; ?><br/>
                    <?php echo $customer['accounts_address_4']; ?><br/>
                    Customer ID: <?php echo str_pad($customer['id'], 4, '0', STR_PAD_LEFT); ?><br/>
                    <?php echo $customer['customer_email']; ?><br/>
				</p>
				<span style="display:none;">Account No: 1123ml</span>
			</div>
		</div>
            <div class="container">
                <table class="printemailbuttons" style="">
                    <tr>
                    <td>
                    <div id="noprint" class="noprint" style="height: 30px !important;">
 		            <a href="#" onclick="window.print();">Print</a>
 	                </div>
                    </td>
                    <td>
                    <div id="generatepdf" class="noprint" style="width: 150px; height: 30px !important;">
 		            <a href="#">Email as PDF</a>
 	                </div>
                    </td>
                </tr>
            </table>
            </div>

        <h4><?php echo $customer['businessname'];?> (ID: <?php echo $customer['id'];?>)<br>
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
                    <th align="left" >Invoice ID</th>
                    <th align="left" data-orderable="false" >Assembly Date</th>
                    <th align="left" data-orderable="false" >Due Date</th>
                    <th align="right" >Value</th>
                    <th align="right" >Paid</th>
                    <th align="right" >Credit</th>
                    <th align="right" >Outstanding</th>
                </tr>
            </thead>
            <tfoot class="last">
                <tr>
                    <th align="right"></th>
                    <th align="right"></th>
                    <th align="right">Total:</th>
                    <th align="right" width="120" class="total_digit_value"></th>
                    <th align="right" width="120" class="total_digit_paid"></th>
                    <th align="right" style="color:red;" width="120" class="total_digit_credit"></th>
                    <th align="right" width="120" class="total_digit_outstanding"></th>
                </tr>
            </tfoot>
            <tbody id="dataResults">

            </tbody>
        </table>
        </div>
        </div>
        <div style="" id="invoiceZone" class="myInvoice">
        </div>
    
    </div>
    <?php
    }
    ?>
</div>

<div class="clearfix"></div>
<script type="text/javascript">
var invoiceCount = 0;
var crCount = -1;                 
var renderCompleted = false;
function isNumber(n) {
  return !isNaN(parseFloat(n)) && isFinite(n);
}

    var toSkip = 0;
    var due_days = "<?php echo $customer['credit_terms']?>";
    var due_warning = "<?php echo $customer['due_warning']?>";
    due_days=due_days.replace(/\D/g,'');
    due_warning=due_warning.replace(/\D/g,'');

    if(!isNumber(due_days)){
        due_days =0;
    }
    if(!isNumber(due_warning)){
        due_warning = 0;
    }
    var customer_id = <?php echo $_GET['id']; ?>;
    var date_from = '<?php echo $_GET['date_from']; ?>';
    var date_to = '<?php echo $_GET['date_to']; ?>';
    var table = null;
    var column = 3;
    var order = 'DESC';

    $(document).ready(function() {
        table = $('#soaTable').DataTable({
            "pageLength": -1,
            "order": [[ 0, "ASC" ]],
            
        });
        getData();
        //$("#printer").hide();
    });
    function getData() {
        $.post("/ajax/customer_soa_results.php", {
                customer_id: customer_id,
                date_from: date_from,
                date_to: date_to,
                adv:"Y"
            },
            getDataResp);
    }
    var dataParsed = null;
    var showAll = false;
    function getDataResp(data, status) {

        $('#soaTable').DataTable().destroy();
        $("#soaTable > tbody").empty();
        dataParsed = JSON.parse(data);
        getRender();
    }
    function getRender(){
        $.post("ajax/customer_soam_row_renderer.php", {
                picksheet: JSON.stringify(dataParsed),
                duedays: due_days,
                due_warning: due_warning,
                customer_id: customer_id,
                showAll: showAll?"Y":"N"
            },
            getRenderResp);
    }
    function getRenderResp(data, status){
        $('#soaTable tbody').append(data);
        table = $('#soaTable').DataTable({
            "aaSorting": [],
            "pageLength": -1,
            "columnDefs": [
                { "orderable": true, "targets": 0 },
                { "orderable": true, "targets": 1 },
                { "orderable": true, "targets": 2 },
                { "orderable": true, "targets": 3 },
                { "orderable": true, "targets": 4 },
                { "orderable": true, "targets": 5 }
            ]
        }).draw();
        
        let nf = new Intl.NumberFormat('en-GB',{ style: 'currency', currency: 'GBP'});

        var total_digit_value = 0;
        var total_digit_paid = 0;
        var total_digit_credit = 0;
        var total_digit_outstanding = 0;

        //  Total Value Column
        $('.digit_value').each(function(index) {
            total_digit_value += parseFloat($(this).attr('value'));
        });

        total_digit_value = nf.format(total_digit_value);
        $('.total_digit_value').text(total_digit_value);


        //  Total Paid Column
        $('.digit_paid').each(function(index) {
            total_digit_paid += parseFloat($(this).attr('value'));
        });

        total_digit_paid = nf.format(total_digit_paid);
        $('.total_digit_paid').text(total_digit_paid);

        //  Total Credit Column
        $('.digit_credit').each(function(index) {
            total_digit_credit += parseFloat($(this).attr('value'));
        });

        total_digit_credit = nf.format(total_digit_credit);
        $('.total_digit_credit').text(total_digit_credit);


        //  Total Outstanding Column
        $('.digit_outstanding').each(function(index) {
            total_digit_outstanding += parseFloat($(this).attr('value'));
        });
        
        total_digit_outstanding = nf.format(total_digit_outstanding);
        $('.total_digit_outstanding').text(total_digit_outstanding);
        
        getInvoices();
    }
    function renderComplete(){
        return renderCompleted;
    }
    function getInvoices() {
        $('.loadingContainer').show();
        if (crCount == -1 && invoiceCount < dataParsed.length)
        {
            crCount = 0;
            $.get("invoice.php", {
                id: dataParsed[invoiceCount].id,
                adv: "Y"
            },
            getInvoicesResp);
            return;
        }
        else if (dataParsed[invoiceCount] && dataParsed[invoiceCount].hasOwnProperty("creditNotes") && dataParsed[invoiceCount].creditNotes.length > crCount)
        {

            $.get("/ajax/generatePDFcreditnote.php", {
                payment_id: dataParsed[invoiceCount].creditNotes[crCount].id,
                id: dataParsed[invoiceCount].creditNotes[crCount].invoice_id,
                adv:"Y",
                count:(invoiceCount+crCount)
            },
            getInvoicesResp);
            crCount++;
            return;
        }      
        else
        {
            
            invoiceCount++;
            crCount = -1;
            if (invoiceCount < dataParsed.length)
            {
                getInvoices();
            }
            else
            {
                $('.loadingContainer').hide();
                $('.noprint').show();
                renderCompleted = true;
            }
        }
        
    }
    function getInvoicesResp(data, status) {
        $('#invoiceZone').append(data);
        getInvoices();
    }
    function beforePrint() {
        //$(".printer").hide();
        $('.printhide').hide();
        $('.container').css('width', '100%');
    }

    function printCompleted() {
        //$(".printer").show();
        $('.printhide').show();
        $('.container').css('width', '1024px');
    }
    function applySort(){
        dataParsed = dataParsed.sort(function s(a,b){
            var columnName = '';
            if (column == 2) columnName = 'sortableDueDateFormat';
            else columnName = 'sortableDateFormat';
            
            var sortDirection = -1;           
            if (order == "asc") sortDirection = 1;

            return  b[columnName] < a[columnName] ? sortDirection
                :   b[columnName] > a[columnName] ? (sortDirection / -1)
                :   0;

        });

        $('#soaTable').DataTable().destroy();
        $("#soaTable > tbody").empty();
        getRender();
    }
    function logResponse(data, status){
        console.log(status);
        console.log(data);
        $('.loadingContainer').hide();
    }
    //Function that generates a PDF of the invoice using MPDF and stores it in '/PDF/Statement_{ID}_{Datestamp}.pdf'
    $('#generatepdf').click(function() {
        
        $('.loadingContainer').show();
        $.post("ajax/generatePDFstatement.php", {id: customer_id},logResponse);
        
    });

</script>
</script>
