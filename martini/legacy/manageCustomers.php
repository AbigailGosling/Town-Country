<?php

use App\Models\ClientAddress;
use App\Models\ClientType;
use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

	include('functions.php');
	define('DEL_SUNDAY',     1);
	define('DEL_SATURDAY',   2);
	define('DEL_FRIDAY',     4);
	define('DEL_THURSDAY',   8);
	define('DEL_WEDNESDAY', 16);
	define('DEL_TUESDAY',   32);
	define('DEL_MONDAY',    64);
	$showDisabled = 0;
	if (request()->input('showDisabled') !== null)
	{
		$showDisabled = request()->input('showDisabled');
	}
    $sites = Site::all();
?>
<!doctype html>
<html class="int">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Town &amp; Country</title>

	<link href="css/style.css" rel="stylesheet" type="text/css">
	<link href="css/lity.css" rel="stylesheet" type="text/css">
	<link href="css/font-awesome.css" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

	<script src="https://code.jquery.com/jquery-1.12.4.js"></script><script src="https://malsup.github.io/jquery.form.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<script src="js/lity.js"></script>

	<script>
	$( function() {
		$( "#datepicker" ).datepicker();
	});

    function blockSpecialChar(e) {
		var k = e.keyCode;
		return ((k > 64 && k < 91) || (k > 96 && k < 123) || k == 8  ||  k == 67 || (k >= 48 && k <= 57));
	}
	</script>
	<style>
		.transferPopup{
			display:none;
			position: fixed;
			top: 0px;
			left: 0px;
			width: 100%;
			height: 100vh;
			background-color: rgba(0,0,0,0.5);
		}

		.transferPopup-container{
			display:flex;
			align-items:center;
			justify-content: center;
			width: 100%;
			height: 100vh;
		}

		.transferPopup-content{
			background-color: #fff;
			padding:20px;
			text-align: center;
		}

		.transferPopup select{
			height:35px;
			width:300px;
		}

		.transferPopup .transferbtn{
			display: block;
			width: 300px;
			margin: 0 auto;
			margin-top: 20px;
			height: 35px;
		}
	</style>
</head>

<body class="menu">
<div id="top">
	<a href="menu.php" id="menu">MENU</a>
	<a href="logout" id="logout">LOGOUT</a>
</div>

