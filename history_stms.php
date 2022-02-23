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
            <h1>History of Statements sent to <?php echo $customer['businessname'];?></h1>
        </div>        
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
                    <th align="left" >Recipent</th>
                    <th align="center" >Date Sent</th>
                    <th align="center" >Attachment</th>
                    <th align="center" >Last Update</th>
                    <th align="right" >Status</th>
                    <th align="right" ></th>
                </tr>
            </thead>
            <tbody id="dataResults">

            </tbody>
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
var invoiceCount = 0;
var crCount = -1;                 
var renderCompleted = false;

    var customer_id = <?php echo $_GET['id']; ?>;

    $(document).ready(function() {
        table = $('#soaTable').DataTable({
            "pageLength": -1,
            "order": [[ 0, "ASC" ]],
            
        });
        getData();
        //$("#printer").hide();
    });
    function getData() {
        $.post("/ajax/customer_soah_renderer.php", {
                customer_id: customer_id
            },
            getDataResp);
    }
    function getDataResp(data, status) {
        console.log(data);
        $('#soaTable').DataTable().destroy();
        $("#soaTable > tbody").empty();

        $('#soaTable tbody').append(data);
        table = $('#soaTable').DataTable({
            "aaSorting": [],
            "pageLength": -1,
            "columnDefs": [
                { "orderable": true, "targets": 0 },
                { "orderable": true, "targets": 1 },
                { "orderable": true, "targets": 2 },
                { "orderable": true, "targets": 3 },
                { "orderable": true, "targets": 4 }
            ]
        }).draw();
        
    }

    function logResponse(data, status){
        console.log(status);
        console.log(data);
        $('.loadingContainer').hide();
    }

</script>
</script>
