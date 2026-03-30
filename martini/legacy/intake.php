<?php

use App\Models\Cut;
use App\Models\DocType;
use App\Models\Product;
use App\Models\Site;
use App\Models\Species;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

	include('includes/frontHeader.php');
    ini_set('memory_limit','15M'); //this might kill the process - keep in mind
	$id = request()->input('id');
	$intake_id = request()->input('id');

	$intake = getIntake($id);
	$dateCreated = $intake['created_at'];
	$lastUpdated = getIntakeLastUpdated($id);
	$userX = "SELECT * FROM `users` WHERE id=?";
	$userY = prepareExecuteQuery($userX,'i',[$userid]);
	$user = mysqli_fetch_array($userY);
    $userCanChangeRRP = User::find(Auth::id())->hasPermission("update_rrp");

	$supplier = getSupplier($intake['supplier_id']);

	if(request()->input('hide') == 'true'){
		$pallet_id = request()->input('pallet_id');

		$x1 = "UPDATE `product` SET `status`='1' WHERE pallet_id=?";
		$y1 = prepareExecuteQuery($x1,'i',[$pallet_id]);

		$od = request()->input('id');

		header('location: intake.php?id='.$od);
	}

	if(request()->input('savePrices') == 'true' && (request()->user()->hasPermission("set_prices") || request()->user()->isAdmin())){
		$productids = request()->input('productid');
		$size = sizeof($productids ?? []);

		$intakeid = request()->input('intakeid');
		for($i=0;$i<$size;$i++){
			$product_id = "(" . $productids[$i] . ")";
			$cost = number_format((double)request()->input('cost')[$i],3,".",",");
			$price = number_format((double)request()->input('price')[$i],3,".",",");
            $rrp1 = number_format((double)request()->input('rrp1')[$i],3,".",",");
            $rrp2 = number_format((double)request()->input('rrp2')[$i],3,".",",");
            $rrp3 = number_format((double)request()->input('rrp3')[$i],3,".",",");
			$weightnote = request()->input('weightnote')[$i];
			if ($cost == 0) $cost = null;
			if ($price == 0) $price = null;
            if ($rrp1 == 0) $rrp1 = null;
            if ($rrp2 == 0) $rrp2 = null;
            if ($rrp3 == 0) $rrp3 = null;
            $p = Product::find($productids[$i]);
            $cacheArray[] = $p;
            $c = Cut::find($p->cut_id);
            if($cost < $price && $price != null && $cost != null)
            {
                ?>
                <script>
                    Swal.fire({
					title: "Cost of <?php echo $c->name; ?> cannot be lower than £<?php echo $price; ?>",
					text: "Please check the values and try again",
					icon: "warning",
					allowOutsideClick: false,
					allowEscapeKey: false,
					showCancelButton: false,
					showConfirmButton: false,
					showCloseButton: true
				});
                </script><?php
                continue;
            }

			if($product_id != '' && $intake['approved']==1){
				if (User::find(Auth::id())->hasPermission("viewcosts"))
				{
					$x = "UPDATE `product` SET cost=?, price=?, rrp1=?, rrp2=?, rrp3=?, weightnote=? WHERE id IN $product_id";
 					$y = prepareExecuteQuery($x,'ssssss',[$cost,$price,$rrp1,$rrp2,$rrp3,$weightnote]);
				}
				else
				{
					$x = "UPDATE `product` SET cost=?, weightnote=? WHERE id IN $product_id";
 					$y = prepareExecuteQuery($x,'ss',[$cost,$weightnote]);
				}
				foreach (explode(",",$productids[$i]) as $iProdID)
				{
					loggedDataChange('product_note',$iProdID,$weightnote);
					loggedDataChange('product_cost',$iProdID,$cost);
					if (User::find(Auth::id())->hasPermission("viewcosts"))
                    {
                        loggedDataChange('product_actual_cost',$iProdID,$price);
                        loggedDataChange('product_rrp1',$iProdID,$rrp1);
                        loggedDataChange('product_rrp2',$iProdID,$rrp2);
                        loggedDataChange('product_rrp3',$iProdID,$rrp3);
                    }
				}
			}
			else {
				$x = "UPDATE `product` SET weightnote=? WHERE id IN $product_id";
				$y = prepareExecuteQuery($x,'s',[$weightnote]);
				loggedDataChange('product_note',$product_id,$weightnote);
			}
		}
        if ($intakeid != "" && $intakeid != null)
        {
            $rowId = prepareExecuteQuery("INSERT INTO `debug_logging` (`page`,`request`,`user_id`,`session_id`,`body`) VALUES (?,?,?,?,?)",'sssss',['intake.php',$intakeid,Auth::id(),session()->getId(),json_encode($cacheArray)],true);
            pclose(popen('start /B cmd /C "php '.$artisanLocation.' run:pricechangeemail '.$rowId.' >NUL 2>NUL"', 'r'));
        }
 	}

?>

<div id="top">
	<a href="menu.php" id="menu">MENU</a>
	<a href="logout" id="logout">LOGOUT</a>
</div>

<script type="text/javascript">

	function printPallet(intake_id, pallet_id){
		var x = "printContent.php?intake_id=" + intake_id + "&pallet_id=" + pallet_id;

        window.open(x, '_blank');
	}

</script>
<style type="text/css">
	.pricetype{
		width: 80px;
		height: 30px;
	}

	.pricebox{
		outline: none;
		width: 60px;
		height: 24px;
		padding-left: 10px;
		padding-right: 10px;
	}

	.printICON span{
		font-size:18px;
		text-transform:uppercase;
		font-weight:700;
		padding-left:10px;
	}

	.printICON{
		font-size:24px !important;
	}

	.printICON:active{
		color:#3faddd;
	}
	a{
		text-decoration:none !important;
	}
