<?php
include_once('includes/frontHeader.php');
include_once('functions.php');
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
        rolsor: pointer;
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

<div class="row custom-warning-box" id="warning" style="width: 100%; display: none"></div>
<div class="mainstatement">
        <div style="width: 300px;float: right;">
            <table>
                <tr><td style="text-align: right;">Start Date:</td><td><input class="form-control" type="text" class="inputbox" id="startdate" name="startdate" placeholder="" autocomplete="off"/></td></tr>
                <tr><td style="text-align: right;">or Invoice:</td><td><input class="form-control" type="text" class="inputbox" id="startinvoice" name="startinvoice" placeholder=""/></td></tr>
                <tr><td style="text-align: right;">End Date:</td><td><input class="form-control" type="text" class="inputbox" id="enddate" name="enddate" placeholder="" autocomplete="off"/></td></tr>
                <tr><td style="text-align: right;">or Invoice:</td><td><input class="form-control" type="text" class="inputbox" id="endinvoice" name="endinvoice" placeholder=""/></td></tr>
                <tr><td style="text-align: right;"></td><td style="float: right;" width="55"><input type ="button" style="width: 55px;"  onclick= "fetchResults()" value = "Go"></td></tr>
            </table>
        </div>
        <div>
            <div style="padding-top:195px;padding-left:5px;"><input type ="button" id="print" name="print" style="width: 110px;"  onclick= "print()" value = "Print" disabled></div>
        </div>
        <table id="soaTable" class="table" width="100%" style="font-size:10pt;border-spacing: 0;border-color: grey;">
            <thead>
                <tr class="heading">
                    <th width="5%" align="left">Invoice ID</th>
                    <th width="5%" align="left">Status</th>
                    <th width="25%" align="left">Customer Name</th>
                    <th width="5%" align="center">Customer ID</th>
                    <th width="5%" align="center">Sage No</th>
                    <th width="25%" align="right">Asm Date</th>
                    <th width="25%" align="center">Del Date</th>
                    <th width="25%" align="right">Total</th>
                    <th width="5%" align="right"></th>
                </tr>
            </thead>
            <tbody>
                <tr><td></td><td></td><td></td><td></td><td>Set your desired Date range than press go.</td><td></td><td></td><td></td><td></td></tr>
            </tbody>
            <tfoot class="last">
                <tr>
                    <th width="10%" align="left"></th>
                    <th width="14%" align="left"></th>
                    <th width="14%" align="left"></th>
                    <th width="14%" align="center"></th>
                    <th width="14%" align="center"></th>
                    <th width="14%" align="right"></th>
                    <th width="14%" align="right"></th>
                    <th width="14%" align="right"></th>
                    <th width="10%" align="right"></th>
                </tr>
            </tfoot>
            <tbody id="dataResults">

            </tbody>
        </table>
        </div>
</div>

<div class="clearfix"></div>
<script type="text/javascript">
var startInv;
var start;
var end;
var endInv;
var d;
var selectedID;
$.ajaxSetup({
		headers: { 'X-CSRF-TOKEN': "<?php echo csrf_token();?>" }
	});
$(document).ready(function(){
    $("#startdate").datepicker({
        dateFormat: 'yy-mm-dd'
    });
    $("#enddate").datepicker({
        dateFormat: 'yy-mm-dd'
    });
});

function fetchResults(){
    $('#soaTable').DataTable().destroy();
    $("#soaTable > tbody").empty();

    startInv = $("#startinvoice").val();
    start = $("#startdate").val();
    end = $("#enddate").val();
    endInv = $("#endinvoice").val();
    if ((end == null || end == "") && (endInv == null || endInv == ""))
    {
        $("#endinvoice").css({"border-color":"#FF0000"});
        $("#enddate").css({"border-color":"#FF0000"});
        $('#warning').css('background', "#ff8266");
        $('#warning').css('border', "2px solid #ff0000");
        $('#warning').css('display', "inline-block");
        $('#warning').html("Please select an End Date or Invoice Number");
        return;
    }
    else
    {
        $("#enddate").css({"border-color":"#bfbfbf"});
        $("#endinvoice").css({"border-color":"#bfbfbf"});
    }
    if ((start == null || start == '') && (startInv == null || startInv == ""))
    {
        $("#startinvoice").css({"border-color":"#ff0000"});
        $("#startdate").css({"border-color":"#ff0000"});
        $('#warning').css('background', "#ff8266");
        $('#warning').css('border', "2px solid #ff0000");
        $('#warning').css('display', "inline-block");
        $('#warning').html("Please select a Start Date or Invoice Number");
        return;
    }
    else
    {
        $("#startinvoice").css({"border-color":"#bfbfbf"});
        $("#startdate").css({"border-color":"#bfbfbf"});
    }
    $("#soaTable").append("<tr><td></td><td>Loading Please Wait...</td><td></td></tr>");
    $.post("ajax/generateInvoicePaymentCheck.php", { 'start':start, 'startInv':startInv, 'end':end, 'endInv':endInv, 'previous_id':selectedID }, results);
}
var printArray = null;
function results(data, status){
    printArray = JSON.parse(data);
    $("#soaTable > tbody").empty();
    $("#soaTable").append(printArray.pop());

    $( "#print" ).prop( "disabled", false );
}
function ticked(e)
{
    var ticked = $('#img-mail-selector-'+e);
    var stater;
    if (ticked.is(":visible"))
    {
        ticked.hide();
        stater = 0;
    }
    else
    {
        ticked.show();
        stater = 1;
    }
    $.post("ajax/setInvoicePaymentCheck.php", { 'id':e, 'state':stater }, debug);
}
function print()
{
    $( "#print" ).prop( "disabled", true );
    $( "#print" ).prop( "value", "Downloading..." );
    $.post("ajax/generateInvoicePaymentPrintout.php", { 'data':printArray.toString() }, download);
}
function debug(data, status)
{
    console.log(data);
}
function download (data1) {
    $( "#print" ).prop( "value", "Print" );
    $.ajax({
        url: data1,
        method: 'GET',
        xhrFields: {
            responseType: 'blob'
        },
        success: function (data) {
            var a = document.createElement('a');
            var url = window.URL.createObjectURL(data);
            a.href = url;
            a.download = data1.replace("PDF/","");
            document.body.append(a);
            a.click();
            a.remove();
            window.URL.revokeObjectURL(url);
        }
    });
}
</script>
