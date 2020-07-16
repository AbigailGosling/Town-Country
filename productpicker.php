<?php
	include('includes/frontHeader.php');
?>
<div id="top">
	<a href="menu.php" id="menu">MENU</a>
	<a href="logout.php" id="logout">LOGOUT</a>
</div>


<form id="pickerForm" method="POST" action="/scripts/buildPicker.php" autocomplete="off">
<input autocomplete="off" name="hidden" type="text" style="display:none;">
<input type="hidden" name="addressid" id="addressid" value="1">
<div class="topBox">
	<div style="padding:40px;">
		<div>
			<label>Customer</label><br/>
			<input type="text" id="customer" class="inputbox" required>
			<div id="customer_search_results" style="position:relative;z-index:99999;"></div>
		</div>
		<div>
			<label>Delivery Date</label><br/>
			<input type="text" class="inputbox" id="estimated_delivery_date" name="estimated_delivery_date" placeholder="">
		</div>
	  
	</div>
	
	<span id="address"></span>
	 
	
	<div style="display:block;padding-top:10px;position:absolute;left:505px;top:300px;">
		<label>	Order Reference Number</label><br/>
		<input type="text" class="inputbox" name="orderReferenceNumber" value="<?php echo $row['orderReferenceNumber']; ?>">
	</div>
</div>

<div class="rightPanel">
	<table width="100%" class="basketTable">
		<tr align="left" style="background:#3FADDD;height:30px;color:#FFF;">
			<th>Intake ID</th>
			<th>Plt ID</th>
			<th>Product</th>
			<th>Nationality</th>
			<th>Brand</th>
			<th>Volume <span style="display:none;">(num of cases)</span</th>
			<th>Weight</th>
			<th>Sell Price</th>
			<th>Value</th>
		</tr>
	</table>
	
	<div style="float:right;">
		<br/><br/>
		<div class="totalprice" style="display:none;"></div>
		<br/>
		<input type="submit" value="Send" id="sendreal" class="inputbox-button" style="display:none">
		<input type="button" value="Completed" id="sendfake" class="inputbox-button">
	</div>
</div>
</form>

<div class="leftPanel" style="position:relative;">
    <form id="searchForm">
	<select id="SearchSpecies" style="width:322px;height:40px;">
        <option value="" disabled selected>Select species..</option>
		<?php
			$x = "SELECT * FROM `species`";
			$y = mysqli_query($conn, $x);
			
			while($row = mysqli_fetch_array($y)){
			?><option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option><?php
			}
		?>
	</select>
    
    <select id="SearchCutgroups" name="cutgroup_id" style="width:322px;height:40px;">
            <option sid="<?php echo $rand; ?>" class="header" value="<?php echo $rand; ?>" selected>Select cut..</option>
            <?php
                $x = "SELECT * FROM `cutgroups`";
                $y = mysqli_query($conn, $x);
                
                $i=0;
                while($row = mysqli_fetch_array($y)){
                    
                    
                    $thisid = $row['species_id'];
                    $y2 = mysqli_query($conn,"SELECT * FROM species WHERE id='$thisid'");
                    $species = mysqli_fetch_array($y2);
                    $rand = 'z' . rand(6000,12212);
                        ?><option style="display:none;" sid="<?php echo $row['id']; ?>" class="allsoption s<?php echo $species['id']; ?>" value="<?php echo $row['id']; ?>"<?php if($_POST['acutgroup_id'] == $row['id']){ echo 'selected'; } ?>><?php echo $row['name']; ?></option><?php
                    }
            ?>
        </select>
    &nbsp;&nbsp;&nbsp;
    <input type="number" name="intake_id" id="IntakeID" placeholder="Intake ID" style="width:100px;height: 33px;padding-left: 10px;">
    <input type="number" name="pallet_id" id="PalletID" placeholder="Pallet ID" style="width:100px;height: 33px;padding-left: 10px;">
    <input type="button" onclick="doSearch()" value="Search" style="height: 39px;width: 80px;">
    </form>
    <div class="weightTotal" style="display:none;">Total Weight: <span class="weightVal">0</span>kg</div>
	
	<div id="loadResults" class="resultsContainer"></div>
</div>


<div class="clearfix"></div>
<?php 
	if($_GET['msg'] != ''){
	?>
	<script type="text/javascript">
		alert('<?php echo $_GET['msg'];?>');
	</script>
	<?php	
	}
