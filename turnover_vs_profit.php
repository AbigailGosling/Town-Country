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
    <input name="intake_id" id="intake_id" placeholder="Intake ID" value="<?php echo $_POST['intake_id']; ?>" style="height:34px;width:100px;">
    <input name="pallet_id" id="pallet_id" placeholder="Pallet ID" value="<?php echo $_POST['pallet_id']; ?>" style="height:34px;width:100px;margin-right:20px;">

    <input type="hidden" id="toSkipCount" value="0">
    <input type="hidden" id="moreRowsAvailable" value="1"> 

    <select name="species_id" id="species_id" style="width:152px;height:40px;">
        <option value="" disabled selected>Select species..</option>
        <option value="0">All species</option>
		<?php
			$x = "SELECT * FROM `species`";
			$y = mysqli_query($conn, $x);
			
			while($row = mysqli_fetch_array($y)){
			?><option value="<?php echo $row['id']; ?>" <?php if($_POST['species_id'] == $row['id']){ echo 'selected'; } ?>><?php echo $row['name']; ?></option><?php
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
    <input type="button" value="Search" style="height: 39px;width: 80px;" onclick="loadData(true)">
    <b>BETWEEN</b>
    <input class="datepicker" name="date_start" id="date_start" placeholder="START DATE" value="<?php echo $uk_date_start; ?>" style="height:34px;width:100px;">
    <b>AND</b>
    <input class="datepicker" name="date_end" id="date_end" placeholder="END DATE" value="<?php echo $uk_date_end; ?>" style="height:34px;width:100px;">
    </form>
 	
	<div id="loadResults" class="resultsContainer">
        <table style="width:100%;" id="resultsTable">

        </table>
    </div>
    <div class="loadMoreBtn" onclick="loadData(false)" style="display:none;">Load More</div>
</div>


<div class="clearfix"></div>
 
<script type="text/javascript">
    
    function loadData(reset){

        if(reset == true){
            var toSkip = 0;
            $('#resultsTable').html('');
        }else{
            var toSkip = $('#toSkipCount').val();
        }
        
        var species_id = $('#species_id').val();
        
        var intake_id = $('#intake_id').val();
        var pallet_id = $('#pallet_id').val();
        var user_id = $('#user_id').val();
        var customer_id = $('#customer_id').val();

        var date_start = $('#date_start').val();
        var date_end = $('#date_end').val();

        $.post("/ajax/turnover_vs_profit_results.php",
        {
            toSkip: toSkip,
            species_id: species_id,
            intake_id: intake_id,
            pallet_id: pallet_id,
            user_id: user_id,
            customer_id: customer_id,
            date_start: date_start,
            date_end: date_end,
        },
        function(data, status){
            $('#resultsTable').append(data);
            
            setTimeout(() => {
                var toSkip = parseInt($('#toSkipCount').val());
                var moreRowsAvailable = parseInt($('#moreRowsAvailable').val());

                console.log('toskip: ' + toSkip);
                console.log('totalCount: ' + moreRowsAvailable);
                if(moreRowsAvailable == 1){
                    $('.loadMoreBtn').show();
                }else{
                    $('.loadMoreBtn').hide();
                }
            }, 1000);
        

        });
    }


    $(document).ready(function() {
        $( ".datepicker" ).datepicker({
            dateFormat: 'dd/mm/yy'
        });
		
    });


</script>
 
  
</script>