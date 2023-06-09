<?php

include_once(__DIR__.'/../functions.php');
require_once(__DIR__.'/../scripts/PDFRenderer.php');
use InternalScripts\PDFRenderer;
$statementDate = time();
$fileName = 'CombinedInvoice_'.$userid.'_'.$statementDate.'.pdf';
$pathToFile = 'PDF';

PDFRenderer::generatePDFfromWeb('invoicepaymentprintout.php?data='.request()->input('data'),$pathToFile,$fileName);

echo join(DIRECTORY_SEPARATOR,array($pathToFile,$fileName));
?>