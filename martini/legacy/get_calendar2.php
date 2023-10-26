<?PHP

use App\Models\User;
use Illuminate\Support\Facades\Auth;

	include_once('functions.php');
	
	function checkEvents($date){
		global $mysqli;
		
		$x = "SELECT * FROM `purchase_form` WHERE date_due=?";
		$y = prepareExecuteQuery($x,'s',[$date]);
		$count = $y->num_rows;
			
		if($count >= 1){
			return 1;
		}else{
			return 0;
		}
	}
	
	if(request()->input('temperature_id') != 0){
		$temperature_id = request()->input('temperature_id');
		
		$temperatureQueryPiece = "&& temperature_id='$temperature_id'"; 
	}else{
		$temperature_id = 0;
	}
	if (request()->has('display_col') != "" && request()->input('display_col') != "") {
		$display_col = request()->input('display_col');
	}
	else {
		$display_col = "*";
	}
	if (request()->has('site_id') != "" && request()->input('site_id') != "" && request()->input('site_id') != "*") {
		$show_site_id = request()->input('site_id');
		$siteQueryPiece = "&& site_id='$show_site_id'";
	}
	else {
		$siteQueryPiece = "";
		$show_site_id = "*";
	}
	$startTime = 4; # 4am - 8pm
	$hourCount = 17;
	$shownWeekStart = new DateTime('now');
	if (request()->has('ts')){
		$shownWeekStart->setTimestamp(request()->input('ts'));
	}
	else {
		if (request()->has('y')) $shownWeekStart->setDate(request()->input('y'),$shownWeekStart->format('m'),$shownWeekStart->format('d'));
		if (request()->has('m')) $shownWeekStart->setDate($shownWeekStart->format('Y'),request()->input('m'),$shownWeekStart->format('d'));
		else if (request()->has('w')) {
			$shownWeekStart->setDate($shownWeekStart->format('Y'),1,1);
			$shownWeekStart->modify("+ ".request()->input('w')." weeks");
		}		
	}
	while ($shownWeekStart->format('l')!="Monday"){
		$shownWeekStart->modify("-1 day");
	}
	$year = $shownWeekStart->format('Y');
	$month = $shownWeekStart->format('m');
	$week = $shownWeekStart->format('W');