</style>
<main class="int">

	<a href="javascript:;" onclick="window.history.back();" class="backbtn">< Back</a>
    <?php
        if ($intake['deleted']==1)
        {?>
            <div style="visibility:visible;
            left:20%;
            pointer-events: none;
            position:absolute;
            z-index:100;
            background:none;
            display:block;
            height:100%;
            width:115%;">
            <p style="opacity: 0.5; display: block; height: 100%;
            color:red;
            text-align:center; text-justify: inter-word;
            font-size:60px; font-weight: bold;
            transform:rotate(-60deg);
            -webkit-transform:rotate(-60deg);"><br><br>
            THIS INTAKE HAS BEEN DELETED</p>
	</div>
        <?php
        }?>
	<form style="float:right;padding-bottom:10px;display:none;" method="POST" action="markIntakeAs.php">
		<input type="text" name="intakeid" value="<?php echo $intake_id; ?>" style="display:none;">
		<select name="state">
            <option value="0">Mark as unsold</option>
			<option value="1">Mark as sold</option>
		</select>

		<input type="submit" value="SAVE">
	</form>

	<div class="overview">
		<div class="overview_block">
			<label>Intake ID</label>
			<?php echo $intake['id']; ?>
		</div>

		<?php if($intake['returned'] == 1){ ?>
		<div class="overview_block">
			<label>Customer</label>
			<form id="changeIntakeSupplierForm" method="post" action="scripts/changeIntakeSupplier.php">
                <input type="hidden" name="intake_id" value="<?php echo $intake['id']; ?>">
                <select id="changeIntakeSupplier" style="height:30px;outline:none;border:0px;width: 100%;" name="supplier_id">
                    <?php
                        $y = prepareExecuteQuery("SELECT * FROM `customers` WHERE `disabled`=0 OR `id` = ? ORDER BY `businessname` ASC",'s',[$intake['supplier_id']]);

                        while($customer = mysqli_fetch_array($y)){
                        ?><option value="<?php echo $customer['id']; ?>" <?php if($customer['id'] == $intake['supplier_id']){ echo 'selected'; } ?>><?php echo $customer['businessname']; ?></option>
                        <?php }
                    ?>
                </select>
            </form>

		</div>

		<?php }else{ ?>
		<div class="overview_block">
            <label>Supplier</label>
            <form id="changeIntakeSupplierForm" method="post" action="scripts/changeIntakeSupplier.php">
                <input type="hidden" name="intake_id" value="<?php echo $intake['id']; ?>">
                <select id="changeIntakeSupplier" style="height:30px;outline:none;border:0px;width: 100%;" name="supplier_id">
                    <?php
                        $y = prepareExecuteQuery("SELECT * FROM `supplier` ORDER BY `name` ASC");

                        while($supplier = mysqli_fetch_array($y)){
                        ?><option value="<?php echo $supplier['id']; ?>" <?php if($supplier['id'] == $intake['supplier_id']){ echo 'selected'; } ?>><?php echo $supplier['name']; ?></option>
                        <?php }
                    ?>
                </select>
            </form>
		</div>
		<?php } ?>

		<div class="overview_block">
			<label>Vehicle Registration</label>
			<span style="text-transform:uppercase;"><?php echo $intake['vehicle_reg']; ?></span>
		</div>

		<div class="overview_block">
			<label>Date Recieved</label>
			<?php
				$date_received2 = str_replace('/', '-', $intake['date_received']);
				$date_received2 = date('d/m/Y', strtotime($date_received2));

				echo $date_received2;
			?>
		</div>

		<div class="overview_block">
			<label>Vehicle Temp</label>
			<?php echo $intake['vehicle_temperature']; ?>&deg;C
		</div>

		<div class="overview_block">
			<label>Delivery Note Number</label>
			<?php if($intake['returned'] == 1){ ?>
			<form method="POST" action="scripts/changeIntakeDeliveryNoteNumber.php" class="flex">
				<input type="hidden" name="_token" value="<?php echo csrf_token();?>">
				<input type="hidden" name="intake_id" value="<?php echo $intake['id']; ?>">
				<input type="text" name="delivery_note_number" value="<?php echo $intake['delivery_note_number']; ?>" style="width:140px;">
				<input type="submit" value="Save">
			</form>
			<?php }else{
				echo $intake['delivery_note_number'];
			} ?>
		</div>

		<div class="overview_block">
			<label>Staff Name</label>
			<?php
				if(is_numeric($intake['user_id'])){
					echo getUsername($intake['user_id']);
				}else{
					echo $intake['user_id'];
				}
			?>
		</div>

		<br/><br/><br/>
		<div class="overview_block">
			<div>
				<label>Total Intake Weight</label>
				<div id="intakeTotalWeightA">0</div>
			</div>
		</div>
		<?php if($intake['security_id'] != ''){ ?>
		<div class="overview_block">
			<div>
				<label>Security</label>
				<?php echo getSecurityName($intake['security_id']); ?>
			</div>
		</div>
		<?php } ?>
		<?php if($dateCreated != '' || $lastUpdated != ''){ ?>
		<div class="overview_block">
			<div>
				<label>Date Created</label>
				<?php echo ($dateCreated!= '')?DateTime::createFromFormat('Y-m-d H:i:s',$dateCreated)->format('d/m/Y H:i:s'):"Unknown"; ?>
			</div>
		</div>
		<div class="overview_block">
			<div>
				<label>Last Updated</label>
				<?php echo ($lastUpdated!= '')?DateTime::createFromFormat('Y-m-d H:i:s',$lastUpdated)->format('d/m/Y H:i:s'):"Unknown"; ?>
			</div>
		</div>
        <?php } ?>
        <div class="overview_block">
            <div>
				<label>Original Depo</label>
				<form id="changeOriginalSiteForm" method="post" action="scripts/changeIntakeSite.php">
                    <input type="hidden" name="intake_id" value="<?php echo $intake['id']; ?>">
                    <select id="changeIntakeHealth" style="height:30px;outline:none;border:0px;width: 100%;" name="site_id">
                    <option value="-1" disabled<?php if(-1 == $intake['site_id']||null == $intake['site_id']||"" == $intake['site_id']){ echo 'selected'; } ?>>Unkown</option>
                        <?php
                            foreach (Site::all() as $site)
                            {
                                ?><option value="<?php echo $site->id; ?>" <?php if($site->id == $intake['site_id']){ echo 'selected'; } ?>><?php echo $site->name; ?></option><?php
                            }
                        ?>
                    </select>
                </form>
			</div>
		</div>
        <div class="overview_block">
			<div>
                <label>T&C Number</label>
                <form method="POST" action="scripts/changeIntakeInternalNum.php" class="flex">
                    <input type="hidden" name="_token" value="<?php echo csrf_token();?>">
                    <input type="hidden" name="intake_id" value="<?php echo $intake['id']; ?>">
                    <input type="text" name="internal_num" value="<?php echo $intake['internal_num']; ?>" style="width:140px;">
                    <input type="submit" value="Save">
                </form>
			</div>
		</div>
        <div class="overview_block">
			<div>
                <label>Health Mark</label>
                <form id="changeIntakeHealthForm" method="post" action="scripts/changeIntakeHealth.php">
                    <input type="hidden" name="intake_id" value="<?php echo $intake['id']; ?>">
                    <select id="changeIntakeHealth" style="height:30px;outline:none;border:0px;width: 100%;" name="health_id">
                    <option value="-1" disabled<?php if(-1 == $intake['health_id']||null == $intake['health_id']||"" == $intake['health_id']){ echo 'selected'; } ?>></option>
                        <?php
                            $y = prepareExecuteQuery("SELECT * FROM `health_mark` ORDER BY `name` ASC");

                            while($healthmark = mysqli_fetch_array($y)){
                            ?><option value="<?php echo $healthmark['id']; ?>" <?php if($healthmark['id'] == $intake['health_id']){ echo 'selected'; } ?> <?php if($healthmark['disabled'] == 1){ echo 'disabled'; } ?>><?php echo $healthmark['name']; ?></option>
                            <?php }
                        ?>
                    </select>
                </form>
			</div>
		</div>
        <div class="overview_block">
			<div>
                <label>Customs Import Entry</label>
                <form method="POST" action="scripts/changeIntakeImportNum.php" class="flex">
                    <input type="hidden" name="_token" value="<?php echo csrf_token();?>">
                    <input type="hidden" name="intake_id" value="<?php echo $intake['id']; ?>">
                    <input type="text" name="import_num" value="<?php echo $intake['import_num']; ?>" style="width:140px;">
                    <input type="submit" value="Save">
                </form>
			</div>
		</div>
        <div class="overview_block">
            <div>
                <label>No Open Product</label>
                <form method="POST" action="scripts/changeIntakePackagingNote.php" class="flex">
                    <input type="hidden" name="_token" value="<?php echo csrf_token();?>">
                    <input type="hidden" name="intake_id" value="<?php echo $intake['id']; ?>">
                    <input type="text" name="packaging_notes" value="<?php echo $intake['packaging_notes']; ?>" style="width:140px;">
                    <input type="submit" value="Save">
                </form>
			</div>
		</div>
		<div style="clear:both;"></div>
	</div>
	<br/><br/>
	<div style="display:flex;justify-content:space-between;flex-wrap:wrap;">
	<div style="width:45%;padding:15px;border: 1px solid grey;">
		<h2 style="font-size: 20px;">Intake Notes</h2>
		<form method="POST" action="scripts/saveIntakeNotes.php" enctype="multipart/form-data">
			<input type="hidden" name="_token" value="<?php echo csrf_token();?>">
			<input type="text" name="intakeid" value="<?php echo $intake['id']; ?>" style="display:none;">
			<label>Notes</label><br/>
			<textarea class="intakeNotes" name="notes"><?php echo $intake['notes']; ?></textarea>
			<br/><br/>
			<input type="submit" value="Save">
		</form>
	</div>

    <?php
        $x = "SELECT `id` FROM `intakeDocs` WHERE `intakeid`=?";
		$y = prepareExecuteQuery($x,'i',[$intake_id]);
        $count = mysqli_num_rows($y);

    ?>
	<div style="width:45%;padding:15px;border: 1px solid grey;">
		<h2 style="font-size: 20px;">Add Document</h2>
		<form method="POST" action="scripts/addImageToIntake.php" enctype="multipart/form-data">
			<input type="text" name="intakeid" value="<?php echo $intake['id']; ?>" style="display:none;">
			<input type="hidden" name="_token" value="<?php echo csrf_token();?>">
			<label>Document Name</label><br/>
			<input type="text" name="name" required>
			<br/><br/>

            <label>Type</label><br/>
            <select style="width:177px;height:21px;" name="type_id" id="type_id" required>
            <option disabled selected>Select...</option>
			<?php echo DocType::generateHTMLList($intake['type_id']); ?><br/>
            </select><br/><br/>

			<label>Image</label><br/>
			<input type="file" name="dfile" style="border: 1px solid #cacaca;" required><br/>

			<br/><br/>
			<input type="submit" value="Attach to intake">

		</form>
    </div>
    <?php
        $x = "SELECT * FROM `intakeDocs` WHERE intakeid=?";
		$y = prepareExecuteQuery($x,'s',[$intake_id]);
		$count = mysqli_num_rows($y);

		if($count > 0){
			?><div style="padding: 15px;border: 1px solid grey;width:100%;margin-top:40px;">
			<h2 style="font-size:20px;">Intake Documents</h2><?php
			while($row = mysqli_fetch_array($y)){
                if ($row['new_file_system']==0) {
			?>
			<a href="./scripts/deleteIntakeDoc.php?intakeid=<?php echo $intake_id; ?>&docid=<?php echo $row['id']; ?>">
				<i class="fa fa-trash" aria-hidden="true" style="text-decoration:none;font-size:24px;color:#000;"></i>
			</a> &nbsp;&nbsp;&nbsp; <a target="_blank" href="./docs/<?php echo $row['dfile']; ?>" target="_blank"><?php echo $row['name']; ?></a><?php echo " (".(DocType::find($row['type_id'])->name??"Unknown").")"; ?><br/><br/>
			<?php
                } else {
            ?>
            <a href="./scripts/deleteIntakeDoc.php?intakeid=<?php echo $intake_id; ?>&docid=<?php echo $row['id']; ?>">
                <i class="fa fa-trash" aria-hidden="true" style="text-decoration:none;font-size:24px;color:#000;"></i>
            </a> &nbsp;&nbsp;&nbsp; <a href="<?php echo route('files.view', ['file' => $row['file_id']]); ?>" target="_blank"><?php echo $row['name']; ?></a><?php echo " (".(DocType::find($row['type_id'])->name??"Unknown").")"; ?><br/><br/>
			<?php
			}
            }
			echo '</div>';
		}
	?>
	</div>
	<br/><br/>
	<?php
		if($intake['purchase_id'] != ''){
	?>
	<div style="padding:10px;padding-left: 10px;border: 1px solid grey;position:relative;">

		<a href="<?php echo $domain; ?>createPurchase.php?id=<?php echo $intake['purchase_id']; ?>" class="viewpurchase">View Purchase</a>
		<h2 style="font-size: 20px;">Purchase Notes</h2>
		<?php
			$purchase_id = $intake['purchase_id'];

			$x = "SELECT * FROM purchase_form WHERE id=?";
			$y = prepareExecuteQuery($x,'i',[$purchase_id]);

			$row = mysqli_fetch_array($y);
		?>
		<b>Comments</b>
		<p style="margin-top: 4px;"><?php echo $row['purchase_comments']; ?></p>
		<ul style="padding-left:20px;">
		<?php

			$species = explode('|', $row['species']);
			$cuts = explode('|', $row['cut']);
			$units = explode('|', $row['units']);

			$size = sizeof($species);

			for($i=0;$i<$size;$i++){
			?>
			<li><?php echo ucfirst(strtolower($species[$i] . ': ' . $cuts[$i])); ?></li>
			<?php
			}
		?>
		</ul>
	</div>
	<?php } ?>

	<br/>
	<table border="1" cellpadding="5" width="100%">
		<tr>
			<td colspan="3" align="center"><b>Intake Approval</b></td>
		</tr>
		<tr>
		<?php if ($intake['approved']==1) {?>
			<td style="width:33%"><b style="color:green">Intake Approved</b></td><td align="center" style="width:33%"><?php echo ($intake['approved_by'] && $intake['approved_by']>-1)?User::find($intake['approved_by'])->name:"Unknown";?></td><td align="right" style="width:33%"><?php echo DateTime::createFromFormat('Y-m-d H:i:s',$intake['approved_date'])->format('d/m/Y H:i:s');?></td>
		<?php } else {?>
			<td><b style="color:red">Intake Not Yet Approved</b></td><?php if (User::find(Auth::id())->hasPermission("approve_intake")) {?><td style="width:50%" align="right"><form id="approveIntake" name="approveIntake" method="POST" action="scripts/approveIntake.php"><input type="hidden" name="_token" value="<?php echo csrf_token();?>"><input type="hidden" name="intake_id" value="<?php echo $intake['id']; ?>"><input id="appButton" name="appButton" type="button" onclick="approvingIntake();" value="Approve Intake"></form></td><?php }?>
		<?php } ?>
		</tr>
	</table>
	<br/>
	<?php
		$x = "SELECT * FROM `users` WHERE id=?";
		$y = prepareExecuteQuery($x,'i',[$userid]);
		$user = mysqli_fetch_array($y);


		if($user['view_intake_prices'] == 1){
	?>
	<form method="POST" action="intake.php?savePrices=true&id=<?php echo $intake_id; ?>">
	<input type="hidden" name="_token" value="<?php echo csrf_token();?>">
	<input type="text" name="intakeid" value="<?php echo $intake_id; ?>" style="display:none;">
		<table border="1" cellpadding="5" width="100%">
			<tr>
				<td colspan="11" align="center"><b>Overview</b></td>
			</tr>
			<tr>
				<th style="background:#3faddd;">Species</th>
				<th style="background:#3faddd;">Cut</th>
				<th style="background:#3faddd;">Cases</th>
				<th style="background:#3faddd;">Comments</th>
 				<th style="background:#3faddd;">Total Weight</th>
				<?php if ($intake['approved']==1) {?>
 				<th style="background:#3faddd;">Cost</th>
				<?php if (User::find(Auth::id())->hasPermission("viewcosts")) { ?>
                <th style="background:#3faddd;">Actual Cost</th>
                <th style="background:#3faddd;">1-10</th>
                <th style="background:#3faddd;">10-35</th>
                <th style="background:#3faddd;">35+</th>
                <?php } ?>
				<?php } ?>
			</tr>
			<?php

				$x = "SELECT id FROM `pallet` WHERE intake_id=?";
				$y = prepareExecuteQuery($x,'i',[$intake_id]);
				$countPallets = mysqli_num_rows($y);

				$qPallets = '';

				while($row = mysqli_fetch_array($y)){
					$rowid = $row['id'];

					$qPallets .= " pallet_id = '$rowid' OR";
 				}

				$qPallets = substr($qPallets, 0, -2);

				if($countPallets >= 1){
					$x = "SELECT * FROM product WHERE " . $qPallets . " GROUP BY cut_id";
				}else{
					$x = "SELECT * FROM product WHERE id = 0";
				}

				$y = prepareExecuteQuery($x);
				$count = mysqli_num_rows($y);

				$totalCases = 0;
				$totalWeight = 0;
				$totalWeight = 0;
				$c = 0;

				while($row = mysqli_fetch_array($y)){
					$c++;
					$product_id = $row['id'];

					$rowcutid = $row['cut_id'];

					if($countPallets >= 1){
						$x2 = "SELECT id FROM product WHERE (" . $qPallets . ") AND cut_id='$rowcutid'";
					}else{
						// ??: What does this do?
						$x2 = "SELECT id FROM product WHERE id = 0";
					}

					$y2 = prepareExecuteQuery($x2);


					$weightthing = 0;

					// used as a reference for updating the costs
					$productIDs = [];

					while($row2 = mysqli_fetch_array($y2)){
						$rowid = $row2['id'];
						$productIDs[] = $rowid;
						$weightthing += weightFromProductID($rowid);
						$totalWeight += weightFromProductID($rowid);
						$qAppend2 .= " product_id = '$rowid' OR";
					}

					$qAppend2 = substr($qAppend2, 0, -2);
					$count2 = mysqli_num_rows($y2);

				?>
				<tr>
                    <?php
                    $cut = Cut::find($row["cut_id"]);
                    $species = Species::find($cut->species_id);
                    $showComments = $species->show_comments;
                    ?>
					<td><?php echo $species->name; ?></td>
					<td><?php echo $cut->name;?></td>
					<td align="center"><?php
							$cut_id = $row['cut_id'];
							$xk = "SELECT id FROM `weights` WHERE weight_gross > 0 AND (" . $qAppend2 . ")";
							$yk = prepareExecuteQuery($xk);

                            if($row['akg'] != ''){
                                $countQuery = prepareExecuteQuery("SELECT * FROM product WHERE " . $qPallets);
                                $theCount = mysqli_num_rows($countQuery);

                                $t_count = 0;
                                while($countRow = mysqli_fetch_array($countQuery)){
                                    $t_count += $countRow['quantity'];
                                }

                                echo $t_count . '<br/><span style="font-size:12px">Advised KG</span>';
                                $totalCases = $totalCases + $t_count;
                            }else{
								$count = mysqli_num_rows($yk);
								if ($count == 0)
								{
									$xk2 = "SELECT id FROM `weights` WHERE $qAppend2";
									$yk2 = prepareExecuteQuery($xk2);
									$count =mysqli_num_rows($yk2);
								}
                                echo $count;
                                $totalCases = $totalCases + $count;
                            }
							$qAppend2 = '';



						?>
					</td>
					<td>
                    	<textarea name="weightnote[]" class="overviewcomment" style="border:1px solid #f2f2f2;" <?php if ($showComments == false) echo "disabled";?> > <?php echo $row['weightnote']; ?></textarea>
						<?php

						?>
					</td>
 					<td align="right">
					<?php

						if($row['unit'] == 'PPC'){
							echo 'PPC';
						}else{
							$palletid = $row['pallet_id'];
							$yP = prepareExecuteQuery("SELECT id,grosspallet FROM `pallet` WHERE id=?",'i',[$palletid]);
							$pRow = mysqli_fetch_array($yP);

							if($pRow['grosspallet'] == 1){
								echo '[GT] ';
							}

							if($row['akg'] != ''){
								$t_count = 0;

								$countQuery = prepareExecuteQuery("SELECT * FROM product WHERE " . $qPallets);

								while($countRow = mysqli_fetch_array($countQuery)){
									$t_count += $countRow['akg'];
								}

								echo $t_count . ' kg';
							}else{
								echo number_format($weightthing, 3, '.', '') . ' kg';
								$weightthing = 0;
							}
						}
                        $species_id = prepareExecuteQuery("SELECT `species_id` FROM `cuts` WHERE id=?",'i',[$row['cut_id']])->fetch_assoc()['species_id'];
					?>
					<input type="text" name="productid[]" value="<?php echo implode(",",$productIDs); ?>" style="display:none;">
					</td>
					<?php if ($intake['approved']==1) {
                        $rrp1Change = ($species_id != "5" && $pRow['grosspallet'] == 0 && ($userCanChangeRRP || $row['rrp1']==null || $row['rrp1']==''))?"":"readonly";
                        $rrp2Change = ($species_id != "5" && $pRow['grosspallet'] == 0 && ($userCanChangeRRP || $row['rrp2']==null || $row['rrp2']==''))?"":"readonly";
                        $rrp3Change = ($species_id != "5" && ($userCanChangeRRP || $row['rrp3']==null || $row['rrp3']==''))?"":"readonly";
                        ?>
					<td>
						<?php if (User::find(Auth::id())->hasPermission("view_product_id_on_intake")) { ?>
							<?php echo "<div style='color:lightgray;font-size:8px;'>Prod ID: ".implode(", ",$productIDs)."</div>"; ?>
						<?php } ?>
						<input type="text" style="width: 90px;" name="cost[]" value="<?php if(empty($row['cost'])) echo ''; else echo number_format((double)$row['cost'], 3, '.', ''); ?>">
					</td>
					<?php if (User::find(Auth::id())->hasPermission("viewcosts")) { ?>
					<td style="width: 1px;">
						<?php if (User::find(Auth::id())->hasPermission("view_product_id_on_intake")) { ?>
							<?php echo "<div style='color:lightgray;font-size:8px;'>&nbsp</div>"; ?>
						<?php } ?>
						<input style="width: 90px;" type="text" name="price[]" value="<?php if(empty($row['price'])) echo ''; else echo number_format((double)$row['price'], 3, '.', ''); ?>">
					</td>
                    <td>
                        <?php if (User::find(Auth::id())->hasPermission("view_product_id_on_intake")) { ?>
							<?php echo "<div style='color:lightgray;font-size:8px;'>&nbsp</div>"; ?>
						<?php } ?>
                        <input style="width: 90px;" type="text" name="rrp1[]" <?php echo $rrp1Change; ?> value="<?php if(empty($row['rrp1'])) echo ''; else echo number_format((double)$row['rrp1'], 3, '.', ''); ?>">
                    </td>
                    <td>
                        <?php if (User::find(Auth::id())->hasPermission("view_product_id_on_intake")) { ?>
							<?php echo "<div style='color:lightgray;font-size:8px;'>&nbsp</div>"; ?>
						<?php } ?>
                        <input style="width: 90px;" type="text" name="rrp2[]" <?php echo $rrp2Change; ?> value="<?php if(empty($row['rrp2'])) echo ''; else echo number_format((double)$row['rrp2'], 3, '.', ''); ?>">
                    </td>
                    <td>
                        <?php if (User::find(Auth::id())->hasPermission("view_product_id_on_intake")) { ?>
							<?php echo "<div style='color:lightgray;font-size:8px;'>&nbsp</div>"; ?>
						<?php } ?>
                        <input style="width: 90px;" type="text" name="rrp3[]" <?php echo $rrp3Change; ?> value="<?php if(empty($row['rrp3'])) echo ''; else echo number_format((double)$row['rrp3'], 3, '.', ''); ?>">
                    </td>
					<?php } ?>
					<?php } ?>
				</tr>
			<?php } ?>
			<tr>
				<td colspan="2">Total</td>
				<td align="center"><?php echo $totalCases; ?></td>
				<?php if ($intake['approved']==1) {?>
				<td></td>
				<?php } else {?>
				<td align="right"><?php if ($intake['container_id']==null || $intake['container_id']<0 || User::find(Auth::id())->hasPermission("update_container")) { ?><input type="submit" value="Save & Update"><?php } ?></td>
				<?php }?>
				<td align="right"><?php echo number_format($totalWeight, 3, '.', ''); ?>kg</td>
				<?php if ($intake['approved']==1) {?>
				<td colspan="5" align="right"><?php if ($intake['container_id']==null || $intake['container_id']<0 || User::find(Auth::id())->hasPermission("update_container")) { ?><input type="submit" value="Save & Update"><?php } ?></td>
				<?php }?>
			</tr>
		</table>
		</form>
	<?php }else{ ?>
		<table border="1" cellpadding="5" width="100%">
			<tr>
				<td colspan="6" align="center"><b>Overview</b></td>
			</tr>
			<tr>
				<th>Species</th>
				<th>Cut</th>
				<th>Cases</th>
				<th>Comments</th>
				<th>Total Weight</th>
			</tr>
			<?php

				$x = "SELECT id FROM `pallet` WHERE intake_id=?";
				$y = prepareExecuteQuery($x,'i',[$intake_id]);
				$countPallets = mysqli_num_rows($y);

				$qPallets = '';

				while($row = mysqli_fetch_array($y)){
					$rowid = $row['id'];

					$qPallets .= " pallet_id = '$rowid' OR";
				}

				$qPallets = substr($qPallets, 0, -2);


				if($countPallets >= 1){
					$x = "SELECT * FROM product WHERE " . $qPallets . " GROUP BY cut_id";
				}else{
					$x = "SELECT * FROM product WHERE id = -999";
				}


				$y = prepareExecuteQuery($x);
				$count = mysqli_num_rows($y);

				$totalCases = 0;
				$totalWeight = 0;
				$totalWeight = 0;
				$c = 0;

				while($row = mysqli_fetch_array($y)){
					$c++;
					$product_id = $row['id'];

					$rowcutid = $row['cut_id'];

					if($countPallets >= 1){
						$x2 = "SELECT id FROM product WHERE (" . $qPallets . ") AND cut_id='$rowcutid'";
					}else{ $x2 = "SELECT id FROM product WHERE id = 0"; }

					$y2 = prepareExecuteQuery($x2);


					$weightthing = 0;
					while($row2 = mysqli_fetch_array($y2)){

                        $rowid = $row2['id'];
                        if(weightFromProductID($rowid) != 1){
						$weightthing += weightFromProductID($rowid);
						$totalWeight += weightFromProductID($rowid);
						$qAppend2 .= " product_id = '$rowid' OR";
                        }
                    }

					$qAppend2 = substr($qAppend2, 0, -2);
					$count2 = mysqli_num_rows($y2);

				?>
				<tr>
					<td><?php echo getSpeciesFromCutID($row['cut_id']); ?></td>
					<td><?php echo getCut($row['cut_id']);?></td>
					<td><?php

							$cut_id = $row['cut_id'];

							$xk = "SELECT * FROM `weights` WHERE " . $qAppend2;
							$yk = prepareExecuteQuery($xk);
							// $ykRow = mysqli_fetch_array($yk);

							$qAppend2 = '';
							echo $count = mysqli_num_rows($yk);


							$totalCases = $totalCases + $count;


						?>
					</td>
					<td style="width:100px;">
						<textarea name="comment" class="comment" productid="<?php echo $row['id'];?>" style="height:30px;width:124px;">
                            <?php echo $row['comments']; ?>
						</textarea>
					</td>
					<td align="right">
                    <?php
						if($row['unit'] == 'PPC'){
							echo 'PPC';
						}else{

							$productid = $row['id'];
							$xX = "select * from `weights` WHERE product_id =?";
							$yY = prepareExecuteQuery($xX,'i',[$productid]);

							$weightt = mysqli_fetch_array($yY);

							$original_gross = number_format($weightt['original_gross'], 2, '.', '');
							$num_cartons = number_format($weightt['number_of_cartons'], 2, '.', '');
							$pallet_tare = number_format($weightt['pallet_tare'], 2, '.', '');
							$tare_per_carton = number_format($weightt['tare_per_carton'], 2, '.', '');

							$carton_tare = $num_cartons * $tare_per_carton;

							$total_tare = $carton_tare + $pallet_tare;

							$tare = $original_gross - $total_tare;

							if($weightt['grosstare'] == 1){
								echo number_format($tare, 3, '.', '');
								$totalWeight+= $tare;
								$tare = 0;
							}else{
								echo number_format($weightthing, 3, '.', ''); $weightthing = 0;
							}
							echo 'kg';
						}
                        ?>
					</td>
				</tr>
			<?php } ?>
			<tr>
				<td colspan="2">Total</td>
				<td colspan="1"><?php echo $totalCases; ?></td>
				<td></td>
				<td align="right"><?php echo number_format($totalWeight, 3, '.', ''); ?>kg</td>
			</tr>
		</table>
	<?php } ?>

	<?php
		$xk = "SELECT * FROM product WHERE original_intake_id=?";
		$yk = prepareExecuteQuery($xk,'s',[$intake_id]);

		$counting = mysqli_num_rows($yk);

	if($counting){ ?>
		<br/>
 		<table border="1" cellpadding="5" width="100%">
			<tr>
				<td colspan="7" align="center"><b>Returned Stock Overview</b></td>
			</tr>
			<tr>
			    <th>Species</th>
                <th>Cut</th>
                <th>Cases</th>
				<th>Comments</th>
                <th>Total Weight</th>
                <th>Customer</th>
                <th>New Intake ID</th>
			</tr>
			<?php


				$x = "SELECT * FROM product WHERE original_intake_id=?";

				$y = prepareExecuteQuery($x,'s',[$intake_id]);
				$count = $counting;

				$totalCases = 0;
				$totalWeight = 0;
				$totalWeight = 0;
				$c = 0;

				while($row = mysqli_fetch_array($y)){

                    $returnedIntakeID = intakeIDfromPalletID($row['pallet_id']);

                    $xo = "SELECT id FROM `pallet` WHERE intake_id=?";
                    $yo = prepareExecuteQuery($xo,'s',["$returnedIntakeID"]);
                    $countPallets = mysqli_num_rows($yo);

                    $qPallets = array();

                    while($roow = mysqli_fetch_array($yo)){
                        $rowid = $roow['id'];
                        $qPallets[] = $rowid;
                    }


					$c++;
					$product_id = $row['id'];

					$rowcutid = $row['cut_id'];

					if($countPallets >= 1){
						$x2 = "SELECT id FROM product WHERE pallet_id IN (" . implode(",",$qPallets) . ") AND cut_id='$rowcutid'";
					}else{ $x2 = "SELECT id FROM product WHERE id = 0"; }

					$y2 = prepareExecuteQuery($x2);


					$weightthing = 0;
					$rowid = $row['id'];
					if(weightFromProductID($rowid) != 1){
					$weightthing += weightFromProductID($rowid);
					$totalWeight += weightFromProductID($rowid);
					$qAppend2 .= " product_id = '$rowid' OR";
					}

					$qAppend2 = substr($qAppend2, 0, -2);
					$count2 = mysqli_num_rows($y2);

				?>
				<tr>
					<td><?php echo getSpeciesFromCutID($row['cut_id']); ?></td>
					<td><?php echo getCut($row['cut_id']);?></td>
					<td><?php

							$cut_id = $row['cut_id'];

							$xk = "SELECT * FROM `weights` WHERE " . $qAppend2;
							$yk = prepareExecuteQuery($xk);
							// $ykRow = mysqli_fetch_array($yk);

							$qAppend2 = '';
							echo $count = mysqli_num_rows($yk);


							$totalCases = $totalCases + $count;


						?>
					</td>
					<td style="width:100px;">
						<textarea name="comment" class="comment" productid="<?php echo $row['id'];?>" style="height:30px;width:124px;">
                            <?php echo $row['comments']; ?>
						</textarea>
					</td>
					<td align="right">
                    <?php
                        $productid = $row['id'];
						$stmt = $mysqli->prepare("select * from `weights` WHERE product_id = ?");
						$stmt->bind_param('i', $productid);
                        $stmt->execute();
                        $result = $stmt->get_result();
						$weightt = $result->fetch_assoc();

                        $original_gross = number_format($weightt['original_gross'], 2, '.', '');
                        $num_cartons = number_format($weightt['number_of_cartons'], 2, '.', '');
                        $pallet_tare = number_format($weightt['pallet_tare'], 2, '.', '');
                        $tare_per_carton = number_format($weightt['tare_per_carton'], 2, '.', '');

                        $carton_tare = $num_cartons * $tare_per_carton;

                        $total_tare = $carton_tare + $pallet_tare;

                        $tare = $original_gross - $total_tare;

                        if($weightt['grosstare'] == 1){
                            echo number_format($tare, 3, '.', '');
                            $totalWeight+= $tare;
                            $tare = 0;
                        }else{
                            echo number_format($weightthing, 3, '.', ''); $weightthing = 0;
                        }

                        ?>kg
                    </td>
                    <td><?php

						$returnedIntakeID = intakeIDfromPalletID($row['pallet_id']);
						$returnedIntake = getIntake($returnedIntakeID);

						$customer = getCustomer($returnedIntake['supplier_id']);
						echo $customer['businessname'];
						?>
					</td>
					<td><a href="intake.php?id=<?php echo $returnedIntakeID; ?>"><?php echo $returnedIntakeID; ?></a></td>
				</tr>
			<?php } ?>

		</table>
 	<?php } ?>

	<br/>
	<div id="printShow">
		<table style="height:100px;width:100%;">
			<tr>
				<td style="border: 1px dashed black;"></td><td style="border: 1px dashed black;"></td><td style="border: 1px dashed black;"></td>
			</tr>
			<tr>
				<td style="border: 1px dashed black;"></td><td style="border: 1px dashed black;"></td><td style="border: 1px dashed black;"></td>
			</tr>
		</table>
	</div>
	<a href="javascript:;" class="add_product" onclick="openAddPallet(<?php echo $intake_id; ?>);">Add a Pallet</a>
 	<a href="printIntake.php?intake_id=<?php echo $intake_id; ?>" class="print_intake" >Print Intake</a>
	 <a href="printAllPallets.php?intake_id=<?php echo $intake_id; ?>" class="print_intake" >Print all pallets</a>

	<center id="hidePalletBtnContainer"><br/><br/><br/><br/><br/><div class="loadPalletBtn" id="loadPalletBtn">Load Pallets</div></center>
	<div id="ajaxContent">

	</div>
