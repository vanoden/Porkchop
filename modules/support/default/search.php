<div class="breadcrumbs">
	<a href="/_support/requests">Support Home</a>
	<a href="/_support/requests">Search</a>
</div>
<h2 style="display: inline-block;">Search Support Requests</h2>
<?php include(MODULES.'/support/partials/search_bar.php'); ?>
Searched for: <strong><?=$searchTerm?></strong>

<h3>Request Items</h3>
<!--	Start First Row-->
<div class="tableBody min-tablet">
    <div class="tableRowHeader">
        <div class="tableCell" style="width: 15%;">Code</div>
        <div class="tableCell" style="width: 15%;">Date Requested</div>
        <div class="tableCell" style="width: 15%;">Requestor</div>
        <div class="tableCell" style="width: 15%;">Organization</div>
        <div class="tableCell" style="width: 15%;">Type</div>
        <div class="tableCell" style="width: 15%;">Status</div>
    </div> <!-- end row header -->
    <?php foreach ($supportRequestList as $request) { ?>
        <div class="tableRow">
            <div class="tableCell">
	            <span class="value"><a href="/_support/request_detail/<?=$request->code?>"><?=$request->code?></a></span>
            </div>
            <div class="tableCell">
	            <span class="value"><?=$request->date_request?></span>
            </div>
            <div class="tableCell">
	            <span class="value"><?=$request->customer->full_name()?></span>
            </div>
            <div class="tableCell">
	            <span class="value"><?=$request->customer->organization->name?></span>
            </div>
            <div class="tableCell">
	            <span class="value"><?=ucwords(strtolower($request->type))?></span>
            </div>
            <div class="tableCell">
	            <span class="value"><?=ucwords(strtolower($request->status))?></span>
            </div>
        </div>
    <?php } ?>
</div>
<!--End first row-->	Manage


<?php
// show the none found message
if (empty($supportRequestList)) {
?>
    <h4>No Results</h4>
<?php
}
?>

<h3>Request Tickets</h3>

<!--	Start Request Item-->
<?	foreach ($supportItemList as $item) { ?>
<div class="tableBody min-tablet">
    <div class="tableRowHeader">
        <div class="tableCell" style="width: 10%;">Ticket</div>
        <div class="tableCell" style="width: 25%;">Product</div>
        <div class="tableCell" style="width: 25%;">Serial</div>
        <div class="tableCell" style="width: 20%;">Status</div>
    </div> <!-- end row header -->
    <div class="tableRow">
        <div class="tableCell">
            <a href="/_support/request_item/<?=$item->id?>"><?=$item->ticketNumber()?></a>
        </div>
        <div class="tableCell">
            <?=$item->product->code?>
        </div>
        <div class="tableCell">
            <?=$item->serial_number?>
        </div>
        <div class="tableCell">
            <?=$item->status?>
        </div>
    </div>
</div>
        
<div class="tableBody min-tablet marginBottom_20">
    <div class="tableRowHeader">
        <div class="tableCell" style="width: 100%;">Description</div>
    </div> <!-- end row header -->
    <div class="tableRow">
        <div class="tableCell">
            <?=$item->description?>
        </div>
    </div>
</div>

<?	} ?>	
<!--End Request Item -->	

<?php
// show the none found message
if (empty($supportItemList)) {
?>
    <h4>No Results</h4>
<?php
}
?>
<?php	if (count($actions) > 0) { ?>
    <div style="width: 756px;">
    <h3>Request Actions</h3>
    <?php	
            foreach ($actions as $action) {
		    if (isset($action->requestedBy)) {
			    $requested_by = $action->requestedBy->full_name();
		    } else {
			    $requested_by = "Unknown";
		    }
		    if (isset($action->assignedTo) && isset($action->assignedTo->id)) {
			    $assigned_to = $action->assignedTo->full_name();
		    } else {
			    $assigned_to = "Unassigned";
		    }
		    if ($action->type == "Note") {
    ?>
        <table style="width: 100%; margin-bottom: 10px; border: 1px solid gray">
            <tr>
                <th>Posted On</th>
	            <th>Posted By</th>
            </tr>
            <tr>
                <td><?=$action->date_requested?></td>
	            <td><?=$requested_by?></td>
            </tr>
            <tr><th colspan="2">Note</th></tr>
            <tr><td colspan="2"><?=$action->description?></td></tr>
        </table>
    <?php } else { ?>
        <table style="width: 100%; margin-bottom: 10px; border: 1px solid gray">
            <tr>
                <th>Date Requested</th>
	            <th>Requested By</th>
	            <th>Assigned To</th>
	            <th>Type</th>
	            <th>Status</th>
            </tr>
            <tr>
                <td><a href="/_support/action/<?=$action->id?>"><?=$action->date_requested?></a></td>
	            <td><?=$requested_by?></td>
	            <td><?=$assigned_to?></td>
	            <td><?=$action->type?></td>
	            <td><?=$action->status?></td>
            </tr>
            <tr><th colspan="5">Description</th></tr>
            <tr><td colspan="5"><?=$action->description?></td></tr>
        </table>
    <?php	} 
    } 
    ?>
    </div>
<?php	} ?>