?>
<div class="caltop">
	<div>
		<select name="month" class="calMonth" id="calMonth">
			<option value="1" <?php if($month == '1'){ echo 'selected'; } ?>>January</option>
			<option value="2" <?php if($month == '2'){ echo 'selected'; } ?>>February</option>
			<option value="3" <?php if($month == '3'){ echo 'selected'; } ?>>March</option>
			<option value="4" <?php if($month == '4'){ echo 'selected'; } ?>>April</option>
			<option value="5" <?php if($month == '5'){ echo 'selected'; } ?>>May</option>
			<option value="6" <?php if($month == '6'){ echo 'selected'; } ?>>June</option>
			<option value="7" <?php if($month == '7'){ echo 'selected'; } ?>>July</option>
			<option value="8" <?php if($month == '8'){ echo 'selected'; } ?>>August</option>
			<option value="9" <?php if($month == '9'){ echo 'selected'; } ?>>September</option>
			<option value="10" <?php if($month == '10'){ echo 'selected'; } ?>>October</option>
			<option value="11" <?php if($month == '11'){ echo 'selected'; } ?>>November</option>
			<option value="12" <?php if($month == '12'){ echo 'selected'; } ?>>December</option>
		</select>

		<select name="year" class="calMonth" id="calYear">
			<?php
				$yearLoop = date('Y') - 3;
				
				while($yearLoop < (date('Y') + 2)){
					$yearLoop++;
					?><option value="<?php echo $yearLoop; ?>" <?php if($year == $yearLoop){ echo 'selected'; } ?>><?php echo $yearLoop; ?></option><?php
				}
			?>
		</select>

		<select name="temperature_id" class="calMonth" id="temperature_id" style="width:150px">
			<option disabled selected>Chilled/Frozen</option>
			<option value="0" <?php if($temperature_id == 0){ echo 'selected'; } ?>>All Temps</option>
			<option value="1" <?php if($temperature_id == 1){ echo 'selected'; } ?>>Fresh</option>
			<option value="2" <?php if($temperature_id == 2){ echo 'selected'; } ?>>Frozen</option>
			<option value="3" <?php if($temperature_id == 3){ echo 'selected'; } ?>>Chilled to Frozen</option>
		</select>

		<select name="display_col" class="calMonth" id="display_col" style="width:150px">
			<option value="*" selected>Display All</option>
			<option value="supplier_id" <?php if($display_col == "supplier_id"){ echo 'selected'; } ?>>Supplier & Product</option>
			<option value="haulier" <?php if($display_col == "haulier"){ echo 'selected'; } ?>>Haulier & Booking Ref</option>
		</select>

		<select name="site_id" class="calMonth" id="site_id" style="width:150px">
			<option value="*" selected>Display All</option>
			<option value="1" <?php if($show_site_id == "1"){ echo 'selected'; } ?>>Wolverhampton</option>
			<option value="2" <?php if($show_site_id == "2"){ echo 'selected'; } ?>>Gatwick</option>
		</select>
	</div>
	<div>
		<?php
			$previousWeek = new DateTime();
			$previousWeek->setTimestamp($shownWeekStart->getTimestamp());
			$previousWeek->modify("- 7 days");
			$nextWeek = new DateTime();
			$nextWeek->setTimestamp($shownWeekStart->getTimestamp());
			$nextWeek->modify("+ 7 days");

		?>
		<a href="calendar.php?ts=<?php echo $previousWeek->getTimestamp(); ?>&temperature_id=<?php echo $temperature_id; ?>&display_col=<?php echo $display_col; ?>&site_id=<?php echo $show_site_id; ?>" onclick="javascript:;" class="calprev"><</a>
		<a href="calendar.php?ts=<?php echo $nextWeek->getTimestamp(); ?>&temperature_id=<?php echo $temperature_id; ?>&display_col=<?php echo $display_col; ?>&site_id=<?php echo $show_site_id; ?>" onclick="javascript:;" class="calnext">></a>
	</div>
