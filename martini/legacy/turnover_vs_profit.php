<?php

use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

	include('includes/frontHeader.php');   
?>
<div id="top">
	<a href="menu.php" id="menu">MENU</a>
	<a href="logout" id="logout">LOGOUT</a>
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
    <input name="invoice_id" id="invoice_id" placeholder="Invoice ID" value="<?php echo request()->input('invoice_id'); ?>" style="height:34px;width:100px;">
    <input name="intake_id" id="intake_id" placeholder="Intake ID" value="<?php echo request()->input('intake_id'); ?>" style="height:34px;width:100px;">
    <input name="pallet_id" id="pallet_id" placeholder="Pallet ID" value="<?php echo request()->input('pallet_id'); ?>" style="height:34px;width:100px;margin-right:20px;">

    <select name="species_id" id="species_id" style="width:152px;height:40px;">
        <option value="0" selected>All species</option>
		<?php
			$x = "SELECT * FROM `species`";
			$y = prepareExecuteQuery($x);
			
			while($row = mysqli_fetch_array($y)){
			?><option value="<?php echo $row['id']; ?>" <?php if(request()->input('species_id') == $row['id']){ echo 'selected'; } ?>><?php echo $row['name']; ?></option><?php
			}
		?>
	</select>

    <select name="cutgroup_id" id="cutgroup_id" style="width:152px;height:40px;">
        <option class="header" selected>...</option>
        <?php
            $x = "SELECT * FROM `cutgroups` WHERE id != 93";
            $y = prepareExecuteQuery($x);
            
            $i=0;
            while($row = mysqli_fetch_array($y)){
                
                
                $thisid = $row['species_id'];
                $y2 = prepareExecuteQuery("SELECT * FROM species WHERE id=?",'i',[$thisid]);
                $species = mysqli_fetch_array($y2);
                    ?><option style="display:none;" sid="<?php echo $row['id']; ?>" class="allsoption s<?php echo $species['id']; ?>" value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option><?php
                }
        ?>
	</select>
	<select id="SearchBrand" name="SearchBrand" style="width:152px;height:40px;">
        <option value="" disabled selected>Select Brand..</option>
        <option value="0">All Brands</option>
		<?php
			$x = "SELECT * FROM `brands` where `name` IS NOT NULL AND `name` <> '' ORDER BY `name`";
			$y = prepareExecuteQuery($x);
			
			while($row = mysqli_fetch_array($y)){
			?><option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option><?php
			}
		?>
	</select>
    <select name="supplier_id" id="supplier_id" style="width:182px;height:40px;">
        <option value="" disabled selected>Select Supplier..</option>
        <option value="0">All Suppliers</option>
		<?php
			$x = "SELECT * FROM `supplier` where `disabled` = 0 AND `name` IS NOT NULL AND `name` <> '' order by `name` ASC";
			$y = prepareExecuteQuery($x);
			
			while($row = mysqli_fetch_array($y)){
			?><option value="<?php echo $row['id']; ?>" <?php if(request()->input('supplier_id') == $row['id']){ echo 'selected'; } ?>><?php echo $row['name']; ?></option><?php
			}
		?>
        <option value="" disabled>Disabled Suppliers</option>
        <?php
			$x = "SELECT * FROM `supplier` where `disabled` = 1 AND `name` IS NOT NULL AND `name` <> '' order by `name` ASC";
			$y = prepareExecuteQuery($x);
			
			while($row = mysqli_fetch_array($y)){
			?><option style="color:gray" value="<?php echo $row['id']; ?>" <?php if(request()->input('supplier_id') == $row['id']){ echo 'selected'; } ?>><?php echo $row['name']; ?></option><?php
			}
		?>
	</select>
	<select id="SearchNationality" name="SearchNationality" style="width:152px;height:40px;">
        <option value="" disabled selected>Select Nationality..</option>
		<?php
			$x = "SELECT * FROM `nationality` ORDER BY `name`";
			$y = prepareExecuteQuery($x);
			
			while($row = mysqli_fetch_array($y)){
			?><option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option><?php
			}
		?>
	</select>
    <select name="cooling_id" id="cooling_id" style="width:152px;height:40px;">
        <option value="0" selected>Select option..</option>
        <?php
			$x = "SELECT * FROM `temperature`";
			$y = prepareExecuteQuery($x);
			
			while($row = mysqli_fetch_array($y)){
			?><option class="alltemperature temperature<?php echo $row['id']; ?>" value="<?php echo $row['id']; ?>" <?php if(request()->input('cooling_id') == $row['id']){ echo 'selected'; } ?>><?php echo $row['temperature']; ?></option><?php
			}
		?>
	</select>

    <select name="user_id" id="user_id" style="width:152px;height:40px;">
        <option value="" disabled selected>Select salespeople..</option>
        <option value="0">All sales team</option>   
		<?php
			$users = User::orderBy("name")->get();
            $disabledUsers = [];
            $sellPermission = Permission::find(1);
			foreach ($users as $row){
                if ($row['disabled'] == 0 && $row->hasPermission($sellPermission)){
                    ?><option value="<?php echo $row['id']; ?>" <?php if(request()->input('user_id') == $row['id']){ echo 'selected'; } ?>><?php echo $row['name']; ?></option><?php
                }
                else $disabledUsers[] = $row;
			}
            ?>
            <option value="" disabled>Other Users</option>
            <?php
            foreach ($disabledUsers as $row){
                ?><option style="color:gray" value="<?php echo $row['id']; ?>" <?php if(request()->input('user_id') == $row['id']){ echo 'selected'; } ?>><?php echo $row['name']; ?></option><?php
            }
		?>
	</select>


    <select name="customer_id" id="customer_id" style="width:182px;height:40px;">
        <option value="" disabled selected>Select customer..</option>
        <option value="0">All customers</option>
		<?php
			$x = "SELECT * FROM `customers` where `disabled` = 0 order by businessname ASC";
			$y = prepareExecuteQuery($x);
			
			while($row = mysqli_fetch_array($y)){
			?><option value="<?php echo $row['id']; ?>" <?php if(request()->input('customer_id') == $row['id']){ echo 'selected'; } ?>><?php echo $row['businessname']; ?></option><?php
			}
		?>
        <option value="" disabled>Disabled Customers</option>
        <?php
			$x = "SELECT * FROM `customers` where `disabled` = 1 order by businessname ASC";
			$y = prepareExecuteQuery($x);
			
			while($row = mysqli_fetch_array($y)){
			?><option style="color:gray" value="<?php echo $row['id']; ?>" <?php if(request()->input('customer_id') == $row['id']){ echo 'selected'; } ?>><?php echo $row['businessname']; ?></option><?php
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
    <br/><br/>
    <b>BETWEEN</b>
    <input class="datepicker" name="date_start" id="date_start" placeholder="START DATE" value="<?php echo $uk_date_start; ?>" style="height:34px;width:100px;">
    <b>AND</b>
    <input class="datepicker" name="date_end" id="date_end" placeholder="END DATE" value="<?php echo $uk_date_end; ?>" style="height:34px;width:100px;">
    <input type="button" name="search" id="search" value="Search" style="height: 39px;width: 80px;" onclick="loadData(true)">
    </form>
 	
	<div id="loadResults" class="resultsContainer">
        <div id="loadingContainer"><center><img src="/legacy/img/loading.gif" style="padding-top:170px;width:40px;text-align:center;"></center></div>
		<div id="loadResults2"/>
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
		$('#loadingContainer').hide();
    $.ajaxSetup({
		headers: { 'X-CSRF-TOKEN': "<?php echo csrf_token();?>" }
	});
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
        var supplier_id = $('#supplier_id').val();
        var date_start = $('#date_start').val();
        var date_end = $('#date_end').val();

		$('#loadingContainer').show();
		$('#loadResults2').html("");  
        req = $.post("ajax/turnover_vs_profit_results.php",
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
            nationality_id: nationality_id,
            supplier_id: supplier_id
        },
        function(data, status){
			req = null;
            $("#search").prop('value', 'Search');
            $('#loadingContainer').hide();
            $('#loadResults2').html(data);        

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