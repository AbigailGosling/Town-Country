<?php
    include_once('includes/frontHeader.php');
?>
<div id="top" class="printhide">
    <a href="menu.php" id="menu">MENU</a>
    <a href="logout.php" id="logout">LOGOUT</a>
</div>
<style id="mainStyle">

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
    .dataTables_length,
    .dataTables_info,
    .dataTables_paginate,
    #soaTable_filter {
        display: none;
    }
</style>
<div class="leftPanel" style="padding:20px">
    <h2>Outstanding Breakdown</h2>
    <select name="user_id" id="user_id" style="width:152px;height:40px;">
        <option value="" selected>All Users</option>
		<?php
			$x = "SELECT `users`.`id`,`users`.`name` FROM `users` WHERE `users`.`name` NOT LIKE '%REMOVED%' ORDER BY `users`.`name`";
			$y = mysqli_query($conn, $x);
			
			while($row = mysqli_fetch_array($y)){
			?><option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option><?php
			}
		?>
	</select>
    <select name="customer_id" id="customer_id" style="width:152px;height:40px;">
        <option value="" selected>All Customers</option>
		<?php
			$x = "SELECT `customers`.`id`,`customers`.`businessname` FROM `customers` WHERE `disabled` = 0 AND `customers`.`businessname` NOT IN ('','.. search') ORDER BY `customers`.`businessname`";
			$y = mysqli_query($conn, $x);
			
			while($row = mysqli_fetch_array($y)){
			?><option value="<?php echo $row['id']; ?>"><?php echo $row['businessname']; ?></option><?php
			}
		?>
	</select>
    <input type="button" name="search" id="search" value="Search" style="height: 39px;width: 80px;" onclick="fetchResults()"></input>
</div>
<div class="mainstatement">
        <table id="soaTable" class="table sortable" width="100%" style="font-size:10pt;border-spacing: 0;border-color: grey;">
            <thead>
                <tr class="heading" style="position: sticky; top: 0;">
                    <th width="12.5%" align="left">Default user</th>
                    <th width="12.5%" align="left">Customer</th>
                    <th width="12.5%" align="left">Account Number</th>
                    <th width="12.5%" align="left">Current</th>
                    <th width="12.5%" align="left">Due</th>
                    <th width="12.5%" align="left">Overdue</th>
                    <th width="12.5%" align="left">Beyond Grace</th>
                    <th width="12.5%" align="left">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
            </tbody>
            <tfoot class="last">
                <tr>
                    <th align="left"></th>
                    <th align="left"></th>
                    <th align="left"></th>
                    <th align="left"></th>
                    <th align="left"></th>
                    <th align="left"></th>
                    <th align="left"></th>
                    <th align="left"></th>
                </tr>
            </tfoot>
        </table>
        </div>
        <div align="center" style="display:none" id="loadericoncenter" name="loadericoncenter" class="loadericoncenter">
            <img src="img/loading.gif" alt="">
        </div>
</div>

<div class="clearfix"></div>
<script type="text/javascript">
$(document).ready(function(){ 
});
function fetchResults(){
    $("#search").prop('disabled', true);
    $("#loadericoncenter").show();
    $("#soaTable > tbody").empty();
    var customer_id = $("#customer_id").val();
    var user_id = $("#user_id").val();
    $.post("/ajax/generateOutstandingBreakdown.php", { 'customer_id':customer_id,'user_id':user_id }, results);
}
function results(data, status){
    var arr = data.split("|");
    $("#soaTable > tbody").empty();
    $("#soaTable > tbody").append(arr[0]);
    $("#soaTable > tfoot").empty();
    $("#soaTable > tfoot").append(arr[1]);
    $("#loadericoncenter").hide();
    $("#search").prop('disabled', false);
}
</script>