</div>
<table style="border-collapse:collapse;">
	<tr>
			<td class="calendar_head_box time"></td>
		<?php 
		$headings = ["Mon","Tue","Wed","Thr","Fri","Sat","Sun"];
		foreach($headings as $heading){  ?>
			<td class="calendar_head_box"><?php echo $heading; ?></td>
		<?php } ?>
	</tr>
	<tr>
		<td class="calendar_blank_box slim" align="left"><?php echo $shownWeekStart->format('F Y'); ?></td>
		<?php
		$fakeCols = 0;
		?>
		<?php 
		$workingDate = new DateTime();
		$workingDate->setTimestamp($shownWeekStart->getTimestamp());
		do{	
			$dayNumOut = $workingDate->format('jS');
			if ($dayNumOut == "1st") {
				$dayNumOut = $dayNumOut . " ".$workingDate->format('M')."";
			}
			?><td class="calendar_box slim"><?php echo $dayNumOut; ?> </td><?php
			$workingDate->modify('+ 1 day');
		} while ($workingDate->format('l') != "Monday");
		?>
	</tr>
		<?php
			$divHeightForNoEntries = 119; //Height in pixels of Site / Day gap where no entries are present
			$divHeightIndividualEntry = 58; //Height in pixels of a single Site / Day entry

			$items = [];
			for($x = 0; $x < $hourCount; $x++){
				$number = $startTime + $x;
				$i = str_pad($number, 2, '0', STR_PAD_LEFT);
			?>
				<tr style="height: 120px">
					<td class="calendar_box time" style="background-color:<?php
					if ($i % 2 == 0) {
						echo "darkgray";				
					}
					else {
						echo "lightgray";
					}
					?>;vertical-align:top;"><?php echo $i; ?>:00 <?php if($i < 12){ echo 'AM'; }else{ echo 'PM'; } ?></td>
					<?php
						$workingDate->setTimestamp($shownWeekStart->getTimestamp());
						do{	
							$formatedDate = $workingDate->format('Y-m-d'). ' '. $i .':00:00';
							$xxx = "SELECT * FROM purchase_form WHERE date_due = ? && deleted = '0' && direct_drop = '0' && booking_ref_number != '' $temperatureQueryPiece $siteQueryPiece";
							$purchases = prepareExecuteQuery($xxx,'s',[$formatedDate]);
							$thisBatch = [];
							?>
							<td class="calendar_box" style="vertical-align:top;background-color:<?php
								if ($i % 2 == 0) {
									echo "darkgray";				
								}
								else {
									echo "lightgray";
								}
								?>;">
								<?php while($row = $purchases->fetch_assoc()){ 
									$items[] = $row;
									$thisBatch[$row["site_id"]][] = $row;
								}
								if (!array_key_exists(1,$thisBatch)&&($show_site_id == "*"||$show_site_id == "1")) $thisBatch[1]=[];
								if (!array_key_exists(2,$thisBatch)&&($show_site_id == "*"||$show_site_id == "2")) $thisBatch[2]=[];
								ksort($thisBatch);
								$hasUnknown=false;
								foreach ($thisBatch as $site_id=>$delsToSite)
								{
									switch ($site_id)
									{
										case "0":
										{
											$hasUnknown = true;
											$header = "Unknown";
											break;
										}
										case "1":
										{
											$header = "Wolverhampton";
											break;
										}
										case "2":
										{
											$header = "Gatwick";
											break;
										}
									}
									$href = "site_id=".$site_id."&date=".urlencode($formatedDate);
									$atleast1 = false;
									if ($header == "Unknown" ||(!$hasUnknown && $header == "Wolverhampton" && ($show_site_id == "*" || $show_site_id == "1"))){
									?>
										<div style="position:relative;width:100%;top:0px;height:20px;">
											<a style="color:#4C4C4C;width:100%;display:flex;padding-top:4px;" href="createPurchase.php?<?php echo $href;?>">&nbsp;<?php echo $header;?> <span style="color:#4C4C4C;width:100%;text-align:right;line-height:0.6;font-size:13pt;font-weight:bold;">+&nbsp;</span></a>
										</div>

									<?php
									} else if ($show_site_id == "*" || $show_site_id == "2"){
									?>
									<div style="position:relative;width:100%;height:20px;">
									<a style="border-top: 1px solid grey;color:#4C4C4C;width:100%;display:flex;padding-top:4px;" href="createPurchase.php?<?php echo $href;?>">&nbsp;<?php echo $header;?> <span style="color:#4C4C4C;width:100%;text-align:right;line-height:0.6;font-size:13pt;font-weight:bold;">+ &nbsp;</span></a>
									</div>
									<?php
									}
									foreach ($delsToSite as $row) {
										$divPadding++;
										$supplier = getSupplier($row['supplier_id']); ?>
										<div style="margin:2pt;background:#<?php echo ($row['temperature_id'] == 1)? "c0392b" : "2980b9"; ?>;">
										<a style="margin:2pt;color:white;min-height:3em;width:100%;display:flex;" href="#delivery<?php echo str_replace(' ','_', $row['id']); ?>" data-lity>
										<?php
											if ($display_col == "*" || $display_col == "supplier_id") 
											{ 
												echo $supplier['name'] . '<br>' ;
												echo $row['purchase_comments'] . '<br>' ;
											}
											if($row['haulier'] && ($display_col == "*" || $display_col == "haulier")) 
											{ 
												echo $row['haulier']. '<br>'; 
												echo '#' . $row['booking_ref_number'] . '<br>' ;
											} 
										?>
										</a>
										</div>
								<?php 
									}
									for ($j=0;$j<$neededRow;$j++){
										?>
										<div style="margin:4pt;min-height:3em"></div></div>
										<?php
									}

									if ($divPadding < 2) { //We need to Pad this Day / Time / Site Entry block
										if ($divPadding == 0) { //No entries - pad the empty space
											echo "<div style='position:relative;height:" . $divHeightForNoEntries . "px;'></div>";
										}
										else {
											while ($divPadding > 0) { //For each empty block - pad it!
												echo "<div style='position:relative;height:" . $divHeightIndividualEntry . "px;'></div>";
												$divPadding -=1;
											}	
											
										}
									}
								}
								$divPadding = 0;
								?>
							</td>
							<?php
							$workingDate->modify('+ 1 day');
						} while ($workingDate->format('l') != "Monday");
					?>
				</tr>
				<?php
 
			}
		?>   
