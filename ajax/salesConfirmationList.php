correct file<?php

	require('../functions.php');
	
	$searchterm = $_POST['searchterm'];
	
	if($searchterm != ''){
        
        # Check if any customer names match the search input
        $customerIDs = [0];
        $customerResult = mysqli_query($conn, "SELECT * FROM `customers` WHERE `businessname` LIKE '$searchterm%'");
        while($customer = mysqli_fetch_array($customerResult)){ array_push($customerIDs, $customer['id']); }
        $customerIDs = implode(',', $customerIDs);

        $pickersheetResults = mysqli_query($conn, "SELECT * FROM `pickersheets` WHERE customer_id IN ($customerIDs) || id = '$searchterm' || id LIKE '$searchterm%'");
    }else{
        $pickersheetResults = mysqli_query($conn, "SELECT * FROM `pickerSheets` ORDER BY date DESC");
    }


    
    while($picksheet = mysqli_fetch_array($pickersheetResults)){
				
        $date_purchased = date('d/m/Y', strtotime($picksheet['date']));
    ?>
        <tr><td align="center" class="pos">
            <a href="viewSalesconfirmation.php?id=<?php echo $picksheet['id']; ?>" class="intake">
                <table width="100%" border="0">
                    <tr>
                        <td width="100" align="left">ID: P-00<?php echo $picksheet['id']; ?> </td>
                        <td align="center" style="font-size: 14px;left:100px;">
                            <?php
                            
                                $customer_id = $picksheet['customer_id'];
                                $cusResult = mysqli_query($conn, "SELECT * from `customers` WHERE id='$customer_id'");
                                $customer = mysqli_fetch_array($cusResult);
                            
                            ?>
                            <?php echo $customer['businessname'] . '  <span style="text-transform:lowercase;">t/a</span>  ' . $customer['tradingas']; ?>
                        </td>
                        <td width="200" align="right"> created <?php echo $date_purchased; ?></td>
                    </tr>
                </table>
            </a>
             
            <a href="javascript:;" onclick="deleteSheet(<?php echo $picksheet['id']; ?>)" id="delete_intake">
                <i class="fa fa-times" aria-hidden="true"></i>
            </a>
            <form method="POST" action="/scripts/cancelSale.php" autocomplete="off" class="<?php echo $picksheet['id']; ?>_deleteSheetForm">
                <input type="hidden" name="pickersheet_id" value="<?php echo $picksheet['id']; ?>">
            </form>

            <div class="sendcontainer">
                <div class="active" picksheetid="<?php echo $picksheet['id']; ?>" <?php if($picksheet['sent'] == 0){ echo 'style="display:none;"'; }?>>
                    <i class="fa fa-check" aria-hidden="true"></i>
                </div>
            </div>

        </td></tr>
        <?php
    }

?>

<script>
	$(document).ready(function(){

$('.sendcontainer').click(function(){
    var value = 0;
    
    if($(this).find('.active').css('display') == 'none'){ 
        value = 1;
    }else{
        value = 0;
    }

    var picksheetid = $(this).find('.active').attr('picksheetid');
    
    $.get("/ajax/togglePicksheetSent.php?picksheet=" + picksheetid + '&status=' + value, function(data, status){
    });

    $(this).find('.active').toggle();
});
});

</script>