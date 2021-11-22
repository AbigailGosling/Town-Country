<?php
include('includes/frontHeader.php');
?>
<div id="top" class="printhide">
    <a href="menu.php" id="menu">MENU</a>
    <a href="logout.php" id="logout">LOGOUT</a>
</div>
<div class="search printhide">
    <div class="container flex space-between" style="align-items:center">
        <a href="javascript: window.history.back();" class="back">
            < BACK</a>
                <div class="daterange">
                    <form method="GET">
                        <input type="hidden" name="id" value="<?php echo $_GET['id']; ?>">
                        From:
                        <input type="date" name="date_from" class="datePicker">
                        To:
                        <input type="date" name="date_to" class="datePicker">

                        <input type="submit" value="Search" class="searchbtn">
                </div>
    </div>
</div>
<div class="container">
    <?php
    if ($_GET['id'] != '') {

        $customer = getCustomer($_GET['id']);
    ?>
        <h2>Statement of account for <?php echo $customer['businessname']; ?>
            <?php
            $days = averageDaysUntilPaidForCustomer($customer['id']);

            if ($days != null) {
            ?><h2 style="font-size:18px;">Average days outstanding: <?php echo $days; ?> days</h2><?php
                                                                                            }
                                                                                                ?>
            <?php
            if ($_GET['date_from'] != '' && $_GET['date_to'] != '') {

                $date = str_replace('/', '-', $_GET['date_from']);
                $date_from = date('d/m/Y', strtotime($date));

                $date = str_replace('/', '-', $_GET['date_to']);
                $date_to = date('d/m/Y', strtotime($date));

                echo '(' . $date_from . ' - ' . $date_to . ')';
            }


            ?>
        </h2>
        <a class="mp" href="/multi_invoice_payments.php?customer_id=<?php echo $_GET['id']; ?>">Make / View payments</a>
        <div class="loadingContainer">
            <img src="img/loading.gif" alt="">
        </div>
        <table id="soaTable" class="table" width="100%">
            <thead>
                <tr class="heading">
                    <th align="left" class="sticky-header">Invoice ID</th>
                    <th align="left" class="sticky-header">Add Payment</th>
                    <th align="left" data-orderable="false" class="sticky-header">Due Date <div class="ingrid"><i class="fa fa-sort-asc" aria-hidden="true" data-column="2"></i><i class="fa fa-sort-desc" aria-hidden="true" data-column="2"></i></div></th>
                    <th align="left" data-orderable="false" class="sticky-header">Date <div class="ingrid"><i class="fa fa-sort-asc" aria-hidden="true" data-column="3"></i><i class="fa fa-sort-desc" aria-hidden="true" data-column="3"></i></div></th>
                    <th align="right" class="sticky-header">Value</th>
                    <th align="right" class="sticky-header">Paid</th>
                    <th align="right" class="sticky-header">Credit</th>
                    <th align="right" class="sticky-header">Outstanding</th>
                </tr>
            </thead>
            <tbody id="dataResults">

            </tbody>
            <tfoot class="last">
                <tr>
                    <th align="right" class="sticky-footer"></th>
                    <th align="right" class="sticky-footer"></th>
                    <th align="right" class="sticky-footer"></th>
                    <th align="right" class="sticky-footer">Total:</th>
                    <th align="right" width="120" class="total_digit_value sticky-footer"></th>
                    <th align="right" width="120" class="total_digit_paid sticky-footer"></th>
                    <th align="right" style="color:red;" width="120" class="total_digit_credit sticky-footer"></th>
                    <th align="right" width="120" class="total_digit_outstanding sticky-footer"></th>
                </tr>
            </tfoot>
        </table>
    <?php
    }
    ?>
</div>

<div class="clearfix"></div>
<script type="text/javascript">
    var toSkip = 0;
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

        $('#soaTable thead .fa-sort-asc').on('click', function() {
            $('.loadingContainer').show();
            column = $(this).data('column');
            order = 'asc';
            getData();
        });

        $('#soaTable thead .fa-sort-desc').on('click', function() {
            $('.loadingContainer').show();
            column = $(this).data('column');
            order = 'desc';
            getData();
        });

        

    });

    function getData() {
        $.post("/ajax/customer_soa_results.php", {
                customer_id: customer_id,
                date_from: date_from,
                date_to: date_to
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
        $.post("ajax/customer_soa_row_renderer.php", {
                picksheet: JSON.stringify(dataParsed),
                customer_id: customer_id,
                showAll: showAll?1:0
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
                { "orderable": false, "targets": 2 },
                { "orderable": false, "targets": 3 },
                { "orderable": true, "targets": 4 },
                { "orderable": true, "targets": 5 },
                { "orderable": true, "targets": 6 },
                { "orderable": true, "targets": 7 }
            ]
        }).draw();

        $('.loadMore').show();
        $('.loadingContainer').hide();

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

    }
    function beforePrint() {
        $('.printhide').hide();
        $('.container').css('width', '100%');
    }

    function printCompleted() {
        $('.printhide').show();
        $('.container').css('width', '1024px');
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