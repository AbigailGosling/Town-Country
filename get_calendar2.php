<?PHP
	
	include_once('functions.php');
	
	function checkEvents($date){
		global $conn;
		
		$x = "SELECT * FROM `purchase_form` WHERE date_due='$date'";
		$y = mysqli_query($conn, $x);
		$count = mysqli_num_rows($y);
			
		if($count >= 1){
			return 1;
		}else{
			return 0;
		}
	}
	
	
	$d = date('d');
	$week = $_GET['w'] ?: 1;
	$month = $_GET['m'] ?: date('m');
	$year = $_GET['y'] ?: date('Y');
	
	$running_day_temp = date('w',mktime(0,0,0,$month,0,$year));
	
	if($week == 1){
		$weekOffset = ($week * 7) - 7;	
	}else if($week == 2){
		// $weekOffset = (7 - $running_day_temp) + (($week - 1)* 7);
		$weekOffset = (7 - $running_day_temp);		
	}else{
		// $weekOffset = (7 - $running_day_temp) + (($week - 1)* 7) - 7;
		$weekOffset = (7 - $running_day_temp) + (($week - 2)* 7);
		
		// $weekOffset = (7 - $running_day_temp) + ($week * 7) - 7;
	}
	
	$dayOfWeek = date('w',mktime(0,0,0,$month,1,$year));
	
	$headings = array('Mon','Tue','Wed','Thu','Fri','Sat','Sun');
	
	$running_day = date('w',mktime(0,0,0,$month,0,$year));
	$days_in_month = date('t',mktime(0,0,0,$month,1,$year));
	
	$startTime = 6; # 6am - 4pm
	$hourCount = 11;
	
	$date = date($year . '-' . $month . '-' . 1);
	
	if($weekOffset == 0){
		$running_day = date('w',mktime(0,0,0,$month,0,$year));		
	}else{ $running_day = 0; }
	

	$weekStartDate = 1;
	$weekEndDate = 7;

	$weekStartDate = $weekStartDate + $weekOffset;
	$weekEndDate = $weekEndDate + $weekOffset;
	
	
	$weeksToJump = floor(($d / 7) + ($running_day/7)); 
	$weeksToJump++;
	 
	
	if(!isset($_GET['w'])){
		
	?><script> window.location.href = 'calendar.php?m=<?php echo $month; ?>&y=<?php echo $year; ?>&w=<?php echo $weeksToJump; ?>'; </script> <?php
	}
	
?>
<div class="caltop">
	<div>
		<select name="month" class="calMonth" id="calMonth">
			<option value="1" <?php if($month == '1'){ echo 'selected'; } ?>>January</option>
			<option value="2" <?php if($month == '2'){ echo 'selected'; } ?>>Feburary</option>
			<option value="3" <?php if($month == '3'){ echo 'selected'; } ?>>March</option>
			<option value="4" <?php if($month == '4'){ echo 'selected'; } ?>>April</option>
			<option value="5" <?php if($month == '5'){ echo 'selected'; } ?>>May</option>
			<option value="6" <?php if($month == '6'){ echo 'selected'; } ?>>June</option>
			<option value="7" <?php if($month == '7'){ echo 'selected'; } ?>>July</option>
			<option value="8" <?php if($month == '8'){ echo 'selected'; } ?>>Auguest</option>
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
	</div>
	<div>
		<a href="calendar.php?m=<?php echo $month; ?>&y=<?php echo $year; ?>&w=<?php echo ($week - 1); ?>" onclick="javascript:;" class="calprev"><</a>
		<a href="calendar.php?m=<?php echo $month; ?>&y=<?php echo $year; ?>&w=<?php echo ($week + 1); ?>" onclick="javascript:;" class="calnext">></a>
	</div>