<main style="padding-top:0px !important;">
	<?php
		if(request()->input('id') != ''){

			$id = request()->input('id');
			$x2 = "SELECT * FROM customers WHERE id = ?";
			$yy2 = prepareExecuteQuery($x2,'i',[$id]);


			$data = mysqli_fetch_assoc($yy2);
			totalOutstandingForCustomer($data['id']);
		}
	?>
	<form id="mainForm" method="POST" action="<?php if(request()->input('id') != ''){ echo 'scripts/updateCustomer.php'; } else { echo 'scripts/addCustomer.php'; } ?>">
	<input type="hidden" value="<?php echo request()->input('id'); ?>" name="id">
	<div id="customerContainer">
		<div class="box">
			<h3>Customer Details</h3>

			<table width="100%" id="customerDetails">
				<tr>
					<td class="label"><label>Business Name</label></td>
					<td><input type="text" class="input" name="businessname" style="margin-bottom:-2px;" value="<?php echo $data['businessname']; ?>"></td>
				</tr>
				<tr>
					<td class="label"><label>Trading as</label></td>
					<td><input type="text" class="input" name="tradingas" value="<?php echo $data['tradingas']; ?>"></td>
				</tr>
				<?php
                if (request()->input('id') != '') {
                    $cas = ClientAddress::where([['client_id', request()->input('id')], ['client_type', ClientType::CUSTOMER->value]])->orderBy('address_id')->get();

                } else {
                    $cas = collect();
                }
                if ($cas->count() < 9) {
                    $cas = $cas->concat(collect(array_fill(0, 9 - $cas->count(), new ClientAddress())));
                }
					foreach ($cas as $i=>$ca)
					{
						if (($ca->address_id == null && $i>0) || ($ca->address_id > 1 && $ca->address_1==""))$style1 = "display:none;";
                        if ($i == 0) {
                            $u = 0;
                        }
                        $u = $ca->address_id ?? $u + 1;
				?>
				<tr style="vertical-align: top;">
					<td class="label"><label>Delivery Address <?php echo $u; ?></label></td>
					<td>
						<input type="text" class="input" id="address<?php echo $u; ?>" name="address_1[]" value="<?php echo $ca->address_1; ?>">
                        <input type="hidden" name="address_id[]" value="<?php echo $u; ?>">
						<div style="<?php echo $style1; ?>" id="address<?php echo $u; ?>container">
 							<input type="text" class="input" name="address_2[]" value="<?php echo $ca->address_2; ?>"><br/>
							<input type="text" class="input" name="address_3[]" value="<?php echo $ca->address_3; ?>"><br/>
							<input type="text" class="input" name="address_4[]" value="<?php echo $ca->address_4; ?>">
						</div>

					</td>
				</tr>
				<tr id="address<?php echo $ca->address_id ?? $u; ?>containerPostcode" style="<?php echo $style1; ?>">
					<td class="label"><label>Postcode</label></td>
					<td><input type="text" class="input postcode" name="postcode[]" value="<?php echo $ca->postcode; ?>"></td>
				</tr>
				<tr id="address<?php echo $ca->address_id ?? $u; ?>containerNumber" style="<?php echo $style1; ?>">
					<td class="label"><label>Delivery Contact No.</label></td>
					<td><input type="text" class="input" name="address_number[]" value="<?php echo $ca->address_number; ?>"></td>
				</tr>
                <tr id="address<?php echo $ca->address_id ?? $u; ?>containerRestrictions" style="<?php echo $style1; ?>">
					<td class="label"><label>Restrictions</label></td>
					<td><input type="text" class="input" name="restrictions[]" value="<?php echo $ca->restrictions; ?>"></td>
				</tr>
                <tr id="address<?php echo $ca->address_id ?? $u; ?>site_id" style="<?php echo $style1; ?>">
					<td class="label"><label>Served By</label></td>
					<td><select class="input" name="address_site_id[]">
                            <option disabled value="">-- Please Select --</option>
                            <?php foreach ($sites as $site){ ?>
                                <option value="<?php echo $site->id; ?>" <?php if(($ca->site_id == $site->id)|| ($ca->site_id == null && $data['site_id'] == $site->id)){ echo 'selected'; } ?>><?php echo $site->name; ?></option>
                            <?php } ?>
                        </select></td>
				</tr>
				<tr height="40"><td colspan="2"></td></tr>
				<?php
					}
				?>
				<tr>
					<td class="label"><label>Name of buyer</label></td>
					<td><input type="text" class="input" name="nameofbuyer" value="<?php echo $data['nameofbuyer']; ?>"></td>
				</tr>

				<tr>
					<td class="label"><label>Contact Number</label></td>
					<td><input type="text" class="input" name="contactnumber" value="<?php echo $data['contactnumber']; ?>"></td>
				</tr>

				<tr>
					<td class="label"><label>Email</label></td>
					<td><textarea type="text" style="resize: none; width: 169px; height: 47px;" class="input" name="customer_email"><?php echo $data['customer_email']; ?></textarea></td>
				</tr>
                <tr>
					<td class="label"><label>Allow Reservation</label></td>
					<td><input type="checkbox" name="can_reserve" value="1" <?php echo ($data['can_reserve'] == 1)?"checked":""; ?>></td>
				</tr>
                <tr>
					<td class="label"><label>Petfood Customer</label></td>
					<td><input type="checkbox" name="is_petfood_customer" value="1" <?php echo ($data['is_petfood_customer'] == 1)?"checked":""; ?>></td>
				</tr>
				<tr>
					<td class="label"><label>Disable Customer</label></td>
					<td><input type="checkbox" name="disabled" value="1" <?php echo ($data['disabled'] == 1)?"checked":""; ?>></td>
				</tr>
			</table>
		</div>

		<div class="box">
			<h3>Internal use only</h3>
			<table width="100%" id="customerDetails">
				<tr>
					<td class="label"><label>ID Number</label></td>
					<td><input type="text" class="input" name="asdf" value="<?php echo $data['id']; ?>" style="background:#fff;" disabled></td>
				</tr>
				<tr>
					<td class="label"><label>Sage Number</label></td>
					<td><input type="text" class="input" name="sage_no" value="<?php echo $data['sage_no']; ?>" style="background:#fff;"></td>
				</tr>
				<tr height="40"><td colspan="2"></td></tr>
				<tr>
					<td class="label"><label>Company Reg No.</label></td>
					<td><input type="text" class="input" name="companyregno" value="<?php echo $data['companyregno']; ?>"></td>
				</tr>
				<tr style="vertical-align: top;">
					<td class="label"><label>Accounts Address</label></td>
					<td>
						<input type="text" class="input" name="accounts_address_1" value="<?php echo $data['accounts_address_1']; ?>"><br/>
						<input type="text" class="input" name="accounts_address_2" value="<?php echo $data['accounts_address_2']; ?>"><br/>
						<input type="text" class="input" name="accounts_address_3" value="<?php echo $data['accounts_address_3']; ?>"><br/>
						<input type="text" class="input" name="accounts_address_4" value="<?php echo $data['accounts_address_4']; ?>">
 					</td>
				</tr>

				<tr style="vertical-align: top;">
					<td class="label"><label>Accounts Email</label></td>
					<td>
						<textarea type="email" style="resize: none; width: 169px; height: 47px;" class="input" name="accounts_email"><?php echo $data['accounts_email']; ?></textarea><br/>
 					</td>
				</tr>

				<tr style="vertical-align: top;">
					<td class="label"><label>Accounts Comments</label></td>
					<td>
						<textarea class="input" name="accounts_comments"><?php echo $data['accounts_comments']; ?></textarea>
 					</td>
				</tr>

				<tr height="40"><td colspan="2"></td></tr>

				<tr>
					<td class="label"><label>Accounts Contact</label></td>
					<td><input type="text" class="input" name="accounts_contact" value="<?php echo $data['accounts_contact']; ?>"></td>
				</tr>
				<tr>
					<td class="label"><label>Tel Number</label></td>
					<td><input type="text" class="input" name="tel_number" value="<?php echo $data['tel_number']; ?>"></td>
				</tr>
				<tr>
					<td class="label"><label>Email</label></td>
					<td><textarea type="text" style="resize: none; width: 169px; height: 47px;" class="input" name="internal_email"><?php echo $data['internal_email']; ?></textarea></td>
				</tr>
				<tr height="40"><td colspan="2"></td></tr>
				<tr>
					<td class="label"><label>Due Warning</label></td>
					<td><input type="number" class="input" name="due_warning" min="-1" value="<?php echo $data['due_warning']; ?>"></td>
				</tr>
				<tr>
					<td class="label"><label>Insurance Terms</label></td>
					<td><input type="number" class="input" name="credit_terms" min="-1" value="<?php echo $data['credit_terms']; ?>"></td>
				</tr>
				<tr>
					<td class="label"><label>Grace Period</label></td>
					<td><input type="number" class="input" name="credit_grace" min="-1" value="<?php echo $data['credit_grace']; ?>"></td>
				</tr>
				<tr height="40"><td colspan="2"></td></tr>
				<tr>
					<td class="label"><label>Prices & Extensions</label></td>
					<td>
						<select name="pricedefault">
                            <option value="0" <?php if($data['pricedefault'] == 0 && $data != ''){ echo 'selected'; } ?>>Hide</option>
                            <option value="1" <?php if($data['pricedefault'] == 1 && $data != ''){ echo 'selected'; } ?>>Display</option>
						</select>
					</td>
				</tr>
					<tr></tr><tr></tr>
				<tr height="40"><td colspan="2"></td></tr>
				<tr>
					<td class="label"><label>Default User</label></td>
					<td>
						<select id="sales_person" name="default_salesman_id">
							<?php
                            $newUsers = User::where([["disabled",false],["is_hidden",false]]);
                            if (request()->input('id') != '') $newUsers= $newUsers->orWhere("id",$data['default_salesman_id']);
                            $newUsers = $newUsers->get()->pluck("id")->toArray();
								$_users = prepareExecuteQuery("SELECT * FROM `users` where 1 in (pages) AND `id` IN (".implode(",",$newUsers).")");

								while ($_user = mysqli_fetch_array($_users)) {
									?><option value="<?php echo $_user['id']; ?>" <?php if($data['default_salesman_id'] == $_user['id']){ echo 'selected'; } ?>><?php echo $_user['name']; ?></option><?php
								}
							?>
						</select>
					</td>
				</tr>
                <tr>
					<td class="label"><label>Default Finance Person</label></td>
					<td>
						<select id="finance_person" name="default_finance_person_id">
							<?php
                            if (request()->input('id') == '' || $data['default_finance_person_id'] == null) {
                                ?> <option value="" selected disabled></option> <?php
                            }
                            $newUsers = User::where([["disabled",false],["is_hidden",false]]);
                            if (request()->input('id') != '' && $data['default_finance_person_id'] != null) $newUsers= $newUsers->orWhere("id",$data['default_finance_person_id']);
                            $newUsers = $newUsers->get()->pluck("id")->toArray();
                            $_users = prepareExecuteQuery("SELECT * FROM `users` where `id` IN (".implode(",",$newUsers).")");

                            while ($_user = mysqli_fetch_array($_users)) {
                                ?><option value="<?php echo $_user['id']; ?>" <?php if($data['default_finance_person_id'] == $_user['id']){ echo 'selected'; } ?>><?php echo $_user['name']; ?></option><?php
                            }
							?>
						</select>
					</td>
				</tr>
                <tr height="40"><td colspan="2"></td></tr>
				<tr>
					<td class="label"><label>Default Served By</label></td>
					<td>
						<select id="site_id" name="site_id">
							<?php
								foreach ($sites as $site) {
									?><option value="<?php echo $site->id; ?>" <?php if($data['site_id'] == $site->id){ echo 'selected'; } ?>><?php echo $site->name; ?></option><?php
								}
							?>
						</select>
					</td>
				</tr>
 			</table>
		</div>
	</div>

	<div id="flexContainerTwo">
		<div class="fullbox controls">
			<table width="100%">
                <tr>
					<td class="label"><label>Override Credit Check</label></td>
					<td align="right">
						<a href="javascript:;" id="overrider" onclick="overrideSales(this,<?php echo $id; ?> )" class="override"style="background-color:<?php if($data['override'] == 0){?>red<?php }else{?>lightgreen<?php }?>"><?php if($data['override'] == 0){ ?>Disabled<?php } else { ?>Enabled<?php } ?></a>
                        <input type="hidden" id="override_hidden" name="override_hidden" value="<?php echo $data['override']?1:0; ?>">
                    </td>
				</tr>
                <tr height=""><td colspan="2"></td></tr>
                <tr>
					<td class="label"><label>Override MSP Check</label></td>
					<td align="right">
						<a href="javascript:;" id="override_cost_check" onclick="overridePriceChecks(this,<?php echo $id; ?> )" class="override"style="background-color:<?php if($data['override_cost_check'] == 0){?>red<?php }else{?>lightgreen<?php }?>"><?php if($data['override_cost_check'] == 0){ ?>Disabled<?php } else { ?>Enabled<?php } ?></a>
                        <input type="hidden" id="override_cost_check_hidden" name="override_cost_check_hidden" value="<?php echo $data['override_cost_check']?1:0; ?>">
                    </td>
				</tr>
				<tr height=""><td colspan="2"></td></tr>
				<tr>
					<td class="label"><label>Price Markup/Markdown</label></td>
					<td align="right">
						<a href="javascript:;" id="markup_enabled" onclick="markupEnabled(this,<?php echo $id; ?> )" class="override" style="background-color:<?php if($data['markup_enabled'] == 0){?>red<?php }else{?>lightgreen<?php }?>"><?php if($data['markup_enabled'] == 0){ ?>Disabled<?php } else { ?>Enabled<?php } ?></a>
                        <input type="hidden" id="markup_enabled_hidden" name="markup_enabled_hidden" value="<?php echo $data['markup_enabled']?1:0; ?>">
					</td>
				</tr>
				<tr height=""><td colspan="2"></td></tr>
				<tr>
					<td class="label"><label>Credit Rating</label></td>
					<td><input type="text" class="input" name="credit_rating" value="<?php echo number_format((double)$data['credit_rating'], 2, '.', ''); ?>"></td>
				</tr>
                <tr>
					<td class="label"><label>Insured Rating</label></td>
					<td><input type="text" class="input" name="insured_rating" value="<?php echo number_format((double)$data['insured_rating'], 2, '.', ''); ?>"></td>
				</tr>
				<tr>
					<td class="label"><label>Close to limit alert</label></td>
					<td><input type="text" class="input" name="flaguplimit" value="<?php echo number_format((double)$data['flaguplimit'], 2, '.', ''); ?>"></td>
				</tr>
				<tr>
					<td class="label"><label>Current outstanding</label></td>
					<td><input type="text" class="input" name="current_outstanding" value="<?php echo totalOutstandingForCustomer($data['id']);?>"></td>
				</tr>
				<tr>
					<td class="label"><label>Markup/Markdown Amount</label></td>
					<td><input type="number" class="input" id="markup_amount" name="markup_amount" value="<?php echo $data['markup_amount']; ?>"><label> %</label></td>
				</tr>
				<!--<tr>
					<td class="label"><label>Payments received</label></td>
					<td><input type="text" class="input" name="payment_received"></td>
				</tr>
				<tr style="vertical-align:top;">
					<td class="label"><label>Account status</label></td>
					<td>
						<?php
							$current_outstanding = (double) $data['current_outstanding'];
							$flaguplimit = (double) $data['flaguplimit'];
							$credit_rating = (double) $data['credit_rating'];

							if($current_outstanding >= $credit_rating){
							?><div class="status stop">Stop</div><?php
							}else if($current_outstanding >= $flaguplimit){
							?><div class="status closetolimit">Close to limit</div><?php
							}
						?>
					</td>
				</tr>-->
			</table>
		</div>
		<div class="fullbox controls">
			<table width="100%">
                <tr>
					<td class="label"><label>Delivery Date Overide</label></td>
					<td align="right">
						<a href="javascript:;" id="delivery_day_override" onclick="delDayOverride(this,<?php echo $id; ?> )" class="override" style="background-color:<?php if($data['delivery_day_override'] == 0){?>red<?php }else{?>lightgreen<?php }?>"><?php if($data['delivery_day_override'] == 0){ ?>Disabled<?php } else { ?>Enabled<?php } ?></a>
                        <input type="hidden" id="delivery_day_override_hidden" name="delivery_day_override_hidden" value="<?php echo $data['delivery_day_override']?1:0; ?>">
                    </td>
				</tr>
                <tr height=""><td colspan="2"></td></tr>
                <tr>
					<td class="label"><label>Next Day and Long Reservation Controls</label></td>
					<td align="right">
						<a href="javascript:;" id="check_saledate" onclick="checkSaleDate(this,<?php echo $id; ?> )" class="override" style="background-color:<?php if($data['check_saledate'] == 0){?>red<?php }else{?>lightgreen<?php }?>"><?php if($data['check_saledate'] == 0){ ?>Disabled<?php } else { ?>Enabled<?php } ?></a>
                        <input type="hidden" id="check_saledate_hidden" name="check_saledate_hidden" value="<?php echo $data['check_saledate']?1:0; ?>">
                    </td>
				</tr>
                <tr height=""><td colspan="2"></td></tr>
				<tr>
					<td class="label"><label>Monday</label></td>
					<td><input type="checkbox" id="del_monday" name="del_monday" value="1" <?php echo ($data['delivery_days'] & DEL_MONDAY)?"checked":""; ?>></td>
				</tr>
				<tr>
					<td class="label"><label>Tuesday</label></td>
					<td><input type="checkbox" id="del_tuesday" name="del_tuesday" value="1" <?php echo ($data['delivery_days'] & DEL_TUESDAY)?"checked":""; ?>></td>
				</tr>
				<tr>
					<td class="label"><label>Wednesday</label></td>
					<td><input type="checkbox" id="del_wednesday" name="del_wednesday" value="1" <?php echo ($data['delivery_days'] & DEL_WEDNESDAY)?"checked":""; ?>></td>
				</tr>
				<tr>
					<td class="label"><label>Thursday</label></td>
					<td><input type="checkbox" id="del_thursday" name="del_thursday" value="1" <?php echo ($data['delivery_days'] & DEL_THURSDAY)?"checked":""; ?>></td>
				</tr>
				<tr>
					<td class="label"><label>Friday</label></td>
					<td><input type="checkbox" id="del_friday" name="del_friday" value="1" <?php echo ($data['delivery_days'] & DEL_FRIDAY)?"checked":""; ?>></td>
				</tr>
				<tr>
					<td class="label"><label>Saturday</label></td>
					<td><input type="checkbox" id="del_saturday" name="del_saturday" value="1" <?php echo ($data['delivery_days'] & DEL_SATURDAY)?"checked":""; ?>></td>
				</tr>
				<tr>
					<td class="label"><label>Sunday</label></td>
					<td><input type="checkbox" id="del_sunday" name="del_sunday" value="1" <?php echo ($data['delivery_days'] & DEL_SUNDAY)?"checked":""; ?>></td>
				</tr>
			</table>
		</div>
	</div>
