<?php
require THIRD_PARTY . '/autoload.php';
$page = new \Site\Page ();
#$page->requireRole ( "support user" );

// get cooresponding RMA from possible input values
$rma = new \Support\Request\Item\RMA ();
$rmaId = (isset ( $_REQUEST ['id'] )) ? $_REQUEST ['id'] : 0;
$rmaCode = (isset ( $_REQUEST ['code'] )) ? $_REQUEST ['code'] : 0;
if (isset ( $GLOBALS ['_REQUEST_']->query_vars_array [0] )) $rmaCode = $GLOBALS ['_REQUEST_']->query_vars_array [0];
if ($rmaId) {
	$rma = new \Support\Request\Item\RMA ( $_REQUEST ['id'] );
} elseif ($rmaCode) {
	$rma->get ( $rmaCode );
}

// get any values for UI, check if they exist
$rmaNumber = $rma->number () ? $rma->number () : "";
$rmaTicketNumber = $rma->item ()->ticketNumber () ? $rma->item ()->ticketNumber () : "";
$rmaCustomerFullName = $rma->item ()->request->customer ? $rma->item ()->request->customer->full_name () : "";
$rmaCustomerOrganizationName = $rma->item ()->request->customer->organization->name ? $rma->item ()->request->customer->organization->name : "";
$rmaApprovedByName = $rma->approvedBy () ? $rma->approvedBy ()->full_name () : "";
$rmaDateApproved = date ( "m/d/Y", strtotime ( $rma->date_approved ) );
$rmaProductCode = $rma->item ()->product ? $rma->item ()->product->code : "";

// get the shipment in question if it exists
$shippingShipment = new \Shipping\Shipment ($rma->shipment_id);
$itemsInShipment = $shippingShipment->get_items ();
$rmaInstructions = $shippingShipment->instructions ? $shippingShipment->instructions : "NONE";

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
$pdf->Cell ( 0, 0, $rmaNumber, 0, 1 );
$pdf->write1DBarcode ( $rmaNumber, 'C39', '', '', '', 18, 0.4, $style, 'N' );

$pdf->Ln ();

// Set some content to print
$html = <<<EOD
<table style="width:90%;  border: solid 1px #000;">
  <tr>
    <th style="background-color:grey; color:white;"><strong>Customer</strong></th>
    <th style="background-color:grey; color:white;"><strong>Company</strong></th>
  </tr>
  <tr>
    <td>$rmaCustomerFullName</td>
    <td>$rmaCustomerOrganizationName</td>
  </tr>
</table>
<br/><br/><br/>
<table style="width:90%">
  <tr>
    <th style="background-color:grey; color:white; border: solid 1px #000;"><strong>RMA #</strong></th>
    <th></th>
    <th style="background-color:grey; color:white; border: solid 1px #000;"><strong>Date Approved</strong></th>
  </tr>
  <tr>
    <td>$rmaNumber</td>
    <td></td>
    <td>$rmaDateApproved</td>
  </tr>
</table>
<br/><br/>
<table style="width:90%">
  <tr>
    <th style="background-color:grey; color:white; border: solid 1px #000;"><strong>Ticket #</strong></th>
    <th></th>
    <th style="background-color:grey; color:white; border: solid 1px #000;"><strong>Approved By</strong></th>
  </tr>
  <tr>
    <td>$rmaTicketNumber</td>
    <td></td>
    <td>$rmaApprovedByName</td>
  </tr>
</table>
<br/><br/><br/>
<table style="width:100%; border: solid 1px #000; font-size: 10px;">
  <tr style="background-color:grey; color:white;">
    <th><strong>Product</strong></th>
    <th><strong>QTY</strong></th>
    <th><strong>Serial</strong></th>
    <th><strong>Description</strong></th>
    <th><strong>Condition</strong></th>
  </tr>
EOD;

foreach ( $itemsInShipment as $item ) {
	$product = new \Product\Item($item->product_id);
	$html .= "<tr>
	    <td>{$product->code}</td>
	    <td>1</td>
	    <td>{$item->serial_number}</td>
		<td>{$item->description}</td>
	    <td>{$item->condition}</td>
 	</tr>
	";
}

$html .= "
</table>
<br/><br/><br/>
<table style='width:100%; border: solid 1px #000;'>
  <tr style='background-color:grey; color:white;'>
    <th><u>Customer Notes</u></th>
  </tr>
  <tr>
    <td><i>{$rmaInstructions}</i></td>
  </tr>
</table>";

# Get Warehouse Address
$configuration = new \Site\Configuration("module/support/rma_location_id");
if ($configuration->value()) $receive_location_id = $configuration->value();
else $page->addError("Default RMA Address Not Configured");

$location = new \Register\Location($receive_location_id);
$formatted_location = $GLOBALS['_SESSION_']->company->name."<br/>Attn: Service Department<br/>".$location->address_1;
if (!empty($location->address_2)) $formatted_location .= "<br/>".$location->address_2;
$formatted_location .= "<br/>".$location->city.",".$location->province()->abbreviation." ".$location->zip_code;
$html .= "
<br/><br/><br/>
<table style=\"width:100%; font-size: 11px;\" cellspacing=\"10\" cellpadding=\"10\">
  <tr style=\"background-color:grey; color:white;\">
    <th>Shipping Label - Cut Out and Attach to Package	</th>
    <th>Shipping Label - Cut Out and Attach to Package	</th>
  </tr>
  <tr style=\"font-size: 20px;\">
    <td style=\"border:1px dashed #000;\">{$formatted_location}</td>
    <td style=\"border:1px dashed #000;\">{$formatted_location}</td>
  </tr>
</table>";

// Print text using writeHTMLCell()
$pdf->writeHTMLCell ( 0, 0, '', '', $html, 0, 1, 0, true, '', true );

// Close and output PDF document
$pdf->Output ( $rmaNumber . '.pdf', 'I' );
exit ();