</main>
<div id="btm"></div>
<div id="box" style="display:none;">

</div>
<div id="editBox" style="display:none;">

</div>
<style>
	.ui-dialog-titlebar{
		padding-top: 20px;
		padding-bottom: 20px;
	}
</style>
<script>
	$.ajaxSetup({
		headers: { 'X-CSRF-TOKEN': "<?php echo csrf_token();?>" }
	});
    $('#changeIntakeSupplier').change(function(){
		$.ajax({ // make an AJAX request
			type: "POST",
			headers: { 'X-CSRF-TOKEN': "<?php echo csrf_token();?>" },
			url: "scripts/changeIntakeSupplier.php", // it's the URL of your component B
			data: $('#changeIntakeSupplierForm').serialize(), // serializes the form's elements
		});
    });
    $('#changeIntakeHealth').change(function(){
		$.ajax({ // make an AJAX request
			type: "POST",
			headers: { 'X-CSRF-TOKEN': "<?php echo csrf_token();?>" },
			url: "scripts/changeIntakeHealth.php", // it's the URL of your component B
			data: $('#changeIntakeHealthForm').serialize(), // serializes the form's elements
		});
    });
	$('.loadPalletBtn').click(function(){
		$('#ajaxContent').html('<center><img src="https://i.gifer.com/7plQ.gif"></center>');
		$.get( "ajax/loadPallets.php?intake_id=<?php echo $intake_id; ?> ", function( data ) {
			$('#ajaxContent').html(data);
			$('#hidePalletBtnContainer').fadeOut();
		});
	});

	$(document).ready(function(){
		var totalIntakeWeight = 0.0;
		$('#printShow').hide();
		$('.aWeight').each(function() { totalIntakeWeight = parseFloat(totalIntakeWeight) + parseFloat($(this).val()); });
		var xxD = parseFloat(<?php echo $totalWeight; ?>).toFixed(3);
		$('#intakeTotalWeightA').text(xxD + ' KG');
		$(window).on({
			'beforeprint': () => {
				$('#printShow').show();
			},
			'afterprint': () => {
				$('#printShow').hide();
			}
        });
	});
