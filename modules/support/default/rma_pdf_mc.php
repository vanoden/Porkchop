<?php
require THIRD_PARTY . '/autoload.php';
$page = new \Site\Page ();
$page->requireAuth();

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

// Authorization Requirements
if ($GLOBALS['_SESSION_']->customer->organization->id != $rma->item()->request->customer->organization->id && !$GLOBALS['_SESSION_']->customer->can('manage support requests')) {
	$page->addError("Permission Denied");
	return;
}
// get any values for UI, check if they exist
$rmaNumber = $rma->number () ? $rma->number () : "";
$rmaTicketNumber = $rma->item ()->ticketNumber () ? $rma->item ()->ticketNumber () : "";
$rmaCustomerFullName = $rma->item ()->request->customer ? $rma->item ()->request->customer->full_name () : "";

// get any known emails and phone numbers for PDF output
$rmaCustomerEmails = $rma->item ()->request->customer->contacts(array('type'=>'email'));
$rmaCustomerPhoneNumbers = $rma->item ()->request->customer->contacts(array('type'=>'phone'));
$rmaCustomerEmailsOutput = "";
foreach ($rmaCustomerEmails as $customerEmail) $rmaCustomerEmailsOutput .= $customerEmail->description . " : " . $customerEmail->value . "<br/>";
$rmaCustomerPhoneNumberOutput = "";
foreach ($rmaCustomerPhoneNumbers as $customerPhone) $rmaCustomerPhoneNumberOutput .= $customerPhone->description . " : " . $customerPhone->value . "<br/>";

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

if ($GLOBALS['_config']->site->https) $qrl = 'https://'.$GLOBALS['_config']->site->hostname;
else $qrl = 'http://'.$GLOBALS['_config']->site->hostname;
$qrl .= '/_support/rma_form/'.$rmaCode;

$qrcode = new \GoogleAPI\QRCode();
$qrcode->create(array('content' => $qrl,'width' => 200, 'height' => 300));
if (!$qrcode->download()) {
	print "Error downloading chart: ".$qrcode->error();
	print "<br>\n".$qrcode->url()."\n";
	error_log("QRCode Download Failed: ".$qrcode->error());
	exit;
}
error_log("QRCode Download Success: ".$qrcode->error());

$pdf->Image($qrcode->filePath(), 120, 17, 0, 0, 'PNG');
$pdf->Ln ();


// Set some content to print
$html = <<<EOD
<table style="width: 275px;  border: solid 1px #000;">
<tr><th style="background-color:grey; color:white;"><strong>Customer</strong></th></tr>
<tr><td>$rmaCustomerFullName<br/>
        $rmaCustomerOrganizationName<br/>
        <span style="font-size: 10px;">$rmaCustomerEmailsOutput $rmaCustomerPhoneNumberOutput</span>
    </td>
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
$receive_location_id = $shippingShipment->rec_location_id;

$location = new \Register\Location($receive_location_id);
$formatted_location = $location->organization()->name."<br/>".$location->name."<br/>".$location->address_1;
if (!empty($location->address_2)) $formatted_location .= "<br/>".$location->address_2;
$formatted_location .= "<br/>".$location->city.",".$location->province()->abbreviation." ".$location->zip_code;

$toLocation = new \Register\Location($shippingShipment->send_location_id);
$formatted_to = $rmaCustomerFullName."<br/>";
$formatted_to .= $rmaCustomerOrganizationName."<br/>";
$formatted_to .= $toLocation->address_1;
if (!empty($toLocation->address_2)) $formatted_to .= "<br>".$toLocation->address_2;
$formatted_to .= "<br>".$toLocation->city;
$formatted_to .= ", ".$toLocation->province()->abbreviation." ".$toLocation->zip_code;

$customer_log = new \Register\Location($rma->item ()->request->customer->organization->location->id);
$html .= "
<br/><br/><br/>
<table style=\"width:100%; font-size: 11px;\" cellspacing=\"10\" cellpadding=\"10\">
  <tr style=\"background-color:grey; color:white;\">
    <th>Repair Address</th>
    <th>Return Address</th>
  </tr>
  <tr style=\"font-size: 20px;\">
    <td style=\"border:1px dashed #000;\">{$formatted_location}</td>
    <td style=\"border:1px dashed #000;\">{$formatted_to}</td>
  </tr>
</table>";

// Print text using writeHTMLCell()
$pdf->writeHTMLCell ( 0, 0, '', '', $html, 0, 1, 0, true, '', true );

// Close and output PDF document
$pdf->Output ( $rmaNumber . '.pdf', 'I' );
exit ();