<?php if (User::find(Auth::id())->hasPermission("control_credit_enabled")) { ?>
	<div id="flexContainerTwo">
        <div class="fullbox controls">
            <table width="100%">
                <tr>
					<td class="label"><label>Credit Checking</label></td>
					<td align="right">
						<a href="javascript:;" id="credit_enabled" onclick="creditChecking(this,<?php echo $id; ?> )" class="override" style="background-color:<?php if($data['credit_enabled'] == 0){?>red<?php }else{?>lightgreen<?php }?>"><?php if($data['credit_enabled'] == 0){ ?>Disabled<?php } else { ?>Enabled<?php } ?></a>
                        <input type="hidden" id="credit_enabled_hidden" name="credit_enabled_hidden" value="<?php echo $data['credit_rating']?1:0; ?>">
					</td>
				</tr>
                <tr>
					<td class="label"><label>MSP Checking</label></td>
					<td align="right">
						<a href="javascript:;" id="cost_check_enabled" onclick="mspChecking(this,<?php echo $id; ?> )" class="override" style="background-color:<?php if($data['cost_check_enabled'] == 0){?>red<?php }else{?>lightgreen<?php }?>"><?php if($data['cost_check_enabled'] == 0){ ?>Disabled<?php } else { ?>Enabled<?php } ?></a>
                        <input type="hidden" id="cost_check_enabled_hidden" name="cost_check_enabled_hidden" value="<?php echo $data['cost_check_enabled']?1:0; ?>">
					</td>
				</tr>
            </table>
        </div>
        <div class="fullbox controls">
            <table width="100%">
                <tr>
					<td class="label"><label>Delivery Date Checks</label></td>
					<td align="right">
						<a href="javascript:;" id="delivery_day_checking" onclick="delDayEnabled(this,<?php echo $id;?>)" class="override" style="background-color:<?php if($data['delivery_day_checking'] == 0){?>red<?php }else{?>lightgreen<?php }?>"><?php if($data['delivery_day_checking'] == 0){ ?>Disabled<?php } else { ?>Enabled<?php } ?></a>
                        <input type="hidden" id="delivery_day_checking_hidden" name="delivery_day_checking_hidden" value="<?php echo $data['delivery_day_checking']?1:0; ?>">
                    </td>
				</tr>
            </table>
        </div>
    </div>
<?php } ?>
    	<div id="flexContainerTwo">

		<div class="fullbox controls">
			<table width="100%">
				<tr>
					<td>
						<?php if(request()->input('id') != ''){ ?>
							<a href="customer_soa.php?id=<?php echo $data['id']; ?>" class="update" style="color:white;background:orange;">View Statement of account</a>
						<?php } ?>
					</td>
				</tr>
			</table>
		</div>

		<div class="fullbox controls">
			<table width="100%">
				<tr>
					<td class="label"><label></label></td>
					<td style="text-align:right;">
						<a href="#" class="update" style="display:none;">Update & Save</a>
						<input type="button" onclick="mainForm()" class="update" value="Update & Save">
					</td>
				</tr>
			</table>
		</div>
	</div>

	</form>

	<Br/><BR/>

	<div id="intakelist">

		<h1 class="int">CUSTOMER LIST</h1>

		<div>
			<table>
				<tr>
					<td><input type="text" id="instantSearch" placeholder="Search.." style="width:260px;height:28px;padding-left:10px;" enterkeyhint="go"/></td>
					<td style="width:90%"></td>
					<td><input type="button" value="<?php echo ($showDisabled == 1)?"Hide":"Show"; ?> Disabled" style="width:110px;height:30px;"
						onclick='window.location.href = window.location.href.split("?")[0] + "?showDisabled=" + <?php echo ($showDisabled == 1)?0:1; ?>'/></td>
				</tr>
			</table>

		</div>
		<div id="cutAjax">

		<?php
            $_limitedView = User::find(Auth::id())->listViewableCustomers();

			$x = "SELECT * FROM `customers` WHERE `disabled`=? AND `id` IN (" . implode(',', $_limitedView) . ") ORDER BY id ASC";

			$y = prepareExecuteQuery($x,'i',[$showDisabled]);

			while($row = mysqli_fetch_array($y)){

				$customer_id = $row['id'];
				$resultsCheckPicksheets = prepareExecuteQuery("SELECT id FROM pickerSheets WHERE customer_id=?",'i',[$customer_id]);

				$existingPicksheetsCount = mysqli_num_rows($resultsCheckPicksheets);
			?>

			<table width="100%" border="0" cellpadding="0" cellspacing="0">

			<tr><td align="center" class="pos">
				<a href="manageCustomers.php?id=<?php echo $row['id']; ?>" class="intake">
					<table width="100%" border="0">
						<tr>
							<td width="100" align="left">ID: <?php echo $row['id']; ?></td>
							<td align="center" style="font-size: 18px;"><?php echo $row['businessname']; ?></td>
							<td width="100" align="right">
								<!--<a href="manageCustomers.php?id=<?php //echo $row['id']; ?>" style="right:-35px;height:40px;padding-top:6px;top:0px;" id="delete_intake"><i class="fa fa-pencil" style="padding-right:4px;" aria-hidden="true"></i></a>-->
								<a href="javascript:;" onclick="deleteRow(<?php echo $row['id']; ?>, <?php echo $existingPicksheetsCount; ?>)" style="right:-70px;height:40px;padding-top:6px;top:0px;" id="delete_intake"><i class="fa fa-trash" style="padding-right:5px;" aria-hidden="true"></i></a>
							</td>
						</tr>
					</table>
				</a>


			</td></tr>

			</table>

			<?php

			}

		?>
		</div>
	</div>

	<div class="transferPopup">
		<div class="transferPopup-container">
			<div class="transferPopup-content">
				<h2>Transfer required</h2>
				<p>There is currently <b id="transferCount"></b> picksheets connected to this customer.<br/>Please pick a new customer to transfer them.</p>
				<form id="mainForm2" method="POST" action="scripts/transferPicksheetsCustomer.php">
					<input type="hidden" name="old_customer_id" id="old_customer_id">
					<select name="new_customer_id">
						<?php
							$customers = prepareExecuteQuery("SELECT id,businessname FROM `customers` WHERE `id` IN (" . implode(',', $_limitedView) . ") ORDER BY businessname ASC");

							while($customer = mysqli_fetch_array($customers)){
								?><option value="<?php echo $customer['id']; ?>" class="transfer_customers transfer_customers_<?php echo $customer['id']; ?>"><?php echo $customer['businessname']; ?></option><?php
							}
						?>
					</select>

					<input  type="button" onclick="mainForm2()" value="Transfer picksheets" class="transferbtn">
				</form>
			</div>
		</div>
	</div>