<?php if ($intake['approved']==0) {?>
    function approvingIntake(){
        $("#appButton").attr("disabled", "disabled");
        $('form#approveIntake').submit();
    }
<?php }?>
    function qc_hold(pallet_id){
        var c = ($('#qc_hold'+pallet_id).is(":checked"))?1:0;
        $.post( "ajax/toggleQCHold.php",{pallet_id:pallet_id,set_to:c});
    }
    function is_hidden(pallet_id){
        var c = ($('#is_hidden'+pallet_id).is(":checked"))?1:0;
        $.post( "ajax/togglePalletHidden.php",{pallet_id:pallet_id,set_to:c});
    }
	function editWeight(intake_id, pallet_id, product_id, weight_id){
		console.log('intake_id ' + intake_id);
		console.log('pallet_id ' + pallet_id);
		console.log('product_id ' + product_id);
		console.log('weight_id ' + weight_id);

		$(window).scrollTop(0);


		$.get( "ajax/getEditProduct.php?intake_id=" + intake_id + "&pallet_id=" + pallet_id + "&product_id=" + product_id + "&weight_id=" + weight_id, function( data ) {
			$('#editBox').html(data);
			$('#editBox').fadeIn();
		});


	}

	$('#updateIntakeButton').click(function(){

		var supplier_id = $('#supplier_id').val();
		var vehicle_reg = $('#vehicle_reg').val();
		var date_received = $('#date_received').val();
		var vehicle_temperature = $('#vehicle_temp').val();
		var delivery_note_number = $('#delivery_note_number').val();

		var good = 1;
		var msg = "";

		if(vehicle_reg == ''){
			msg = "The highlighted fields cannot be blank!";
			$('#vehicle_reg').css('border','2px solid red');
			good = 0;
		}else{
			$('#vehicle_reg').css('border','1px solid grey');
		}

		if(date_received == ''){
			msg = "The highlighted fields cannot be blank!";
			$('#date_received').css('border','2px solid red');
			good = 0;
		}else{
			$('#date_received').css('border','1px solid grey');
		}

		if(vehicle_temperature == ''){
			msg = "The highlighted fields cannot be blank!";
			$('#vehicle_temp').css('border','2px solid red');
			good = 0;
		}else{
			$('#vehicle_temperature').css('border','1px solid grey');
		}

		if(delivery_note_number == ''){
			msg = "The highlighted fields cannot be blank!";
			$('#delivery_note_number').css('border','2px solid red');
			good = 0;
		}else{
			$('#delivery_note_number').css('border','1px solid grey');
		}

		$('#msgNotice').html(msg);

		if(good == 1){
			var formName = '#updateIntakeInfo';
			var xhttp = new XMLHttpRequest();
			xhttp.open("POST", $(formName).attr('action'), true);
			xhttp.setRequestHeader('X-CSRF-TOKEN', "<?php echo csrf_token();?>");
			xhttp.send($(formName).serialize());
		}
	});

	function deleteProduct(product_id, cut_id){
		console.log(product_id);
		console.log(cut_id);
	}

	function palletDetail(id){

		$('.palletDetail-' + id).toggle();
	}


	function printIntake(intake_id){
		$.ajax({
			headers:{'X-CSRF-TOKEN': "<?php echo csrf_token();?>"},
			type: "POST",
			url: 'printIntake.php?intake_id=' + intake_id,
			type: 'get',
			success: function( response ) {

				var contents = response;
				var idname = name;

				var frame1 = document.createElement('iframe');
				frame1.name = "frame1";
				frame1.style.position = "absolute";
				frame1.style.top = "-1000000px";
				document.body.appendChild(frame1);

				var frameDoc = frame1.contentWindow ? frame1.contentWindow : frame1.contentDocument.document ? frame1.contentDocument.document : frame1.contentDocument;

				frameDoc.document.open();
				frameDoc.document.write('<html><head><meta http-equiv="Content-Type" content="text/html; charset=euc-kr"><title></title>');




				frameDoc.document.write('</head><body>');
				frameDoc.document.write(contents);
				frameDoc.document.write('</body></html>');
				frameDoc.document.close();
				setTimeout(function () {
				window.frames["frame1"].focus();
				window.frames["frame1"].print();
				document.body.removeChild(frame1);
				}, 500);
				return false;
			}
		});
	}

	function printContent(el){
		var restorepage = $('body').html();
		var printcontent = $('#' + el).clone();
		$('body').empty().html(printcontent);
		window.print();
		// $('body').html(restorepage);

		setTimeout(
			function() {
				window.location.reload(1);
			}, 10000);
	}

	function palletDetail(id){

		$('.palletDetail-' + id).toggle();
	}

	function openAddPallet(intake_id){

		$.get( "ajax/addPalletForm.php?intake_id=" + intake_id, function( data ) {
			// console.log(data);
			// $('#cut_id').html('<option></option>');
			$('#box').html(data);
		});

		// $('#add_to_pallet_id').val(pallet_id);
		// $('.add_to_pallet_id').html('0000' + pallet_id);
		$('#box').fadeIn();
	}


	function openAddtoPallet(intake_id, pallet_id){

		$(window).scrollTop(0);

		$.get( "ajax/editPalletForm.php?intake_id=" + intake_id + "&pallet_id=" + pallet_id, function( data ) {
			// console.log(data);
			// $('#cut_id').html('<option></option>');
			$('#box').html(data);
		});

		// $('#add_to_pallet_id').val(pallet_id);
		// $('.add_to_pallet_id').html('0000' + pallet_id);
		$('#box').fadeIn();
	}

	function deleteRow(intake_id, pallet_id){
		if(confirm('Are you sure you want to delete this?')){
			window.location.href = "scripts/deletePallet.php?intake_id=" + intake_id + "&pallet_id=" + pallet_id;
			// console.log(intake_id + '  ' + pallet_id);
		}
	}

	// printContent(1);

	function addProductToProduct(intake_id, pallet_id, product_id){
		$.get( "ajax/addProductToProduct.php?intake_id=" + intake_id + "&pallet_id=" + pallet_id  + "&product_id=" + product_id, function( data ) {
			$('#box').html(data);
		});

		$('#box').fadeIn();
	}

	function printContent(id){
	   $.ajax({

				type: "POST",
				url: 'printContent.php?id=' + id,
				type: 'get',
				success: function( response ) {

					  var contents = response;
					 var idname = name;

					 var frame1 = document.createElement('iframe');
					 frame1.name = "frame1";
					 frame1.style.position = "absolute";
					frame1.style.top = "-1000000px";
					document.body.appendChild(frame1);

					var frameDoc = frame1.contentWindow ? frame1.contentWindow : frame1.contentDocument.document ? frame1.contentDocument.document : frame1.contentDocument;

				frameDoc.document.open();
				frameDoc.document.write('<html><head><title></title>');

			 frameDoc.document.write('<style>table {  border-collapse: collapse;  border-spacing: 0; width:100%; margin-top:20px;} .table td, .table > tbody > tr > td, .table > tbody > tr > th, .table > tfoot > tr > td, .table > tfoot > tr > th, .table > thead > tr > td, .table > thead > tr > th{ padding:8px 18px;  } .table-bordered, .table-bordered > tbody > tr > td, .table-bordered > tbody > tr > th, .table-bordered > tfoot > tr > td, .table-bordered > tfoot > tr > th, .table-bordered > thead > tr > td, .table-bordered > thead > tr > th {     border: 1px solid #e2e2e2;} </style>');

		  // your title
		   frameDoc.document.title = "Print Content with ajax in php";


		  frameDoc.document.write('</head><body>');
				frameDoc.document.write(contents);
				frameDoc.document.write('</body></html>');
				frameDoc.document.close();
				setTimeout(function () {
					window.frames["frame1"].focus();
					window.frames["frame1"].print();
					document.body.removeChild(frame1);
				}, 500);
				return false;




				}
			});

	}



