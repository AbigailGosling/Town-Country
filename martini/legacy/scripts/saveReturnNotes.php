<?php
	require(__DIR__.'/../functions.php');

	$supplierID = request()->input('supplier_id');
	$notes = request()->input('return_notes');
	$x = "UPDATE `supplier` SET return_notes=? WHERE id =?";
	$y = prepareExecuteQuery($x,'si',[$notes,$supplierID]);
	loggedDataChange("supplier_notes",$supplierID,$notes);
?>

<script type="text/javascript">
	window.location.href = '../supplier_return_statement.php?id=<?php echo $supplierID; ?>';
</script>