?>
<script type="text/javascript">

    $(document).ready(function() {

        var formHasChanged = false;
        var submitted = false;

				document.getElementById('menu').addEventListener('click', function(e) {
					if (formHasChanged && !submitted) {
						e.preventDefault()
						var alert = confirm('Are you sure you want to leave?')

						if (alert === true) {
							window.location.href = "/menu.php"
						}
					}
				})

				function alertFunction() {
					alert('hello')
				}
        
        $(document).on('change', 'form #customer', function (e) {
            formHasChanged  = true;
        });

        $("#pickerForm").submit(function() {
            submitted = true;
        });

    });

	function addToList(id){
		
		$.get( "/scripts/getBasketItem.php?id="+id, function( data ) {
			$('.basketTable').append(data);
		});
		
	}
	
	function removeFromList(id, pallet_id, product_id){
		$('.basketRow-' + id).remove();
		var COOKIE_NAME = "quantity-"+product_id+"-"+pallet_id;
		
		console.log('trying to delete cookie ' + COOKIE_NAME);
		document.cookie = COOKIE_NAME + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
	}
</script>

<style type="text/css">
     
    
    

	.rightPanel{
		width:calc(100% - 103px);
	
		float:left;
		padding:50px;
		position:relative;
		margin-top:40px;
	}
	.leftPanel{
		height:100%;
		padding:30px;
		border:1px solid #f4f4f4;
		position:relative;
	}
	
	.leftPanel{
		/* background:#f2f2f2; */
	}
	
	.clearfix{
		clear:both;
	}
	
	.inputbox-button{
		width:323px;
		height:34px;
		margin-bottom:10px;
	}
	
	.inputbox{
		width:300px;
		height:34px;
		padding-left:18px;
 
	}
	
	.createCustomerContainer{
		font-weight:700;
		position:absolute;
		top:50px;
		right:30px;
	}
	
	.weightTotal{
		font-weight:700;
		position:absolute;
		top:50px;
		right:30px;
	}
	
	.resultsContainer{
		min-height: 400px;
		border: 2px dashed #cacaca;
		padding: 0px;
		margin-top: 20px;
		padding-top: 14px;
	}

	.searchRContent {
		border-collapse: collapse;
		text-align: center;
		font-size: 14px;
	}

	.searchRContent__head {
		border-bottom: 1px solid #d9d9d9;
		font-size: 14px;
	}

	.searchRContent__head th {
		padding-bottom: 10px;
	}

	.searchRContent__icon {
		font-size: 14px;
	}

	.searchRContent .bold {
		font-size: 16px;
		font-weight: bold;
		padding: 0 10px;
	}

	.searchAccordTitle:nth-child(odd) {
		background: #f2f2f2;
	}

	.searchAccordTitle:nth-child(event) .overviewcomment {
		border: 1px solid #f2f2f2;
	}

	.searchAccordTitle td {
		border: 0;
		padding: 0;
	}

	@media only screen
	and (min-device-width : 768px) 
	and (max-device-width : 1024px)  {
		.searchRContent {
			font-size: 10px
		}

		.searchRContent__head {
			font-size: 12px;
		}

		.searchRContent .bold {
			font-size: 14px;
		}
	}