</script>

<div class="popup">
	<?php
		// $x = "SELECT * FROM `product_form` WHERE ";
	?>
</div>
</body>
</html>
<script>
	$(document).ready(function(){

		$('#closePalletPopup').click(function(){
			$('.palletnotepopup').fadeOut();
		});

		<?php if(request()->has('pallet_id')||request()->has('error')){ ?>
			$('.palletnotepopup').fadeIn();
		<?php } ?>
	});
</script>
<div class="palletnotepopup">Pallet <span class="palletidpopup"><?php echo request()->input('pallet_id'); ?></span> Noted <a href="javascript:;" class="close" id="closePalletPopup">X</a></div>


<?php
	if(request()->has('palletupdated')){
	?>
		<script>
			$(document).ready(function(){
				$('#closePalletPopup').click(function(){
					$('.palletnotepopup').fadeOut();

				});
			});
		</script>
		<div class="palletnotepopup">Pallet <?php echo request()->input('palletupdated'); ?> Updated <a href="javascript:;" class="close" id="closePalletPopup">X</a></div>
	<?php
	}
?>
<?php
	if(request()->has('error')){
        switch (request()->input('error'))
        {
            case 0:
                {
                    $errorMessage = "Cannot Approve: Intake Approval Already Started";
                    break;
                }
            case 1:
                {
                    $errorMessage = "Cannot Approve: No Pallets";
                    break;
                }
            case 2:
                {
                    $errorMessage = "Cannot Approve: Pallets have no Products";
                    break;
                }
            case 3:
                {
                    $errorMessage = "Cannot Approve: Products are missing information";
                    break;
                }
        }
	?>
		<script>
			$(document).ready(function(){
				$('#closePalletPopup').click(function(){
					$('.palletnotepopup').fadeOut();

				});
			});
		</script>
		<div class="palletnotepopup"><?php echo $errorMessage; ?> <a href="javascript:;" class="close" id="closePalletPopup">X</a></div>
	<?php
	}
?>
