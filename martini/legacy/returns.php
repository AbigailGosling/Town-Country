<?php
	include('includes/frontHeader.php');
?>
<div id="top">
	<a href="menu.php" id="menu">MENU</a>
	<a href="logout.php" id="logout">LOGOUT</a>
</div>

<div class="leftPanel" style="position:relative;">
	<div class="searchContainer" style="display:inline;position:relative;">
		<input type="text" class="inputbox" id="intakeID" placeholder="Search by Intake ID"/>
		<div class="searchGo" onclick="doSearch();">Search</div>
	</div>	
	<i><b>*</b>temp - try: 1625 <b>*</b></i>
	<div id="loadResults" class="resultsContainer"></div>
</div>


<div class="clearfix"></div>
<?php 
	if(request('msg') != ''){
	?>
	<script type="text/javascript">
		alert('<?php echo request('msg');?>');
	</script>
	<?php	
	}
?>

<style type="text/css">
	
</style>
<script type="text/javascript">
	
	$(document).ready(function(){
		
	});
	 
	
	function doSearch(){
		
 		var intakeID = $('#intakeID').val();
  		
		$('#loadResults').html('<center><img src="https://zippy.gfycat.com/SkinnySeveralAsianlion.gif" style="padding-top:170px;width:40px;text-align:center;"></center>');
		
		$.get("/scripts/searchReturns.php?intakeID=" + intakeID, function(data, status){
			$('#loadResults').html(data);
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
</style>
<style type="text/css">
	.rightPanel{
		width:calc(100% - 103px);
	
		float:left;
		padding:50px;
		position:relative;
		margin-top:40px;
	}
	.leftPanel{
		width:calc(100% - 103px);
		height:100%;
		float:left;
		padding:50px;
		border:1px solid #f4f4f4;
		position:relative;
	}
	
	.leftPanel{
		background:#f2f2f2;
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
		width: calc(100% - 40px);
		min-height: 400px;
		border: 2px dashed #cacaca;
		padding: 0px;
		margin-top: 20px;
		padding-top: 14px;
	}
</style>