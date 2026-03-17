<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

include('includes/frontHeader.php');
include_once('ajax/customer_soa_results_function.php');
?>
<div id="top" class="printhide">
    <a href="menu.php" id="menu">MENU</a>
    <a href="logout" id="logout">LOGOUT</a>
</div>
<div class="search printhide">
    <div class="container flex space-between" style="align-items:center">
        <a href="javascript: window.history.back();" class="back">
            < BACK</a>
    </div>
</div>
<div class="container">
    <?php
    if (request()->input('id') != '') {

        $customer = getCustomer(request()->input('id'));
        $creditCheck = precredit_check(request()->input('id'));
		$title = "";
		if ($creditCheck['saleAllowed'] == true)
		{
			if ($creditCheck['showWarning'] == true)
			{
				$bg = '#ffc266';
                $bor= '#ff9900';
			}
		}
		else
		{
            $bg = '#ff6666';
            $bor= '#ff0000';

		}
        if ($creditCheck['hideOnStmt'] == false && ($creditCheck['saleAllowed'] == false || $creditCheck['showWarning'] == true))
        {
    ?>
        <div class="row custom-warning-box" id="warning" style="background:<?php echo $bg;?>; border: 2px solid <?php echo $bor;?>">
		    <?php echo $creditCheck['messageLong']; ?>
	    </div>
    <?php
        }
    ?>
        <h2>Statement of account for <?php echo $customer['businessname']; ?>
            <?php
            $days = averageDaysUntilPaidForCustomer($customer['id']);

            if ($days != null) {
            ?><h2 style="font-size:18px;">Average days outstanding: <?php echo $days; ?> days</h2><?php
                                                                                            }
                                                                                                ?>
            <?php
            if (request()->input('date_from') != '' && request()->input('date_to') != '') {

                $date = str_replace('/', '-', request()->input('date_from'));
                $date_from = date('d/m/Y', strtotime($date));

                $date = str_replace('/', '-', request()->input('date_to'));
                $date_to = date('d/m/Y', strtotime($date));

                echo '(' . $date_from . ' - ' . $date_to . ')';
            }


            ?>
        </h2>
        <a id="viewAllLabel" href="">Show All</a>
        <?php if ($customer['default_finance_person_id']==Auth::id()||!User::find(Auth::id())->hasPermission("restrictedaccess")) {?><a class="mp" href="multi_invoice_payments.php?customer_id=<?php echo request()->input('id'); ?>">Make / View payments</a><?php }?>
        <div class="loadingContainer">
            <img src="img/loading.gif" alt="">
        </div>
        <table id="soaTable" class="table" width="100%">
            <thead>
                <tr class="heading">
                    <th align="left" class="sticky-header">Invoice ID</th>
                    <th align="left" class="sticky-header">Add Payment</th>
                    <th align="left" data-orderable="false" class="sticky-header">Delv. Date</th>
                    <th align="left" data-orderable="false" class="sticky-header">Date</th>
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
    $.ajaxSetup({
		headers: { 'X-CSRF-TOKEN': "<?php echo csrf_token();?>" }
	});
    var toSkip = 0;
    var customer_id = <?php echo request()->input('id'); ?>;
    var date_from = '<?php echo request()->input('date_from'); ?>';
    var date_to = '<?php echo request()->input('date_to'); ?>';
    var table = null;
    var column = 3;
    var order = 'DESC';

    $(document).ready(function() {
        table = $('#soaTable').DataTable({
            "pageLength": -1,
            "order": [[ 0, "ASC" ]],

        });
        getData();
    });
    document.getElementById("viewAllLabel").addEventListener("click", toggleViewAll);

    function getData() {

        $.post("ajax/customer_soa_results.php", {
                customer_id: customer_id
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
    function toggleViewAll(event){
        event.preventDefault();
        $('.loadingContainer').show();
        if (showAll)
        {
            showAll = false;
            $("#viewAllLabel").text('Show All');
        }
        else
        {
            showAll = true;
            $("#viewAllLabel").text('Show Outstanding');
        }
        $('#soaTable').DataTable().destroy();
        $("#soaTable > tbody").empty();
        getRender();
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