</style>
<script type="text/javascript">
    
    setTimeout(function(){
        $('.select2-container').css('display', 'none');
        $('.select2-container').first().css('display', 'inline-block');
    }, 10);

	$('#sendfake').click(function(){
		var customer = $('#customer').val();
		var date = $('#estimated_delivery_date').val();
		
		 
		
		ready = 1;
		
		
		 
		
		
		if(customer != ''){
			ready = 1;
		}else{
			ready = 0;
 			$('#customer').css('border','1px solid red');
		}
		
		 
		
		$('.price').each(function(){
 			var value = $(this).val();
			
			if(value == ''){
				ready = 0;
				
				$(this).css('border','1px solid red');
			}
			
		});
		
if(date != ''){
			ready = 1;
		}else{
			ready = 0;
 			$('#estimated_delivery_date').css('border','1px solid red');
		}

		if(ready == 1){
			$('#sendreal').trigger('click');
		}else{
			alert('Please complete the missing fields');
		}
		 
		
		console.log(ready);
		
	});
	
	function setCustomerDetails(customer_id, empty='false'){
		console.log(' setCustomerDetails()');
		
		$.get( "ajax/getCustomerAddress.php?id=" + customer_id + '&empty=' + empty, function( data ) {
			$('#address').html(data);
			$('.rating').fadeIn();
		});
	}
	ready = true;
	setInterval(function(){
		ready = true;
		var totalPrice = 0;
		
		$('.price').each(function(){
			var q = $(this).attr('q');
			
			if(this.value != ''){
				var finalVal = (parseFloat(this.value)) * q;
				
				totalPrice += finalVal;
			}else{
				ready = false;
			}
			
		});
		
		
 	}, 300);
	
		
	
	$(document).ready(function(){  
		setCustomerDetails(null, 'true');
		
		
		$.each(document.cookie.split(/; */), function(){
		  var splitCookie = this.split('=');

			
			if(splitCookie[0].includes('quantity-')){
				document.cookie = splitCookie[0] + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT;';	
			}
		});
	  
		$( "#estimated_delivery_date" ).datepicker({
			dateFormat: 'dd/mm/yy'
		});
		
		
	});
	 

    $('#SearchSpecies').change(function(){

        var thisval = $(this).val();
        $('#SearchCutgroups option.allsoption').hide();
        $('#SearchCutgroups option.s'+thisval).show();
        //$('#SearchCutgroups').val($('#SearchCutgroups option.s'+thisval).first().attr('sid'));


        var id = $(this).val();

        //doSearch();
    });

    $('#SearchCutgroups').change(function(){
        var id = $(this).val();

        //doSearch();
    });

 
	function doSearch(){
		console.log('Searching..');
		var cut = $('#SearchCut').val();
		var palletID = $('#SearchPallet').val();
		var species = $('#SearchSpecies').val();
		var cutgroup_id = $('#SearchCutgroups').val();
 		var temperatureID = $('#temperatureID').val();
 		var intakeID = $('#IntakeID').val();
 		var palletID = $('#PalletID').val();
		
		if(cutgroup_id != '' && species != '' || intakeID != '' || palletID != ''){
			$('#loadResults').html('<center><img src="/img/loading.gif" style="padding-top:170px;width:40px;text-align:center;"></center>');
			
			$.get("/scripts/searchPicker.php?cutgroup_id=" + cutgroup_id + "&species=" + species +  "&temperatureID=" + temperatureID +  "&palletID=" + palletID + "&intakeID=" + intakeID, function(data, status){
				$('#loadResults').html(data);
				
			});


			$('#SearchSpecies').prop('selectedIndex',0);
			$('#SearchCutgroups').prop('selectedIndex',0);
			$('#IntakeID').val('');
			$('#PalletID').val('');
		}else{
			alert('Please fill out the form before searching');
		}
	}
	
	function ShowWeights(pallet_id,species_id, cut_id){
		// $('#weightsContainer').fadeOut();
		$('.weights' + pallet_id + species_id + cut_id).toggle();
	}
	
	$('#submitCustomerAccount').click(function(){
		$.ajax({
			type: 'POST',
			url: '/scripts/addCustomer.php',
			data: $('#createCustomerForm').serialize(),
			success: function () {
				$('#createCustomerForm')[0].reset();
				alert('Customer Added - please refresh to see changes!');
			}
		});
	});
	
	$('#customer').keyup(function(){
		var val = $('#customer').val();
		$('#customer_search_results').fadeIn();

	 	
		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
            $('#customer_search_results').html(this.responseText);
		}
		};
		xhttp.open("POST", "/ajax/getCustomerDropdown.php", true);
		xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xhttp.send("searchterm=" + val);
	
	});
		
		
		
	function changeAddress(customer_id, address_id){

		$('#addressid').val(address_id);


		$.get("/ajax/getCustomerAddress.php?id=" + customer_id + '&address_id=' + address_id, function(data, status){
			$('#address').html(data);
			$('.lity-close').trigger('click');
		});
	}
		
	
</script>

<style type="text/css">
input[type='number'] {
    -moz-appearance:textfield;
}
/* Webkit browsers like Safari and Chrome */
input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.select2-container--default .select2-selection--single{
    height:40px;
    border-radius:0px;
}

.select2-container--default .select2-selection--single .select2-selection__rendered{
    line-height:41px;
}

.select2-results__option:first-child{ display:none; }
</style>