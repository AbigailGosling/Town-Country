<?php

use App\Models\Site;

	include_once('functions.php');
    $showPrinted = 0;
	if (request()->input('showPrinted') !== null)
	{
		$showPrinted = request()->input('showPrinted');
	}
?>
<!doctype html>
<html class="int">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Town &amp; Country</title>
	<link href="css/style.css" rel="stylesheet" type="text/css">

	<link href="css/font-awesome.css" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<script src="https://code.jquery.com/jquery-1.12.4.js"></script><script src="https://malsup.github.io/jquery.form.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

</head>
<body class="menu">
<div id="top">
	<a href="menu.php" id="menu">MENU</a>
	<a href="logout" id="logout">LOGOUT</a>
</div>
<main>
	<div id="intakelist">
		<h1 class="int">Delivery Notes</h1>
		<input type="text" id="instantSearch" placeholder="Search.." style="width:260px;height:28px;padding-left:10px;" enterkeyhint="go">
		<input type="hidden" id="toSkipCount" value="0">
		<input type="hidden" id="totalRowsCount" value="0">
        <div class="datesearchcontainer">
			<label>Date</label>
			<input type="text" id="datePicker" name="datePicker" value="<?php echo request()->input("datePicker")??""; ?>" style="width:100px;height:28px;padding-left:10px;" >

			<label>Location</label>
			<select id="location" style="width:100px;height:32px;padding-left:10px;" >
                <option value="" selected disabled>Select Location</option>
				<?php
					foreach(Site::with("locations")->get() as $site){
                        if ($site->locations->where("disabled",false)->count() == 0) continue; ?>
					<option disabled><?php echo $site->abbreviation; ?></option>
                    <?php
                        foreach($site->locations->sortBy("name",SORT_NATURAL)->where("disabled",false) as $location){ ?>
                            <option value="<?php echo $location->id; ?>" <?php echo (request()->input("location",-1)==$location->id)?"selected":""; ?>><?php echo $location->name; ?></option>
                    <?php } ?>
				<?php } ?>
            </select>
            <td><input type="button" value="<?php echo ($showPrinted == 1)?"Hide":"Show"; ?> Printed" style="width:110px;height:30px;"
						onclick='window.location.href = window.location.href.split("?")[0] + "?showPrinted=" + <?php echo ($showPrinted == 1)?0:1; ?>'/></td>
		</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0" id="intakeAjax">

		</table>
		<div class="loadMoreBtn" onclick="loadRows()">Load More</div>
    </div>
	<script type="text/javascript">
$.ajaxSetup({
		headers: { 'X-CSRF-TOKEN': "<?php echo csrf_token();?>" }
	});
		$(document).ready(function(){
            $('#datePicker').datepicker({   dateFormat: 'dd/mm/yy',
                                            onSelect:function(e) {
									            doSearch();
				                            }
                                        });
			// load initial 80 rows
			loadRows();
			$('#instantSearch').on('keypress',function(e) {
				if(e.which == 13) {
					doSearch();
				}
			});
            $('#location').on('change',doSearch);
        });
		function doSearch(){
			var searchterm = $('#instantSearch').val()??"";
            var datePicker = $('#datePicker').datepicker('getDate')??"";
            var location = $('#location').find(":selected").val()??"";
            if (searchterm == "" && datePicker == "" && location == "")
            {
                loadRows();
                return;
            }
            var request = $.ajax({
                headers:{'X-CSRF-TOKEN': "<?php echo csrf_token();?>"},
                type: "POST",
                url: "ajax/page-list/deliveryNoteList.php",
                data: {
                    searchterm: searchterm,
                    datePicker: datePicker,
                    location:   location,
                },
                dataType: "html"
            });
            request.done(function(data) {
                $('#intakeAjax').html(data);
            });
            request.fail(function(jqXHR, textStatus) {
                // alert( "Request failed: " + textStatus );
            });
		}
		function loadRows(){
			var toSkip = $('#toSkipCount').val();
            var showPrinted = <?php echo $showPrinted;?>;
			var xhttp = new XMLHttpRequest();
			xhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
				$('#intakeAjax').append(this.responseText);


				setTimeout(() => {
					var toSkip = parseInt($('#toSkipCount').val());
					var totalRowsCount = parseInt($('#totalRowsCount').val());
                    var showPrinted = <?php echo $showPrinted;?>;
					if(toSkip >= totalRowsCount){
						$('.loadMoreBtn').hide();
					}else{
						$('.loadMoreBtn').show();
					}
				}, 1000);
			}
			};

			xhttp.open("POST", "ajax/page-list/deliveryNoteList.php", true);
			xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xhttp.setRequestHeader('X-CSRF-TOKEN', "<?php echo csrf_token();?>");
			xhttp.send("toSkip=" + toSkip+"&showPrinted="+showPrinted);
		}
    </script>
</main>
<div id="btm"></div>
</body>
</html>
