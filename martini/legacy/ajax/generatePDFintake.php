<?php
	require(__DIR__.'/../functions.php');

	ini_set('memory_limit', '1024M');
	
	require_once '../../vendor/autoload.php';
    $intake_id = request()->input('id');

/*	
	$perPage = 29;
 	$border = 0;
	
	$mpdf = new \Mpdf\Mpdf([
		'tempDir' => __DIR__ . '/../docs',
        'mode' => 'utf-8',
        'format' => [210, 297],
		'setAutoTopMargin' => 'stretch',
        'autoMarginPadding' => 0,
        'bleedMargin' => 0,
        'crossMarkMargin' => 0,
        'cropMarkMargin' => 0,
        'nonPrintMargin' => 0,
        'margBuffer' => 0,
        'collapseBlockMargins' => true,
    ]);
	
	$pageArray = array();
	
	$intake_id = request()->input('id');
 
	$header .= '<link href="https://fonts.googleapis.com/css?family=Roboto:300,400,700&display=swap" rel="stylesheet">';
	$header .= '<link href="https://fonts.googleapis.com/css?family=Handlee&display=swap" rel="stylesheet">';
	
	$css ="
		body{
			font-family: 'Roboto', sans-serif;
			margin:0px;
		}
	";
	
	$mpdf->WriteHTML($css,\Mpdf\HTMLParserMode::HEADER_CSS);
	
	
	$header .= '';
	
	$pageHeader .= '';
	
	$html = '';
	
	$pageFooter .= '';
		
	$footer = '';
	
	
	
	
	
	$mpdf->SetHTMLHeader($header);
	
	
 	
 	foreach($pageArray as $page){
		$mpdf->SetHTMLFooter($footer);
		$mpdf->AddPage();
		$mpdf->WriteHTML($pageHeader);
		$mpdf->WriteHTML($page);
		$mpdf->WriteHTML($pageFooter);
	}
	
	
   	$mpdf->SetHTMLFooter($footer);
 
 
 
 	$filename2 = 'Intake_'.$intake_id.'.pdf';
	$filename = '/home/tandcphenixdevel/public_html/PDF/' . $filename2;
	
 	
	$mpdf->Output(__DIR__."/".$filename,'F');
	
	$x = "UPDATE `intake` SET finished = 1, pdf=? WHERE id=?";
    $y = prepareExecuteQuery($x,'si',[$filename2,$intake_id]);
    
    */
 

    $opts = array(
        'http'=>array(
        'method'=>"GET",
        'header'=>"Accept-language: en\r\n" .
            "Cookie: PHPSESSID=3df2bc52da4c93967b6b52130dff8ca5\r\n"

    )
    );
      
    $context = stream_context_create($opts);
    echo $file = file_get_contents($domain . 'intake.php?id='. $intake_id, false);
    
?>

<script> //window.location.href = '<?php echo $domain; ?>intake.php?id=<?php echo $intake_id; ?>'; </script>