</table>

<?php
	foreach($items as $row){
		$purchaseid = $row['id'];
		
		$supplier = getSupplier($row['supplier_id']);
		?>
		<div id="delivery<?php echo str_replace(' ','_', $row['id']); ?>" class="deliveryBox lity-hide">
			<h2><?php echo $supplier['name']; ?></h2>
			<b>Booking Ref</b>
			<p style="margin-top:5px;">#<?php echo $row['booking_ref_number']; ?></p>
			<?php if($row['site_id'] && $row['site_id']!=0){ ?>
				<b>Site</b>
				<p style="margin-top:5px;"><?php echo ($row['site_id'] == 1) ? "Wolverhampton" : "Gatwick" ; ?></p>
			<?php } ?>
			<?php if($row['haulier']){ ?>
				<b>Haulier</b>
				<p style="margin-top:5px;"><?php echo $row['haulier']; ?></p>
			<?php } ?>

			<?php if($row['temperature_id']){ ?>
				<b>Fresh/Frz</b>
				<p style="margin-top:5px;"><?php 
					switch ($row['temperature_id']) {
						case 1:
							echo "Fresh";
							break;
						case 2:
							echo "Frozen";
							break;
						case 3:
							echo "Fresh/Frozen";
							break;
					}

				?></p>
			<?php } ?>
			
			<b>Comments</b>
			<p style="margin-top: 4px;"><?php echo $row['purchase_comments']; ?></p>
			
			<ul style="padding-left:20px;">
				<?php
					$species = explode(',', $row['species']);
					$cuts = explode(',', $row['cut']);
					$units = explode(',', $row['units']);
					
					$size = sizeof($species);
					
					for($i=0;$i<$size;$i++){
					?>
					<li><?php echo ucfirst(strtolower($species[$i] . ' ' . $cuts[$i])); ?></li>
					<?php
					}
				?>
			</ul>
			
			<?php
				$tx = "SELECT id FROM intake WHERE purchase_id='$purchaseid'";
				$ty = prepareExecuteQuery($tx);
				$txcount = $ty->num_rows;
				$intake = $ty->fetch_assoc();
			?>
			<br/>
			<a href="createPurchase.php?id=<?php echo $purchaseid; ?>" class="btn">View Arrival</a>
			<?php if ((User::find(Auth::id()))->hasPermission("createPurchase.php")) { ?>
				<a href="scripts/deletePurchase.php?id=<?php echo $purchaseid; ?>&ts=<?php echo $shownWeekStart->getTimestamp(); ?>" class="btn">Delete Arrival</a>
			<?php } ?>
		</div>
		<?php
	}	
?>

<script>
	var year = '<?php echo $year; ?>';
	var chilled_filter = '<?php echo $temperature_id; ?>';
	var week = '<?php echo $week; ?>';
	var display_col = '<?php echo $display_col; ?>';
	var site_id = '<?php echo $show_site_id; ?>';

	$('#calMonth').change(function(){
		var month = $(this).val();
		console.log(month);
		updateCalendarByMonth(year, chilled_filter, month,display_col,site_id);
	});
	
	$('#calYear').change(function(){
		year = $(this).val();
		
		updateCalendar(year, chilled_filter, week,display_col,site_id);
	});

	$('#temperature_id').change(function(){
		chilled_filter = $(this).val();
		updateCalendar(year, chilled_filter, week,display_col,site_id);
	});

	$('#display_col').change(function(){
		display_col = $(this).val();
		updateCalendar(year, chilled_filter, week,display_col,site_id);
	});

	$('#site_id').change(function(){
		site_id = $(this).val();
		updateCalendar(year, chilled_filter, week,display_col,site_id);
	});
	

</script>