</div>
<table style="border-collapse:collapse;">
	<tr>
			<td class="calendar_head_box time"></td>
		<?php foreach($headings as $heading){  ?>
			<td class="calendar_head_box"><?php echo $heading; ?></td>
		<?php } ?>
	</tr>
	<tr>
		<td class="calendar_blank_box slim" colspan="<?php echo $running_day + 1; ?>" align="left"><?php  echo date('F Y', strtotime($date)); ?> (week <?php echo $week; ?>)</td>
		<?php for($day = $weekStartDate; $day <= ($weekEndDate - $running_day); $day++){
				
				if($day < ($days_in_month + 1)){
				$date = date($year . '-' . $month . '-' . $day);
				?><td class="calendar_box slim"><?php echo date('jS', strtotime($date)); ?> </td><?php
				}else{
				$date = date($year . '-' . $month . '-' . ($day - ($days_in_month)));
				?><td class="calendar_box slim"><?php //echo date('jS', strtotime($date)); ?> </td><?php
				}
			}
		?>
	</tr>
		<?php
			for($x = 0; $x < $hourCount; $x++){
				$number = $startTime + $x;
				$i = str_pad($number, 2, '0', STR_PAD_LEFT);
			?>
				<tr>
					<td class="calendar_box time"><?php echo $i; ?>:00 <?php if($i < 12){ echo 'AM'; }else{ echo 'PM'; } ?></td>
					<?php for($rd = 0; $rd < $running_day; $rd++){ ?>
						<td class="calendar_blank_box"></td>
					<?php } ?>
					<?php
						for($day = $weekStartDate; $day <= ($weekEndDate - $running_day); $day++){
							if($day < ($days_in_month + 1)){ # this month
							$formatedDate = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) .'-'. str_pad($day, 2, '0', STR_PAD_LEFT) . ' ' . $i . ':00:00';
							
							$xxx = "SELECT * FROM purchase_form WHERE date_due = '$formatedDate' && direct_drop = '0' && booking_ref_number != ''";
							$y = mysqli_query($conn, $xxx);
							?>
							<td class="calendar_box">
								<br/>
								<?php while($row = mysqli_fetch_array($y)){ $supplier = getSupplier($row['supplier_id']); ?>
								<a href="#delivery<?php echo str_replace(' ','_', $row['id']); ?>" data-lity>
								<?php
									echo $supplier['name'];
									if($row['haulier']) { echo '<br/>' . $row['haulier']; } 
									echo '<br/>#' . $row['booking_ref_number'];
									echo '<br/><br/>';
								?>
								</a>
								<?php } ?>
							</td>
							<?php
							}else{ # next month
							?><td class="calendar_box"><?php //echo $i; ?><?php //echo $day -($days_in_month); ?></td><?php
							}
						}
					?>
				</tr>
				<?php
 
			}
		?>   
</table>

<?php

	$x = "SELECT * FROM `purchase_form` WHERE booking_ref_number != ''";
	$y = mysqli_query($conn, $x);
	
	while($row = mysqli_fetch_array($y)){
		$purchaseid = $row['id'];
		
		$supplier = getSupplier($row['supplier_id']);
		?>
		<div id="delivery<?php echo str_replace(' ','_', $row['id']); ?>" class="deliveryBox lity-hide">
			<h2><?php echo $supplier['name']; ?></h2>
			<b>Booking Ref</b>
			<p style="margin-top:5px;">#<?php echo $row['booking_ref_number']; ?></p>
			
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
				$ty = mysqli_query($conn, $tx);
				$txcount = mysqli_num_rows($ty);
				$intake = mysqli_fetch_array($ty);
			?>
			<br/>
			<a href="createPurchase.php?id=<?php echo $purchaseid; ?>" class="btn">View Purchase</a>
			<?php				
				if($txcount > 0){
				?><a href="intake.php?id=<?php echo $intake['id']; ?>" class="btn">View Intake</a><?php
				}else{
				?><a href="newDelivery.php?purchaseid=<?php echo $purchaseid; ?>" class="btn">Create Intake</a><?php
				}
			?>
		</div>
		<?php
	}
?>

<script>
	let month = '<?php echo $month; ?>';
	let year = '<?php echo $year; ?>';
	
	$('#calMonth').change(function(){
		month = $(this).val();
		
		updateCalendar(month, year);
	});
	
	$('#calYear').change(function(){
		year = $(this).val();
		
		updateCalendar(month, year);
	});
	
	//window.location.href = "calendar.php?m=<?php echo $month; ?>&y=<?php echo $year; ?>&w=<?php echo $week + $weeksToSkip; ?>";
</script>

