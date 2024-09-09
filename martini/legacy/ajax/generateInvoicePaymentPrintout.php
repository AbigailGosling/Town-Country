<?php

include_once(__DIR__.'/../functions.php');
require_once(__DIR__.'/../scripts/PDFRenderer.php');
set_time_limit(5000);
use InternalScripts\PDFRenderer;
$statementDate = time();
$fileName = 'CombinedInvoice_'.$userid.'_'.$statementDate.'.pdf';
$pathToFile = 'PDF';
$x = "INSERT into tmp_data_dump (`dump`) VALUES (?)";
$id = prepareExecuteQuery($x,'s',[request()->input('data')],true);

PDFRenderer::generatePDFfromWeb('invoicepaymentprintout.php?id='.$id,$pathToFile,$fileName);

prepareExecuteQuery("DELETE FROM tmp_data_dump WHERE id = ?",'i',[$id]);

echo join(DIRECTORY_SEPARATOR,array($pathToFile,$fileName));
?>
