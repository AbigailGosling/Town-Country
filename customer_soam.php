<?php
include_once('includes/frontHeader.php');
$serverRoot = $_SERVER["SERVER_NAME"];
?>
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
        <img class="logo" style="width: 330px;" src="data:image/jpeg;base64,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"><br/>
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

function isNumber(n) {
  return !isNaN(parseFloat(n)) && isFinite(n);
}

    var toSkip = 0;
    var due_days = "<?php echo $customer['credit_terms']?>";
    due_days=due_days.replace(/\D/g,'');

    if(!isNumber(due_days)){
        due_days =0;
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
                showAll: "Y"
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
                customer_id: customer_id,
                showAll: showAll?1:0,
                adv: 1
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
    var invoiceCount = -1;
    function getInvoices() {
        $('.loadingContainer').show();
        invoiceCount++;
        if (invoiceCount < dataParsed.length)
        {
            $.get("invoice.php", {
                id: dataParsed[invoiceCount].id,
                adv: "Y"
            },
            getInvoicesResp);
        }
        else
        { 
            $('.loadingContainer').hide();
            $('.noprint').show();
            console.log("Finished Rendering");
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
    function logResponse(response){
        $('.loadingContainer').hide();
    }
    //Function that generates a PDF of the invoice using MPDF and stores it in '/PDF/Statement_{ID}_{Datestamp}.pdf'
    $('#generatepdf').click(function() {
        alert("This function will automatically send an email to the Client with all of the attached invoices on this page via SocketLabs once it is set up!");
        /*
        $('.loadingContainer').show();
        var myInvoice = document.getElementsByClassName("printme");
        var HTMLRender = [];
        console.log(document.getElementsByClassName("topInvoice")[0].innerHTML);
        //Push the total/top of the invoice first
        HTMLRender.push(document.getElementsByClassName("topInvoice")[0].innerHTML);
        for(var i=0; i<myInvoice.length; i++){
            //Send trimmed (for speed purposes) InnerHTML to generatePDFstatement.php 
            HTMLRender.push(myInvoice[i].innerHTML.replace(/^\s+|\s+$/gm,''));
        }
        $.post("ajax/generatePDFstatement.php", {web: JSON.stringify(HTMLRender), id: customer_id},logResponse);
        */
    });

</script>
</script>
