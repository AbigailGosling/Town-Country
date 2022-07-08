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
<div class="logocontainer" style="text-align: center; line-height: 13px; font-size: 10px; padding-top:10px;">
        </div>
        <div style="width: 300px;float: right;">
            <table>
                <tr><td>Start Date:</td><td><input class="form-control" type="text" class="inputbox" id="startdate" name="startdate" placeholder=""/></td></tr>
                <tr><td>End Date:</td><td><input class="form-control" type="text" class="inputbox" id="enddate" name="enddate" placeholder=""/></td></tr>
                <tr><td></td><td style="float: right;"><input type ="button" onclick = "fetchResults()" value = "Go"></td></tr> 
            </table>
        </div>
        <!--<input class="form-control" type="text" class="inputbox" id="starttime" name="starttime" placeholder=""/>
        <input class="form-control" type="text" class="inputbox" id="enddtime" name="endtime" placeholder="">-->
<div class="mainstatement">
        <table id="soaTable" class="table" width="100%" style="font-size:10pt;border-spacing: 0;border-color: grey;">
            <thead>
                <tr class="heading">
                    <th width="33%" align="left"></th>
                    <th width="33%" align="left"></th>
                    <th width="33%" align="right"></th>
                </tr>
            </thead>
            <tbody>
                <tr><td></td><td>Set your desired Date range than press go.</td><td></td></tr>
            </tbody>
            <tfoot class="last">
                <tr>
                    <th align="left"></th>
                    <th align="left"></th>
                    <th align="right"></th>
                </tr>
            </tfoot>
            <tbody id="dataResults">

            </tbody>
        </table>
        <input type ="button" onclick = "save()" value = "Save"></td>
        </div>
</div>

<div class="clearfix"></div>
<script type="text/javascript">
$(document).ready(function(){  
    $( "#startdate" ).datepicker({
        dateFormat: 'yy-mm-dd'
    });
    $( "#enddate" ).datepicker({
        dateFormat: 'yy-mm-dd'
    });
    /*
    $( "#starttime" ).timepicker({
        dateFormat: 'dd/mm/yy'
    });
    $( "#endtime" ).timepicker({
        dateFormat: 'dd/mm/yy'
    });*/
});
var start;
var end;
var d;
function fetchResults(){
    $('#soaTable').DataTable().destroy();
    $("#soaTable > tbody").empty();
    start = $( "#startdate" ).val();
    end = $( "#enddate" ).val();
    $("#soaTable").append("<tr><td></td><td>Loading Please Wait...</td><td></td></tr>");
    $.post("/ajax/generateFinancialReport.php", {'start':start, 'end':end }, results);
}
function results(data, status){
    $('#soaTable').DataTable().destroy();
    $("#soaTable > tbody").empty();
    d = JSON.parse(data);
    $("#soaTable").append("<tr><td>Total \"Previous\" Outstanding</td><td></td><td align=\"right\">£"+d.totPrevOut.toLocaleString('en-US', {minimumFractionDigits: 2})+"</td></tr>");
    $("#soaTable").append("<tr><td>Total Sales Value \"This Period\"</td><td></td><td align=\"right\">£"+d.curSaleVal.toLocaleString('en-US', {minimumFractionDigits: 2})+"</td></tr>");
    $("#soaTable").append("<tr style=\"background: #e2e2e2;\"><td>Sub-total: </td><td></td><td align=\"right\">£"+(d.curSaleVal+d.totPrevOut).toLocaleString('en-US', {minimumFractionDigits: 2})+"</td></tr>");
    $("#soaTable").append("<tr><td></td><td></td><td></td></tr>");
    $("#soaTable").append("<tr><td>Total Payments this Period: </td><td></td><td align=\"right\">£"+(d.curPayment).toLocaleString('en-US', {minimumFractionDigits: 2})+"</td></tr>");
    $("#soaTable").append("<tr style=\"background: #e2e2e2;\"><td>Total: </td><td></td><td align=\"right\">£"+((d.curSaleVal+d.totPrevOut)-d.curPayment).toLocaleString('en-US', {minimumFractionDigits: 2})+"</td></tr>");
}
function save()
{
    var q = {'start':start, 'end':end, 'val': (d.curSaleVal+d.totPrevOut)-d.curPayment};
    console.log(q);
    $.post("/ajax/saveFinancialReport.php", {'start':start, 'end':end, 'input': (d.curSaleVal+d.totPrevOut)-d.curPayment},debug);
}
function debug(data, status)
{
    console.log(data);
    console.log(status);
}
</script>