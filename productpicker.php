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
<div class="container container--pt">
	<div class="row">
		<div class="col">
			<label>Customer</label><br/>
			<input class="form-control" type="text" id="customer" class="inputbox" required>
			<div id="customer_search_results" style="position:relative;z-index:99999;"></div>
		</div>
		<div class="col">
			<label>Delivery Date</label><br/>
			<input class="form-control" type="text" class="inputbox" id="estimated_delivery_date" name="estimated_delivery_date" placeholder="">
		</div>
	  
	</div>
	
	<div class="row" id="address"></div>
	 
	
	<div class="row">
		<div class="col">
			<label>	Order Reference Number</label><br/>
			<input class="form-control" type="text" class="inputbox" name="orderReferenceNumber" value="<?php echo $row['orderReferenceNumber']; ?>">
		</div>
		<div class="col"></div>
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
	
	<div>
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
				changePage('menu')
			}
		})

        $(document).on('change', 'form #customer', function (e) {
            formHasChanged  = true;
        });

        $("#pickerForm").submit(function() {
            submitted = true;
        });

		$(document).on('click', '.intakeLink', function(e) {
			if (formHasChanged && !submitted) {
				e.preventDefault()	
				changePage($(this).attr('id'))
			}
		})

		function changePage(prop) {					
			var alert = confirm('Are you sure you want to leave?')

			if (alert === true) {
				if (prop == 'menu') {
					window.location.href = "/menu.php"
				} else {
					window.location.href = '/intake.php?id=' + prop + '&ref=salesconfirmationsheet'
				}
			}
		}

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

	
function checkStock(){
    var readyToSubmit = 1;

    var group = $('input[name="basketRow[]"]');
		group.each(function (index) {
            var value = $(this).val();
            var bits = value.split('-');
            var product_id = bits[0];
            var quantity_wanted = bits[1];

            $.get("/ajax/checkProductStockQuantity.php?product_id=" + product_id, function(num, status){
 				console.log('Type Before: ' + typeof num);
				var product_stock_count = parseInt(num);
				console.log('Type After: ' + typeof product_stock_count);

				console.log('We wanted ' + quantity_wanted + ' there are ' + product_stock_count);
				
                if(quantity_wanted <= product_stock_count){
 
				}else{
                    $('.product' + product_id).css('background-color','red');

                    readyToSubmit = 0;
                }
            });
        });

        setTimeout(function(){
			if(readyToSubmit == 0){
				Swal.fire({
					title: "Some of the selected items are already sold",
					text: "Please search stock again to view available items",
					icon: "warning",
					showCancelButton: false,
					showConfirmButton: false,
					dangerMode: true,
					showCloseButton: true
				});

				$('#sendfake').prop('disabled', false);
			}else{
				$('#sendreal').trigger('click');
			}
		}, 2000);
}
</script>

