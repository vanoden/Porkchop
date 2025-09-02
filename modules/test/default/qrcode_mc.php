<?php
	//////////////////////////////////////////////
	// TEST 3rd PARTY LIBRARY - QR CODE - RMA PDF
	//////////////////////////////////////////////
	
	require THIRD_PARTY . '/autoload.php';
	use BaconQrCode\Renderer\ImageRenderer;
	use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
	use BaconQrCode\Renderer\RendererStyle\RendererStyle;
	use BaconQrCode\Writer;
	
	// create new PDF document
	$pdf = new TCPDF ( PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false );

	// set document information
	$pdf->SetCreator ( PDF_CREATOR );
	$pdf->SetAuthor ( 'Spectros Instruments, Inc' );
	$pdf->SetTitle ( 'Spectros Instruments Return Materials Authorization Document' );
	$pdf->SetSubject ( 'Please include this with your return' );

	// set default header data
	$pdf->SetHeaderData ( '', 0, 'Spectros Instruments Return Materials Authorization Document', 'Please include this with your return', array (100,100,100 ), array (100,100,100 ) );
	$pdf->setFooterData ( array (0,64,0 ), array (0,64,128 ) );

	// set header and footer fonts
	$pdf->setHeaderFont ( Array (PDF_FONT_NAME_MAIN,'',PDF_FONT_SIZE_MAIN ) );
	$pdf->setFooterFont ( Array (PDF_FONT_NAME_DATA,'',PDF_FONT_SIZE_DATA ) );

	// set default monospaced font
	$pdf->SetDefaultMonospacedFont ( PDF_FONT_MONOSPACED );

	// set margins
	$pdf->SetMargins ( PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT );
	$pdf->SetHeaderMargin ( PDF_MARGIN_HEADER );
	$pdf->SetFooterMargin ( PDF_MARGIN_FOOTER );

	// set auto page breaks
	$pdf->SetAutoPageBreak ( TRUE, PDF_MARGIN_BOTTOM );

	// set image scale factor
	$pdf->setImageScale ( PDF_IMAGE_SCALE_RATIO );

	// set some language-dependent strings (optional)
	if (@file_exists ( dirname ( __FILE__ ) . '/lang/eng.php' )) {
		require_once (dirname ( __FILE__ ) . '/lang/eng.php');
		$pdf->setLanguageArray ( $l );
	}

	// ---------------------------------------------------------

	// set default font subsetting mode
	$pdf->setFontSubsetting ( true );

	// Set font
	$pdf->SetFont ( 'dejavusans', '', 14, '', true );

	// Add a page
	$pdf->AddPage ();

	// set text shadow effect
	$pdf->setTextShadow ( array ('enabled' => false,'depth_w' => 0.2,'depth_h' => 0.2,'color' => array (196,196,196 ),'opacity' => 1,'blend_mode' => 'Normal' ) );

	// define barcode style
	$style = array ('position' => '','align' => 'C','stretch' => false,'fitwidth' => true,'cellfitalign' => '','border' => true,'hpadding' => 'auto','vpadding' => 'auto','fgcolor' => array (0,0,0 ),'bgcolor' => false,'text' => true,'font' => 'helvetica','fontsize' => 8,'stretchtext' => 4 );

	// CODE 39 - ANSI MH10.8M-1983 - USD-3 - 3 of 9.
	$pdf->Cell ( 0, 0, 0, 0, 1 );
	$pdf->write1DBarcode ( 0, 'C39', '', '', '', 18, 0.4, $style, 'N' );

	if ($GLOBALS['_config']->site->https) $qrl = 'https://'.$GLOBALS['_config']->site->hostname;
	else $qrl = 'http://'.$GLOBALS['_config']->site->hostname;
	$qrl .= '/_support/rma_form/0';

	$renderer = new ImageRenderer(
		new RendererStyle(200),
		new ImagickImageBackEnd()
	);
	$writer = new Writer($renderer);
	$writer->writeFile($qrl, 'qrcode.png');

	$pdf->Image('qrcode.png', 120, 17, 0, 0, 'PNG');
	$pdf->Ln ();


// Set some content to print
$html = <<<EOD
<table class="table-width-275 table-border-black">
<tr><th class="bg-gray text-white"><strong>Customer</strong></th></tr>
<tr><td>Customer Name Here<br/>
        Organization Name Here<br/>
        <span class="text-size-10">Email and Phone Here</span>
    </td>
  </tr>
</table>
<br/><br/><br/><br/>
<table class="table-width-90">
  <tr>
    <th class="bg-gray text-white table-border-black"><strong>RMA #</strong></th>
    <th></th>
    <th class="bg-gray text-white table-border-black"><strong>Date Approved</strong></th>
  </tr>
  <tr>
    <td>RMA # Here</td>
    <td></td>
    <td>Date Approved Here</td>
  </tr>
</table>
<br/><br/>
<table class="table-width-90">
  <tr>
    <th class="bg-gray text-white table-border-black"><strong>Ticket #</strong></th>
    <th></th>
    <th class="bg-gray text-white table-border-black"><strong>Approved By</strong></th>
  </tr>
  <tr>
    <td>Ticket # Here</td>
    <td></td>
    <td>Approved by Name Here</td>
  </tr>
</table>
<br/><br/><br/>
<table class="table-width-100 table-border-black text-size-10">
  <tr class="bg-gray text-white">
    <th><strong>Product</strong></th>
    <th><strong>QTY</strong></th>
    <th><strong>Serial</strong></th>
    <th><strong>Description</strong></th>
    <th><strong>Condition</strong></th>
  </tr>
EOD;


$html .= "
</table>
<br/><br/><br/>
<table class='table-width-100 table-border-black'>
  <tr class='bg-gray text-white'>
    <th><u>Customer Notes</u></th>
  </tr>
  <tr>
    <td><i>Instructions Here</i></td>
  </tr>
</table>";

$html .= "
<br/><br/><br/>
<table class=\"table-width-100 text-size-11\" cellspacing=\"10\" cellpadding=\"10\">
  <tr class=\"bg-gray text-white\">
    <th>Repair Address</th>
    <th>Return Address</th>
  </tr>
  <tr class=\"text-size-20\">
    <td class=\"border-dashed-black\">Location Here</td>
    <td class=\"border-dashed-black\">To Here</td>
  </tr>
</table>";

// Print text using writeHTMLCell()
$pdf->writeHTMLCell ( 0, 0, '', '', $html, 0, 1, 0, true, '', true );

// Close and output PDF document
$pdf->Output ( 0 . '.pdf', 'I' );
unlink('qrcode.png');
exit ();

