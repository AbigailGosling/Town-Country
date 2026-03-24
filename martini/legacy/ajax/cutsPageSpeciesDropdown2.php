<?php
	require(__DIR__.'/../functions.php');
    ini_set("memory_limit", "-1");
    $cutID = request()->input('cutid');
    $speciesID = request()->input('speciesid');

    $cutsArray = Array();

    $cutsResult = prepareExecuteQuery("SELECT * FROM `cuts` WHERE `disabled` = 0 AND `species_id` = ? ORDER by `name` ASC",'i',[$speciesID]);

    while($cutRow = mysqli_fetch_array($cutsResult)){
        if ($cutID == $cutRow['id']) $thisName = $cutRow['name'];
        array_push($cutsArray, $cutRow);
    }
?>

<div id="deletePopup" class="lity-hide" style="background:#fff;padding:20px;text-align:center;height:485px;">
    <h2>Confirm</h2>
    <p>Where would you like to reassign the existing products?</p>

    <form id="mainForm" method="POST" action="../scripts/reassignproductcuts.php">
    <input type="hidden" name="_token" value="<?php echo csrf_token();?>">
        <input type="hidden" value="<?php echo $cutID; ?>" name="before_cutid">
        <select style="width:100%;height:35px;" name="after_cutid" required>
            <option value="" disabled selected>Please select a cut..</option>
            <?php
                foreach ($cutsArray as $cut) {
                    if($cut['id'] != $cutID){
                        ?><option value="<?php echo $cut['id']; ?>"><?php echo $cut['name']; ?></option><?php
                    }
                }
            ?>
        </select>

        <input type="submit" value="Reassign products & delete <?php echo $thisName; ?>" style="width:100%;height:35px;color:#fff;margin-top:20px;background:#3faddd;outline:none;border:0px;font-weight:bold;">
    </form>
</div>
