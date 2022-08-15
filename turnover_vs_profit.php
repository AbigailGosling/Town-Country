<?php
	include('includes/frontHeader.php');   
?>
<div id="top">
	<a href="menu.php" id="menu">MENU</a>
	<a href="logout.php" id="logout">LOGOUT</a>
</div>

<style type="text/css">
    
    .result{
        width:100%;
        background:#f2f2f2;
        margin:5px;
        height:50px;
    }
 
	.leftPanel{
		height:100%;
		padding:30px;
		border:1px solid #f4f4f4;
		position:relative;
	}
	
	.clearfix{
		clear:both;
	}
	
	.resultsContainer{
		min-height: 400px;
		border: 2px dashed #cacaca;
		padding: 0px;
		margin-top: 20px;
		padding-top: 14px;
	}
   
</style>
<div class="leftPanel" style="position:relative;">
    <h2>Turnover VS Profit Reports</h2>
    <form method="POST">
    <input name="invoice_id" id="invoice_id" placeholder="Invoice ID" value="<?php echo $_POST['invoice_id']; ?>" style="height:34px;width:100px;">
    <input name="intake_id" id="intake_id" placeholder="Intake ID" value="<?php echo $_POST['intake_id']; ?>" style="height:34px;width:100px;">
    <input name="pallet_id" id="pallet_id" placeholder="Pallet ID" value="<?php echo $_POST['pallet_id']; ?>" style="height:34px;width:100px;margin-right:20px;">

    <select name="species_id" id="species_id" style="width:152px;height:40px;">
        <option value="0" selected>All species</option>
		<?php
			$x = "SELECT * FROM `species`";
			$y = mysqli_query($conn, $x);
			
			while($row = mysqli_fetch_array($y)){
			?><option value="<?php echo $row['id']; ?>" <?php if($_POST['species_id'] == $row['id']){ echo 'selected'; } ?>><?php echo $row['name']; ?></option><?php
			}
		?>
	</select>

    <select name="cutgroup_id" id="cutgroup_id" style="width:152px;height:40px;">
        <option class="header" selected>...</option>
        <?php
            $x = "SELECT * FROM `cutgroups` WHERE id != 93";
            $y = mysqli_query($conn, $x);
            
            $i=0;
            while($row = mysqli_fetch_array($y)){
                
                
                $thisid = $row['species_id'];
                $y2 = mysqli_query($conn,"SELECT * FROM species WHERE id='$thisid'");
                $species = mysqli_fetch_array($y2);
                    ?><option style="display:none;" sid="<?php echo $row['id']; ?>" class="allsoption s<?php echo $species['id']; ?>" value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option><?php
                }
        ?>
	</select>
	<select id="SearchBrand" name="SearchBrand" style="width:152px;height:40px;">
        <option value="" disabled selected>Select Brand..</option>
		<?php
			$x = "SELECT * FROM `brands` ORDER BY `name`";
			$y = mysqli_query($conn, $x);
			
			while($row = mysqli_fetch_array($y)){
			?><option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option><?php
			}
		?>
	</select>
	<select id="SearchNationality" name="SearchNationality" style="width:152px;height:40px;">
        <option value="" disabled selected>Select Nationality..</option>
		<?php
			$x = "SELECT * FROM `nationality` ORDER BY `name`";
			$y = mysqli_query($conn, $x);
			
			while($row = mysqli_fetch_array($y)){
			?><option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option><?php
			}
		?>
	</select>
    <select name="cooling_id" id="cooling_id" style="width:152px;height:40px;">
        <option value="0" selected>Select option..</option>
        <?php
			$x = "SELECT * FROM `temperature`";
			$y = mysqli_query($conn, $x);
			
			while($row = mysqli_fetch_array($y)){
			?><option class="alltemperature temperature<?php echo $row['id']; ?>" value="<?php echo $row['id']; ?>" <?php if($_POST['cooling_id'] == $row['id']){ echo 'selected'; } ?>><?php echo $row['temperature']; ?></option><?php
			}
		?>
	</select>

    <select name="user_id" id="user_id" style="width:152px;height:40px;">
        <option value="" disabled selected>Select salesman..</option>
        <option value="0">All sales team</option>
		<?php
			$x = "SELECT * FROM `users`";
			$y = mysqli_query($conn, $x);
			
			while($row = mysqli_fetch_array($y)){
			?><option value="<?php echo $row['id']; ?>" <?php if($_POST['user_id'] == $row['id']){ echo 'selected'; } ?>><?php echo $row['name']; ?></option><?php
			}
		?>
	</select>


    <select name="customer_id" id="customer_id" style="width:182px;height:40px;">
        <option value="" disabled selected>Select customer..</option>
        <option value="0">All customers</option>
		<?php
			$x = "SELECT * FROM `customers` order by businessname ASC";
			$y = mysqli_query($conn, $x);
			
			while($row = mysqli_fetch_array($y)){
			?><option value="<?php echo $row['id']; ?>" <?php if($_POST['customer_id'] == $row['id']){ echo 'selected'; } ?>><?php echo $row['businessname']; ?></option><?php
			}
		?>
	</select>
    <?php
        if($date_start != ''){
            $uk_date_start = str_replace('/', '-', $date_start);
            $uk_date_start = date('d/m/Y', strtotime($uk_date_start));
        }

        if($date_end != ''){
            $uk_date_end = str_replace('/', '-', $date_end);
            $uk_date_end = date('d/m/Y', strtotime($uk_date_end));
        }
    ?>
    <b>BETWEEN</b>
    <input class="datepicker" name="date_start" id="date_start" placeholder="START DATE" value="<?php echo $uk_date_start; ?>" style="height:34px;width:100px;">
    <b>AND</b>
    <input class="datepicker" name="date_end" id="date_end" placeholder="END DATE" value="<?php echo $uk_date_end; ?>" style="height:34px;width:100px;">
    <input type="button" name="search" id="search" value="Search" style="height: 39px;width: 80px;" onclick="loadData(true)">
    </form>
 	
	<div id="loadResults" class="resultsContainer">
        <div id="loadingContainer" style="display:none;"><center><img src="/img/loading.gif" style="padding-top:170px;width:40px;text-align:center;"></center></div>
        <table style="width:100%;" id="resultsTable">

        </table>
    </div>
    <div class="loadMoreBtn" onclick="loadData(false)" style="display:none;">Load More</div>
