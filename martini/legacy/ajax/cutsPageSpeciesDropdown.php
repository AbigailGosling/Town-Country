<?php
	require(__DIR__.'/../functions.php');
    ini_set("memory_limit", "-1");
    $speciesID = request()->input('species_id');

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
                <a href="ajax/cutsPageSpeciesDropdown2.php?cutid=<?php echo $cutRow['id']; ?>&speciesid=<?php echo $speciesID; ?>" id="delete_intake" style="right:-75px;" data-lity><i class="fa fa-times" aria-hidden="true"></i></a>
            <?php } ?>
        </td></tr>

    </table>
    <?php
    }
?>