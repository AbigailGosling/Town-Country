<?php

include_once('../functions.php');
require_once('../scripts/PDFRenderer.php');
use InternalScripts\PDFRenderer;
$statementDate = time();
$fileName = 'CombinedInvoice_'.$userid.'_'.$statementDate.'.pdf';
$pathToFile = 'PDF';

PDFRenderer::generatePDFfromWeb('invoicepaymentprintout.php?data='.$_GET['data'],$pathToFile,$fileName);

echo join(DIRECTORY_SEPARATOR,array($pathToFile,$fileName));
?>