<?php

use Illuminate\Support\Facades\Auth;

	require(__DIR__.'/../functions.php');
	if(request()->has('pod') && request()->input('pickersheet_id') != ''){

		$id = request()->input('pickersheet_id');

        $x = "SELECT `customer_id` FROM `pickerSheets` WHERE id=? LIMIT 1";
		$y = prepareExecuteQuery($x,'i',[$id]);
        $customerID = $y->fetch_assoc()['customer_id'];

        $x = "UPDATE `customers` SET `allowPrint`=0 WHERE id=? LIMIT 1";
		$y = prepareExecuteQuery($x,'i',[$customerID]);

		$x = "UPDATE `pickerSheets` SET `deliverynote_printed` = '1', `deliverynote_printed_at` = NOW(),`deliverynote_printed_by` = ? WHERE id = ? LIMIT 1";
		$y = prepareExecuteQuery($x,'ii',[Auth::id(),$id]);

	}
?>
