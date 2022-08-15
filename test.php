<?php
require_once('functions.php');
require_once('ajax/customer_soa_results_function.php');
?>
<script>console.log(<?php echo json_encode(json_encode($_SERVER)); ?>);</script>
<script>console.log(<?php echo json_encode(precredit_check(252)); ?>);</script>
<script>console.log(<?php echo json_encode(getOutstandingPicksheetTotal("17030")); ?>);</script>
<script>console.log(<?php echo json_encode(totalValueCreditedOnInvoiceID("17030")); ?>);</script>