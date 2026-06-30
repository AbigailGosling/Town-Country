<?php
	$intakeID = request()->input('intakeid');
    if ($intakeID != null && $intakeID != "")
    {
        require(__DIR__.'/../functions.php');
        $notes = request()->input('notes');
        $x = "UPDATE `intake` SET notes=? WHERE id =?";
        $y = prepareExecuteQuery($x,'si',[$notes,$intakeID]);
        loggedDataChange("intake",$intakeID,$notes);
    }

?>

<script type="text/javascript">
	window.location.href = '../intake.php?id=<?php echo $intakeID; ?>';
</script>

