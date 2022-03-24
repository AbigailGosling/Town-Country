<?php
include('includes/frontHeader.php');
?>
<div id="top" class="printhide">
    <a href="menu.php" id="menu">MENU</a>
    <a href="logout.php" id="logout">LOGOUT</a>
</div>
<div class="search printhide">
    <div class="container flex space-between" style="align-items:center; width:90%">
        <a href="javascript: window.history.back();" class="back">
            < BACK</a>
    </div>
</div>
<div class="container" style="width:90%">
        <h2>Payment History</h2>
        <div class="loadingContainer">
            <img src="img/loading.gif" alt="">
        </div>
        <table id="soaTable" class="table" width="100%">
            <thead>
                <tr class="heading">
                    <th style="width:8%" class="sticky-header">Invoice ID</th>
                    <th style="width:20%" class="sticky-header">Customer</th>
                    <th style="width:10%" class="sticky-header">Method</th>
                    <th style="width:10%" class="sticky-header">Amount</th>
                    <th class="sticky-header">Notes</th>
                    <th style="width:10%" class="sticky-header">Recorded By</th>
                    <th style="width:10%" class="sticky-header">Date</th>
                </tr>
            </thead>
            <tbody id="dataResults">

            </tbody>
            <tfoot class="last">
                <tr>
                    <th class="sticky-footer"></th>
                    <th class="sticky-footer"></th>
                    <th class="sticky-footer"></th>
                    <th class="sticky-footer"></th>
                    <th class="sticky-footer"></th>
                    <th class="sticky-footer"></th>
                    <th class="sticky-footer"></th>
                </tr>
            </tfoot>
        </table>
</div>

<div class="clearfix"></div>
<script type="text/javascript">

    $(document).ready(function() {
        table = $('#soaTable').DataTable({
            "pageLength": -1,
            "order": [[ 0, "ASC" ]],
            
        });
        getData();
    });

    function getData(){
        $.post("ajax/payhist.php", {
                },
            getRenderResp);
    }
    function getRenderResp(data, status){
        $('#soaTable').DataTable().destroy();
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
                { "orderable": true, "targets": 5 },
                { "orderable": true, "targets": 6 }
            ]
        }).draw();

        $('.loadMore').show();
        $('.loadingContainer').hide();

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


</script>

<style type="text/css">
    .loadingContainer {
        width: 100%;
        text-align: center;
        margin: 10px 0px;
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
</style>