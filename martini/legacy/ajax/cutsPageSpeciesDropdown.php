<?php
	require(__DIR__.'/../functions.php');
    ini_set("memory_limit", "-1");
    $speciesID = $mysqli->real_escape_string( request()->input('species_id'));

    $cutsArray = Array();

    $cutsResult = prepareExecuteQuery("SELECT * FROM `cuts` WHERE species_id = ? ORDER by `name` ASC",'i',[$speciesID]);

    while($cutRow = mysqli_fetch_array($cutsResult)){              
        array_push($cutsArray, $cutRow);  
    }
    foreach ($cutsArray as $cutRow) {
    ?>

    <table style="width: 100%;display: table;">
        <tr><td align="center" class="pos">

            <a href="javascript:;" class="intake" <?php if($cutRow['cutgroup_id'] == ''){ echo 'style="border:2px solid red;"'; } ?>>&nbsp;<?php echo $cutRow['name']; ?></a>
            <a href="manageCuts.php?id=<?php echo $cutRow['id']; ?>" style="right:-35px;height: 29px;" id="delete_intake"><i class="fa fa-pencil" style="padding-right:0px;" aria-hidden="true"></i></a>

            <?php if($user['user_type'] == 'A'){ ?>
                <a href="#deletePopup<?php echo $cutRow['id']; ?>" id="delete_intake" style="right:-75px;" data-lity><i class="fa fa-times" aria-hidden="true"></i></a>
            <?php } ?>
        </td></tr>

    </table>

    <div id="deletePopup<?php echo $cutRow['id']; ?>" class="lity-hide" style="background:#fff;padding:20px;text-align:center;max-width:490px;">
        <h2>Confirm</h2>
        <p>Where would you like to reassign the existing products?</p>

        <form id="mainForm" method="POST" action="scripts/reassignproductcuts.php">
        <input type="hidden" name="_token" value="<?php echo csrf_token();?>">
            <input type="hidden" value="<?php echo $cutRow['id']; ?>" name="before_cutid">
            <select style="width:100%;height:35px;" name="after_cutid" required>
                <option value="" disabled selected>Please select a cut..</option>
                <?php
                    foreach ($cutsArray as $cut) {
                        if($cut['id'] != $cutRow['id']){
                            ?><option value="<?php echo $cut['id']; ?>"><?php echo $cut['name']; ?></option><?php        
                        }
                    }
                ?>
            </select>

            <input type="submit" value="Reassign products & delete <?php echo $cutRow['name']; ?>" style="width:100%;height:35px;color:#fff;margin-top:20px;background:#3faddd;outline:none;border:0px;font-weight:bold;">
            <script>
</script>
        </form>
    </div>
    <?php
    }
?>