</div>
<style>
    #resultsTable td{
        font-size:15px;
    }
</style>

<div class="clearfix"></div>
 
<script type="text/javascript">
    var req = null;
    function loadData(reset){    
        if (req){
            req.abort();
            req = null;
            $('#loadingContainer').hide();
            $("#search").prop('value', 'Search');
            return;
        }
        var invoice_id = $('#invoice_id').val();
        var species_id = $('#species_id').val();
        var cutgroup_id = $('#cutgroup_id').val();
        var cooling_id = $('#cooling_id').val();
        var intake_id = $('#intake_id').val();
        var pallet_id = $('#pallet_id').val();
        var user_id = $('#user_id').val();
        var customer_id = $('#customer_id').val();
        var brand_id = $('#SearchBrand').val();
        var nationality_id = $('#SearchNationality').val();
        var date_start = $('#date_start').val();
        var date_end = $('#date_end').val();

        $('#loadingContainer').fadeIn();

        req = $.post("/ajax/turnover_vs_profit_results.php",
        {
            invoice_id: invoice_id,
            species_id: species_id,
            cutgroup_id: cutgroup_id,
            cooling_id: cooling_id,
            intake_id: intake_id,
            pallet_id: pallet_id,
            user_id: user_id,
            customer_id: customer_id,
            date_start: date_start,
            date_end: date_end,
            brand_id: brand_id,
            nationality_id: nationality_id
        },
        function(data, status){
            $("#search").prop('value', 'Search');
            $('#loadingContainer').hide();
            $('#resultsTable').html(data);
            
            setTimeout(function() {
              
                var totalQuantity = 0;
                $('.quantityValue').each(function(){
                    var val = parseInt($(this).val());
                    totalQuantity = parseInt(totalQuantity) + val;
                });


                var totalWeightValue = 0;
                $('.weightValue').each(function(){
                    var val = parseFloat($(this).val());
                    totalWeightValue = (parseFloat(totalWeightValue) + val).toFixed(2);
                });

                var totalCostValue = 0.00;
                $('.costValue').each(function(){
                    var val = parseFloat($(this).val());
                    totalCostValue = (parseFloat(totalCostValue) + val).toFixed(2);
                 });

                var totalSellValue = 0.00;
                $('.sellValue').each(function(){
                    var val = parseFloat($(this).val());
                    totalSellValue = (parseFloat(totalSellValue) + val).toFixed(2);
                });

                totalProfitValue = (totalSellValue -totalCostValue).toFixed(2);

                $('.totalWeightValue').text(formatNumber(totalWeightValue) + ' kg');
                $('.totalQuantityValue').text(totalQuantity);
                $('.totalProfitValue').text('£' + formatNumber(totalProfitValue));
                $('.totalSellValue').text('£' + formatNumber(totalSellValue));
                $('.totalCostValue').text('£' + formatNumber(totalCostValue));
                
            }, 1000);
        

        });
        $("#search").prop('value', 'Abort');
    }

    function formatNumber(num) {
        return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
    }

    $(document).ready(function() {
        $( ".datepicker" ).datepicker({
            dateFormat: 'dd/mm/yy'
        });


        $('#species_id').change(function(){
            var val = $(this).val();
            $('#cutgroup_id option.allsoption').hide();
            $('#cutgroup_id option.s' + val).show();

            // iOS fix - display:none doesn't work on select options
            $('#cutgroup_id option.allsoption').unwrap('span');
            $('#cutgroup_id option.allsoption').wrap('<span/>');
            $('#cutgroup_id option.s' + val).unwrap();
        });
		
    });


</script>