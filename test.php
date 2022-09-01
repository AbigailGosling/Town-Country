<?php
require_once('functions.php');
require_once('ajax/customer_soa_results_function.php');
?>
<script>console.log(<?php echo json_encode(precredit_check($_GET['cid'])); ?>);</script>
<?php
if (!isset($_GET['iid'])) return;
    foreach (explode(",",$_GET['iid']) as $iid)
    {
?>
<script>console.log(<?php echo json_encode(getOutstandingPicksheetTotal($iid)); ?>);</script>
<script>console.log(<?php echo json_encode(totalValueCreditedOnInvoiceID($iid)); ?>);</script>
<?php
    }
?>