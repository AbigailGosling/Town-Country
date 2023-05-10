<?php
include_once('includes/frontHeader.php');

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

<div class="logocontainer" style="text-align: center; line-height: 13px; font-size: 10px; padding-top:10px;">
        </div>
        <div>
    <table id="hisTable" class="table" width="100%" style="font-size:10pt;border-spacing: 0;border-color: grey;">
        <thead>     
            <tr class="heading">
                <td width="10%" align="left">Created</td>
                <td width="20%" colspan="2" align="center">Date Range</td>
                <td width="05%" align="center">Invoice Range</td>
                <td width="05%" align="center"></td>
                <td width="10%" align="left">Sales Value</td>
                <td width="10%" align="left">Payment Value</td>
                <td width="1%" align="left"></td>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
    </div>
        <div style="width: 350px;float: right;">
            <table>
                <tr><td style="text-align: right;">Start Date:</td><td><input class="form-control" type="text" class="inputbox" id="startdate" name="startdate" placeholder=""/></td></tr>
                <tr><td style="text-align: right;">or Invoice:</td><td><input class="form-control" type="number" class="inputbox" id="startinvoice" name="startinvoice" placeholder=""/></td></tr>
                <tr><td style="text-align: right;">End Date:</td><td><input class="form-control" type="text" class="inputbox" id="enddate" name="enddate" placeholder=""/></td></tr>
                <tr><td style="text-align: right;">or Invoice:</td><td><input class="form-control" type="number" class="inputbox" id="endinvoice" name="endinvoice" placeholder=""/></td></tr>
                <tr><td style="text-align: right;">Previous £:</td><td><input class="form-control" type="number" step=".01" class="inputbox" id="overridevalue" name="overridevalue" placeholder=""/></td></tr>
                <tr><td style="text-align: right;"></td><td style="float: right;" width="55"><input type="button" style="width: 55px;"  onclick="fetchResults()" value="Go"></td></tr> 
            </table>
        </div>
        <div class="row custom-warning-box" id="warning" style="width: 100%; display: none"></div>
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
        <input style="width: 55px;" type ="button" onclick = "save()" value = "Save"/>
        </div>
</div>

<div class="clearfix"></div>
<script type="text/javascript">
    $.ajaxSetup({
		headers: { 'X-CSRF-TOKEN': "<?php echo csrf_token();?>" }
	});
