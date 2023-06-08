<?php
include_once('includes/frontHeader.php');
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
    <a href="logout" id="logout">LOGOUT</a>
</div>


<div id="printDiv" class="container" style=""> 
    
 
    <div class="topheading">

    <div class="topInvoice">
    <div class="headerinfo">

        <div style="" id="invoiceZone" class="myInvoice">
        </div>
    
    </div>
</div>

<div class="clearfix"></div>
<script type="text/javascript">           
var renderCompleted = false;
$.ajaxSetup({
		headers: { 'X-CSRF-TOKEN': "<?php echo csrf_token();?>" }
	});
function isNumber(n) {
  return !isNaN(parseFloat(n)) && isFinite(n);
}
var dataParsed = <?php echo json_encode(explode(",",request()->input('data'))); ?>;
    $(document).ready(function() {
        table = $('#soaTable').DataTable({
            "pageLength": -1,
            "order": [[ 0, "ASC" ]],
            
        });
        getInvoices();
        //$("#printer").hide();
    });
    function renderComplete(){
        return renderCompleted;
    }
    function getInvoices() {
        if (dataParsed.length> 0)
        {
            var id = dataParsed.shift();
            var t = dataParsed.shift();
            var url = "";
            if (t == "i") url = "invoice.php";
            else url = "viewSalesconfirmation.php"
            crCount = 0;
            $.get(url, {
                id: id,
                adv: "Y",
                or: 1
            },
            getInvoicesResp);
            return;
        }     
        else
        $('.loadingContainer').hide();
        $('.noprint').show();
        renderCompleted = true;
        
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
</script>
</script>