<style type="text/css">
     
    
    

	.rightPanel {
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
		table-layout: fixed;
		width: 100%;
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
		padding: 0 5px;
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

	.location-input {
		width: 80px;
	}

	.searchRContent__plus {
		width: 28px;
	}

	.searchRContent__product {
		width: 180px;
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

			.searchRContent__id {
		width: 48px;
		}
		.searchRContent__location {
			width: 60px;
		}

		.searchRContent__dropdown {
			width: 20px;
		}

		.searchRContent__unit {
			width: 55px;
		}

		.searchRContent__chill {
			width: 40px;
		}

		.searchRContent__product {
			width: 140px;
		}

		.searchRContent__date-range {
			width: 70px;
		}

		.location-input {
			width: 50px;
		}
	}

	.subrow {
		height: 58px;
		background:#d9d9d9;
	}

	.subrow:hover {
		background: #979797;
		border: 1px solid #000;
	}

</style>
<script type="text/javascript">
	var customerID = null;

    setTimeout(function(){
        $('.select2-container').css('display', 'none');
        $('.select2-container').first().css('display', 'inline-block');
    }, 10);

	$('#sendfake').click(function(){

		$(this).prop('disabled', true);
		var customer = $('#customer').val();
		var date = $('#estimated_delivery_date').val();
		
		customerEntered = false;
		dateEntered = false;
		priceEntered = false;
		pricedCorrectly = false;
		if (customer != '' && !isNaN(customerID)) {
			customerEntered = true;
			$('#customer').css('border-color', '#f2f2f2');
		} else{
			customerEntered = false;
 			$('#customer').css('border','1px solid red');
		}
		
		if (date != '') {
			dateEntered = true;
			$('#estimated_delivery_date').css('border-color', '#f2f2f2');
		} else{
			dateEntered = false;
 			$('#estimated_delivery_date').css('border','1px solid red');
		}

		$('.price').each(function(){
 			var value = $(this).val();
			
			if(parseFloat(value) && value > 0){
				priceEntered = true;
				if(parseFloat(value) >= parseFloat($('.price').attr('cost'))){
					pricedCorrectly = true;
				}else{
					$(this).css('border','1px solid red');
					if(confirm('Are you sure? the price is less than the cost')){
						pricedCorrectly = true;
					}else{
						pricedCorrectly = false;
					}
				}

			}else{
				priceEntered = false;
				$(this).css('border','1px solid red');
			}
		});

		if(customerEntered && dateEntered && priceEntered && pricedCorrectly){
			checkStock();
		}else{
			if(!customerEntered || !dateEntered || !priceEntered){
				alert('Please complete the missing fields');
			}

			$('#sendfake').prop('disabled', false);

		}
		 
		
		console.log(ready);
		
	});
	
	function setCustomerDetails(customer_id, empty='false'){
		customerID = customer_id;
		console.log(' setCustomerDetails()');
		
		$.get( "ajax/getCustomerAddress.php?id=" + customer_id + '&empty=' + empty, function( data ) {
			$('#address').html(data);
			$('.rating').fadeIn();
			
			$('#addressline1').prop('disabled', true);
			$('#addressline2').prop('disabled', true);
			$('#addressline3').prop('disabled', true);
			$('#addressline4').prop('disabled', true);
			$('#addresspostcode').prop('disabled', true);
			$('#deliverynumber').prop('disabled', true);
			
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
		<?php
			$user_id = $_SESSION['USER'];
			$myCustomerResult = mysqli_query($conn, "SELECT * FROM `customers` WHERE FIND_IN_SET($user_id,users)");
			$row = mysqli_fetch_array($myCustomerResult);
			$my_customer_id =  $row['id'];
		?>
		setCustomer('<?php echo $my_customer_id; ?>','<?php echo $row['businessname']; ?>');

		
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
	
	function setCustomer(customer_id, text){
		console.log('customer_id: ' + customer_id);
		console.log('text: ' + text);
		$('#customer_search_results').fadeOut();
		$('#customer_id').val(customer_id);
		$('#customer').val(text);
		setCustomerDetails(customer_id);
	}

    $('#SearchSpecies').change(function(){

        var thisval = $(this).val();
        $('#SearchCutgroups option.allsoption').hide();
        $('#SearchCutgroups option.s'+thisval).show();

		// iOS fix - display:none doesn't work on select options
		$('#SearchCutgroups option.allsoption').unwrap('span');
        $('#SearchCutgroups option.allsoption').wrap('<span/>');
        $('#SearchCutgroups option.s'+thisval).unwrap();
        //$('#SearchCutgroups').val($('#SearchCutgroups option.s'+thisval).first().attr('sid'));


        var id = $(this).val();

        //doSearch();
	});
	
	// hide cuts on load
	$('#SearchCutgroups option.allsoption').hide();
	// iOS fix - display:none doesn't work on select options
	$('#SearchCutgroups option.allsoption').wrap('<span/>');

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
		
		if(species != '' || cutgroup_id != '' && intakeID != '' || palletID != ''){
			$('#loadResults').html('<center><img src="/img/loading.gif" style="padding-top:170px;width:40px;text-align:center;"></center>');
			
			$.get("/scripts/searchPicker.php?cutgroup_id=" + cutgroup_id + "&species=" + species +  "&temperatureID=" + temperatureID +  "&palletID=" + palletID + "&intakeID=" + intakeID, function(data, status){
				$('#loadResults').html(data);
				
			});


			$('#SearchSpecies').prop('selectedIndex',0);
			$('#SearchCutgroups').prop('selectedIndex',0);
			$('#IntakeID').val('');
			$('#PalletID').val('');

			$('.allsoption').hide();
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