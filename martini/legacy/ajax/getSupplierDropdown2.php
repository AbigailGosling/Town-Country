<?php
//	$cutX = "SELECT * FROM `supplier` WHERE `disabled` = 0 AND `name` LIKE ?";
require(__DIR__.'/../functions.php');

$user_id = $_SESSION['USER'];

$name = request()->input('searchterm');
$isSaleScreen = request()->has('salescreen');

$x = "SELECT * FROM `supplier` WHERE `disabled` = 0 AND `name` LIKE ?";
$y = prepareExecuteQuery($x,'s',['%'.$name.'%']);
$count = mysqli_num_rows($y);

?> <script>var supplierIDs =  [];</script> <?php
if($count > 0){
    while($row = mysqli_fetch_array($y)){
    ?>
    <script>supplierIDs.push(<?php echo $row['id']; ?>);</script>
    <a href="javascript:;" class="intakeCutDropdown" onclick="setSupplier('<?php echo $row['id']; ?>','<?php echo addslashes($row['name']); ?>')"><?php echo $row['name']; ?></a>
    <?php
    }
}else{
?>
<a href="javascript:;" class="intakeCutDropdown" style="border: 1px #f00f00 solid;color:#f00f00;">You must select a valid supplier!</a>
<?php
}
?>

<script type="text/javascript">
$(document).ready(function(){
$('.speciesName').click(function(){
    $(this).next('.cutsContainer').toggle();
    console.log(1);
});
});

</script>
