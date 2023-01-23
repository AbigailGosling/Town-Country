<?PHP
	
	include_once('functions.php');
	
	function checkEvents($date){
		global $mysqli;
		
		$x = "SELECT * FROM `purchase_form` WHERE date_due=?";
		 
		
		$y = prepareExecuteQuery($x,'i',[$date]);
		$count = $y->num_rows;
			
		if($count >= 1){
			return 1;
		}else{
			return 0;
		}
	}
	
	$week = 1;
	
	$today_month = date('m');
	$today_year = date('Y');
	$displayText  = date('F Y');

	$today = date('d/m/Y');

	$id = request('id');
	
	$date = DateTime::createFromFormat('d/m/Y', $today)->format('Y-m-d');
	$date = new DateTime($date);
	$date->modify($id . ' month');
	
	$today = $date->format('d/m/Y');

	
	if($id != ''){
		$today_month  = $date->format('m');
		$today_year  = $date->format('Y');
		$displayText  = $date->format('F Y');
	}
	
	$headings = array('Mon','Tue','Wed','Thu','Fri','Sat','Sun');
	
	$running_day = date('w',mktime(0,0,0,$today_month,0,$today_year));
	$days_in_month = date('t',mktime(0,0,0,$today_month,1,$today_year));
	
	
	$startTime = 8;
	$hourCount = 12;
?>
<div class="caltop">
	<div>
		<h2><?php echo $displayText; ?></h2>
	</div>
	<div>
		<a href="javascript:;" onclick="moveCalendar('-1');" class="calprev"><</a>
		<a href="javascript:;" onclick="moveCalendar('+1');" class="calnext">></a>
	</div>
</div>
<table style="border-collapse:collapse;">
	<tr>
			<td class="calendar_head_box time">#</td>
		<?php foreach($headings as $heading){  ?>
			<td class="calendar_head_box"><?php echo $heading; ?></td>
		<?php } ?>
	</tr>
 
		<?php
			for($x = 0; $x < $hourCount; $x++){
 
				$number = $startTime + $x;
				$i = str_pad($number, 2, '0', STR_PAD_LEFT);
				  
				?>
				<tr>
					<td class="calendar_box time"><?php echo $i; ?>:00 <?php if($i < 12){ echo 'AM'; }else{ echo 'PM'; } ?></td>
					<?php for($xx = 0; $xx < $running_day; $xx++){ ?>
						<td class="calendar_blank_box"></td>
					<?php } ?>
					<?php for($day = 1; $day <= 7; $day++){ ?>
						<td class="calendar_box"><?php echo $day; ?></td>
					<?php } ?>
				</tr>
				<?php
 
			}
		?>   
</table>

<div class="eventList">
	<?php
		$start = $today_year . '-' . $today_month . '-01 00:00:00';
		$end = $today_year . '-' . $today_month . '-' . $days_in_month . ' 00:00:00';
		
		$x = "SELECT * FROM `purchase_form` WHERE date_due BETWEEN ? AND ?";
		
	
		$y = prepareExecuteQuery($x,'ss',[$start,$end]);
		
		while($row = $y->fetch_assoc()){
			$purchaseid = $row['id'];
			?>
			<div class="listedevent allevents" id="<?php echo $t = preg_replace("/[^0-9.]/", "", $row['date_due']); ?>">
				<?php
					$x2 = "SELECT id FROM `intake` WHERE purchase_id=?";
					$y2 = prepareExecuteQuery($x2,'i',[$purchaseid]);
					
					$intake = $y2->fetch_assoc();
					
					$supplier = getSupplier($row['supplier_id']);
					echo '<b style="padding-bottom:10px;display:block;">Intake ID</b> #'. $intake['id'];
					echo '<br/><br/><br/>';
					echo '<b style="padding-bottom:10px;display:block;">Supplier</b>' . $supplier['name'];
					echo '<br/><br/><br/>';
					echo '<b style="padding-bottom:10px;display:block;">Comments</b>' . $row['purchase_comments'];
				?>
			</div>
			<?php
		}
	?>
</div>