</main>

<script type="text/javascript">
	function mainForm(){
	$('#mainForm').ajaxSubmit({headers:{'X-CSRF-TOKEN': "<?php echo csrf_token();?>"},success:mainFormSucess});
}
function mainFormSucess(){
	location.reload();
}
function mainForm2(){
	$('#mainForm2').ajaxSubmit({headers:{'X-CSRF-TOKEN': "<?php echo csrf_token();?>"},success:mainFormSucess});
}
	$.ajaxSetup({
		headers: { 'X-CSRF-TOKEN': "<?php echo csrf_token();?>" }
	});
	$(document).ready(function() {
		$("[name$=_email]").keypress(function(event) {
			if(event.which == '13') {
				return false;
			}
		});
		});
	for (var u=2;u<10;u++){
		$('#address'+u.toString()).click(function(){
			var v = $(this).attr('id').toString().replace('address','').toString().substring(0, 1);
			$('#address'+v.toString()+'container').show();
			$('#address'+v.toString()+'containerPostcode').show();
			$('#address'+v.toString()+'containerNumber').show();
            $('#address'+v.toString()+'containerRestrictions').show();
            $('#address'+v.toString()+'site_id').show();
		});
	}

	$('#instantSearch').on('keypress',function(e){
		if(e.which != 13) {
			return;
		}
		var val = $('#instantSearch').val();

		$.post('ajax/customersPageList.php',{'searchterm':val,'showDisabled':<?php echo ($showDisabled == 1)?1:0; ?>},function(data,status) {
			if (status == "success") {

				$('#cutAjax').html(data);
			}
		});
	});

	$('.transferPopup-container').click(function(e){
		if(e.target != this) return;
		$('.transferPopup').hide();

	});

	function deleteRow(id, existingPicksheetsCount){

		if(existingPicksheetsCount > 0){
			$('.transferPopup').show();
			$('#transferCount').text(existingPicksheetsCount);
			$('#old_customer_id').val(id);

			$('.transfer_customers').show();
			$('.transfer_customers_' + id).hide();

		}else{
			if(confirm('Are you sure you want to delete this?')){
				window.location.href = "scripts/deleteCustomer.php?id=" + id;
			}
		}
	}
	function overrideSales(ele, id){
		var q = $('#overrider');
		if (q.text() != "Disabled") {
			q.css("background-color","red");
			q.text("Disabled");
			setTimeout(alert,10,["Override Disabled!"]);
		}
		else {
			q.css("background-color","lightgreen");
			q.text("Enabled");
			setTimeout(alert,10,["Override Enabled!"]);
		}
		$.post("ajax/overrideSales.php",{
			id: id,
		});
 	}
    function overridePriceChecks(ele, id){
		var q = $('#override_cost_check');
		if (q.text() != "Disabled") {
            $("#override_cost_check_hidden").val("0");
			q.css("background-color","red");
			q.text("Disabled");
			setTimeout(alert,10,["Override Disabled!"]);
		}
		else {
            $("#override_cost_check_hidden").val("1");
			q.css("background-color","lightgreen");
			q.text("Enabled");
			setTimeout(alert,10,["Override Enabled!"]);
		}
		$.post("ajax/overridePriceChecks.php",{
			id: id,
		});
 	}
	 function creditChecking(ele, id){
		var q = $('#credit_enabled');
		if (q.text() != "Disabled") {
            $("#credit_enabled_hidden").val("0");
			q.css("background-color","red");
			q.text("Disabled");
			setTimeout(alert,10,["Credit Checking Disabled!"]);
		}
		else {
            $("#credit_enabled_hidden").val("1");
			q.css("background-color","lightgreen");
			q.text("Enabled");
			setTimeout(alert,10,["Credit Checking Enabled!"]);
		}
		$.post("ajax/toggleCredit.php",{
			id: id,
		});
	}
    function mspChecking(ele, id){
		var q = $('#cost_check_enabled');
		if (q.text() != "Disabled") {
            $("#cost_check_enabled_hidden").val("0");
			q.css("background-color","red");
			q.text("Disabled");
			setTimeout(alert,10,["MSP Checking Disabled!"]);
		}
		else {
            $("#cost_check_enabled_hidden").val("1");
			q.css("background-color","lightgreen");
			q.text("Enabled");
			setTimeout(alert,10,["MSP Checking Enabled!"]);
		}
		$.post("ajax/toggleCostCheck.php",{
			id: id,
		});
	}
	function markupEnabled(ele, id){
		var q = $('#markup_enabled');
		if (q.text() != "Disabled") {
            $("#markup_enabled_hidden").val("0");
			q.css("background-color","red");
			q.text("Disabled");
			setTimeout(alert,10,["Price Markup Disabled!"]);
		}
		else {
            $("#markup_enabled_hidden").val("1");
			q.css("background-color","lightgreen");
			q.text("Enabled");
			setTimeout(alert,10,["Price Markup Enabled!"]);
		}
		$.post("ajax/toggleMarkup.php",{
			id: id,
			amount: $('#markup_amount').val()
		});
	}
	function delDayEnabled(ele, id){
		var q = $('#delivery_day_checking');
		if (q.text() != "Disabled") {
            $("#delivery_day_checking_hidden").val("0");
			q.css("background-color","red");
			q.text("Disabled");
			setTimeout(alert,10,["Delivery Day Checking Disabled!"]);
		}
		else {
            $("#delivery_day_checking_hidden").val("1");
			q.css("background-color","lightgreen");
			q.text("Enabled");
			setTimeout(alert,10,["Delivery Day Checking Enabled!"]);
		}
		$.post("ajax/toggleDeliveryDate.php",{
			id: id,
			mo: $('#del_monday').is(":checked")?1:0,
			tu: $('#del_tuesday').is(":checked")?1:0,
			we: $('#del_wednesday').is(":checked")?1:0,
			th: $('#del_thrusday').is(":checked")?1:0,
			fr: $('#del_friday').is(":checked")?1:0,
			sa: $('#del_saturday').is(":checked")?1:0,
			su: $('#del_sunday').is(":checked")?1:0,
		});
	}
	function delDayOverride(ele, id){
		var q = $('#delivery_day_override');
		if (q.text() != "Disabled") {
            $("delivery_day_override_hidden").val("0");
			q.css("background-color","red");
			q.text("Disabled");
			setTimeout(alert,10,["Delivery Day Override Disabled!"]);
		}
		else {
            $("delivery_day_override_hidden").val("1");
			q.css("background-color","lightgreen");
			q.text("Enabled");
			setTimeout(alert,10,["Delivery Day Override Enabled!"]);
		}
		$.post("ajax/toggleDeliveryOverride.php",{
			id: id,
			mo: $('#del_monday').is(":checked")?1:0,
			tu: $('#del_tuesday').is(":checked")?1:0,
			we: $('#del_wednesday').is(":checked")?1:0,
			th: $('#del_thrusday').is(":checked")?1:0,
			fr: $('#del_friday').is(":checked")?1:0,
			sa: $('#del_saturday').is(":checked")?1:0,
			su: $('#del_sunday').is(":checked")?1:0,
		});
	}
    function checkSaleDate(ele, id){
		var q = $('#check_saledate');
		if (q.text() != "Disabled") {
            $("check_saledate_hidden").val("0");
			q.css("background-color","red");
			q.text("Disabled");
			setTimeout(alert,10,["Next Day and Long Reservation Controls Disabled!"]);
		}
		else {
            $("check_saledate_hidden").val("1");
			q.css("background-color","lightgreen");
			q.text("Enabled");
			setTimeout(alert,10,["Next Day and Long Reservation Controls Enabled!"]);
		}
		$.post("ajax/toggleNextDay.php",{
			id: id,
		});
	}

</script>

<div id="btm"></div>

</body>

</html>