$(document).ready(function(){  
    $("#startdate").datepicker({
        dateFormat: 'dd/mm/yy'
    });
    $("#enddate").datepicker({
        dateFormat: 'dd/mm/yy'
    });
    $.post("ajax/loadHistoricFinancialReport.php", {}, hist_results);
    /*
    $("#starttime").timepicker({
        dateFormat: 'dd/mm/yy'
    });
    $("#endtime").timepicker({
        dateFormat: 'dd/mm/yy'
    });*/
});
var startInv;
var start;
var end;
var endInv;
var previousValue;
var d;
var selectedID;
function selectHist(id){
    if (selectedID != null)
    {
        $("#hist_"+selectedID).css("background-color", "");
    }
    if (id != selectedID)
    {
        selectedID = id;
        $("#hist_"+selectedID).css("background-color", "#afeeee");
        $("#startinvoice").val(parseInt($("#hist_"+selectedID+"_end_invoice_id").html())+1);
        $("#startdate").val("");
        $("#enddate").val("");
        var lSaleVal = parseFloat($("#hist_"+selectedID+"_sales").html().substring(1)).toFixed(2);
        var lPayVal = parseFloat($("#hist_"+selectedID+"_payments").html().substring(1)).toFixed(2);
        $("#overridevalue").val(parseFloat(lSaleVal - lPayVal).toFixed(2));
    }
    else 
    {
        $("#startinvoice").val('');
        $("#overridevalue").val('');
        selectedID = null;
    }
    
}
function hist_results(data,status){
    $("#hisTable > tbody").empty();
    $("#hisTable > tbody").html(data);
}
function deleteHist(id){
    $.post("ajax/deleteHistoricFinancialReport.php", {id:id}, del_results);
}
function del_results(data,status){
    $("#hisTable > tbody").empty();
    $.post("ajax/loadHistoricFinancialReport.php", {}, hist_results);
}
function fetchResults(){
    $('#soaTable').DataTable().destroy();
    $("#soaTable > tbody").empty();
    
    startInv = $("#startinvoice").val();
    start = $("#startdate").val();
    end = $("#enddate").val();
    endInv = $("#endinvoice").val();
    previousValue = $("#overridevalue").val();
    if (previousValue != null && previousValue != "")
    {
        previousValue = parseFloat(previousValue).toFixed(2);
    }
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
    $.post("ajax/generateFinancialReport.php", { 'start':start, 'startInv':startInv, 'end':end, 'endInv':endInv, 'previous_id':selectedID , 'previous_value':previousValue}, results);
}
function results(data, status){
    $("#soaTable > tbody").empty();
    d = JSON.parse(data);

    if (d.abort_id > 0)
    {
        
        $('#warning').css('background', "#ffc266");
        $('#warning').css('border', "2px solid #ff9900");
        $('#warning').css('display', "inline-block");
        $('#warning').html("Invoice "+d.abort_id+" is not picked! Processing stopped!");
    }
    else
    {
        $('#warning').css('display', "none");
    }

    $("#soaTable > thead").css('display', "none");
    $("#soaTable").append("<tr style=\"background: #e2e2e2;\"><td>Processed Invoices</td><td></td><td></td></tr>");
    if (d.hasOwnProperty("previous") && d.previous != null)
    {
        $("#soaTable").append("<tr><td>Previous Range:</td><td></td><td align=\"right\">"+d.previous.start_invoice_id+" - "+d.previous.end_invoice_id+"</td></tr>");
    }
    else
    {
        $("#soaTable").append("<tr><td></td><td></td><td align=\"right\"></td></tr>");
    }
 
    $("#soaTable").append("<tr><td>This Range:</td><td></td><td align=\"right\">"+d.start_invoice_id+" - "+d.end_invoice_id+"</td></tr>");
    $("#soaTable").append("<tr style=\"background: #e2e2e2;\"><td></td><td></td><td></td></tr>");
    if (d.hasOwnProperty("previous") && d.previous != null && d.previous.hasOwnProperty('rolTotal')) 
    {
        $("#soaTable").append("<tr><td>Total \"Previous\" Outstanding</td><td></td><td align=\"right\">£"+Number(d.previous.rolTotal).toLocaleString('en', {minimumFractionDigits: 2})+"</td></tr>");
    }
    else 
    {
        $("#soaTable").append("<tr><td>Total \"Previous\" Outstanding</td><td></td><td align=\"right\">£"+(0).toLocaleString('en', {minimumFractionDigits: 2})+"</td></tr>");
    }
    $("#soaTable").append("<tr><td>Total Sales Value \"This Period\"</td><td></td><td align=\"right\">£"+d.sales.toLocaleString('en', {minimumFractionDigits: 2})+"</td></tr>");
    if (d.hasOwnProperty("previous") && d.previous != null && d.previous.hasOwnProperty('rolTotal')) 
    {
        $("#soaTable").append("<tr style=\"background: #e2e2e2;\"><td>Sub-total: </td><td></td><td align=\"right\">£"+(Number(d.sales)+Number(d.previous.rolTotal)).toLocaleString('en', {minimumFractionDigits: 2})+"</td></tr>");
    }
    else
    {
        $("#soaTable").append("<tr style=\"background: #e2e2e2;\"><td>Sub-total: </td><td></td><td align=\"right\">£"+(d.sales).toLocaleString('en', {minimumFractionDigits: 2})+"</td></tr>");
    }
    $("#soaTable").append("<tr><td></td><td></td><td></td></tr>");
    $("#soaTable").append("<tr><td>Total Payments this Period: </td><td></td><td align=\"right\">£"+(d.payments).toLocaleString('en', {minimumFractionDigits: 2})+"</td></tr>");
    $("#soaTable").append("<tr style=\"background: #e2e2e2;\"><td>Total: </td><td></td><td align=\"right\">£"+d.rolTotal.toLocaleString('en', {minimumFractionDigits: 2})+"</td></tr>");
}
function save()
{
    $('#warning').css('display', "none");
    $.post("ajax/saveFinancialReport.php", {'input': d},debug);
}
function debug(data, status)
{
    $.post("ajax/loadHistoricFinancialReport.php", {}, hist_results);
    $('#warning').css('background', "#CFFDBC");
    $('#warning').css('border', "2px solid #6EFA32");
    $('#warning').css('display', "inline-block");
    $('#warning').html("Saved!");
}
</script>