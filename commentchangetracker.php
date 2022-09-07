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
    <h2></h2>
    <input name="entity_id" id="entity_id" placeholder="Enter ID..." value="<?php echo $_POST['invoice_id']; ?>" style="height:34px;width:100px;"></input>
    <select name="type_id" id="type_id" style="width:152px;height:40px;">
        <option value="" selected>All Types</option>
		<?php
			$x = "SELECT DISTINCT `type` FROM `comment_logging`";
			$y = mysqli_query($conn, $x);
			
			while($row = mysqli_fetch_array($y)){
			?><option value="<?php echo $row['type']; ?>"><?php echo $row['type']; ?></option><?php
			}
		?>
	</select>
    <select name="user_id" id="user_id" style="width:152px;height:40px;">
        <option value="" selected>All Users</option>
		<?php
			$x = "SELECT `users`.`id`,`users`.`name` FROM `comment_logging` INNER JOIN `users` ON `comment_logging`.`user_id` = `users`.`id` GROUP BY `users`.`id`";
			$y = mysqli_query($conn, $x);
			
			while($row = mysqli_fetch_array($y)){
			?><option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option><?php
			}
		?>
	</select>
    <b>BETWEEN</b>
    <input class="datepicker" name="date_start" id="date_start" placeholder="START DATE" style="height:34px;width:100px;"></input>
    <b>AND</b>
    <input class="datepicker" name="date_end" id="date_end" placeholder="END DATE" style="height:34px;width:100px;"></input>
    <input type="button" name="search" id="search" value="Search" style="height: 39px;width: 80px;" onclick="fetchResults()"></input>
</div>
<div class="mainstatement">
        <table id="soaTable" class="table" width="100%" style="font-size:10pt;border-spacing: 0;border-color: grey;">
            <thead>
                <tr class="heading">
                    <th width="10%" align="left"></th>
                    <th width="20%" align="left"></th>
                    <th width="20%" align="left"></th>
                    <th width="20%" align="right"></th>
                    <th width="20%" align="right"></th>
                </tr>
            </thead>
            <tbody>
                <tr><td></td><td></td><td></td><td></td><td></td><td></td></tr>
            </tbody>
            <tfoot class="last">
                <tr>
                    <th align="left"></th>
                    <th align="left"></th>
                    <th align="left"></th>
                    <th align="right"></th>
                    <th align="right"></th>
                </tr>
            </tfoot>
        </table>
        </div>
</div>

<div class="clearfix"></div>
<script type="text/javascript">
$(document).ready(function(){ 
    console.log("t");
    $("#date_start").datepicker({
        dateFormat: 'dd/mm/yy'
    });
    $("#date_end").datepicker({
        dateFormat: 'dd/mm/yy'
    });
    console.log("t");
});
function fetchResults(){
    $("#soaTable > tbody").empty();
    var entity_id = $("#entity_id").val();
    var type_id = $("#type_id").val();
    var user_id = $("#user_id").val();
    var date_start = $("#date_start").val();
    var date_end = $("#date_end").val();
    $.post("/ajax/generateCommentCheck.php", { 'entity_id':entity_id, 'type_id':type_id, 'user_id':user_id, 'date_start':date_start, 'date_end':date_end }, results);
}
function results(data, status){
    console.log(data);
    $("#soaTable > tbody").empty();
    $("#soaTable > tbody").append(data);
    

}
</script>