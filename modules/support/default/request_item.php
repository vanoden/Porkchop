<style>
	div.container {	width: 100%; clear: both;	}
	div.toggleContainer {	width: 100%; clear: both; display: none; }
    pre {
        white-space: pre-wrap;
        white-space: -moz-pre-wrap;
        white-space: -pre-wrap;
        white-space: -o-pre-wrap;
        word-wrap: break-word;
    }
</style>
<script>
	function showForm(form) {
		var forms = ['action','rma','shipping','comment'];
		forms.forEach(function(form) {
			document.getElementById(form+'FormDiv').style.display = 'none';
		});
		var formDiv = document.getElementById(form+'FormDiv');
		formDiv.style.display = 'block';
		return true;
	}
	function hideForm(form) {
		var formDiv = document.getElementById(form+'FormDiv');
		formDiv.style.display = 'none';
		return true;
	}
</script>
<div style="width: 756px;">
	<div class="breadcrumbs">
		<a href="/_support/requests">Support Home</a>
    	<a href="/_support/requests">Requests</a>
		<a href="/_support/request_items">Tickets</a> &gt; Ticket #<?=$item->ticketNumber()?>
	</div>
</div>
<h2 style="display: inline-block;"><i class='fa fa-check-square' aria-hidden='true'></i> Ticket: <span><?=$item->ticketNumber()?></span></h2>
<?php include(MODULES.'/support/partials/search_bar.php'); ?>
<form name="request_form" method="post" action="/_support/request_item">
<input type="hidden" name="request_id" value="<?=$request->id?>" />
<div><!-- START Main Div -->

    <!--	 ==================================== -->
	<!-- START Request Form -->
	<form name="requestForm" method="post">
        <input type="hidden" name="item_id" value="<?=$item->id?>" />
        <?	if ($page->errorCount()) { ?>
        <div class="form_error"><?=$page->errorString()?></div>
        <? } ?>
        <?	if ($page->success) { ?>
        <div class="form_success"><?=$page->success?></div>
        <?	} ?>
		        
	    <!--	Start First Row-->
        <div class="tableBody min-tablet marginTop_20">
	        <div class="tableRowHeader">
		        <div class="tableCell" style="width: 20%;">Request</div>
		        <div class="tableCell" style="width: 20%;">Requested By</div>
		        <div class="tableCell" style="width: 10%;">Line</div>
		        <div class="tableCell" style="width: 10%;">Status</div>
		        <div class="tableCell" style="width: 20%;">Product</div>
		        <div class="tableCell" style="width: 20%;">Serial #</div>
	        </div> <!-- end row header -->
	        <div class="tableRow">
		        <div class="tableCell">
			        <a href="/_support/request_detail/<?=$request->code?>"><?=$request->code?></a>
		        </div>
		        <div class="tableCell">
			        <a href="/_register/admin_account/<?=$request->customer->code?>"><?=$request->customer->full_name()?></a>
		        </div>
		        <div class="tableCell">
			        <span class="value"><?=$item->line?></span>
		        </div>
		        <div class="tableCell">
			        <span class="value"><?=$item->status?></span>
		        </div>
		        <div class="tableCell">
			        <select class="value input" name="product_id">
				        <option value="">N/A</option>
				        <?	foreach ($products as $product) { ?>
				        <option value="<?=$product->id?>"<? if ($product->id == $item->product->id) print " selected";?>><?=$product->code?></option>
				        <?	} ?>
			        </select>
		        </div>
		        <div class="tableCell">
			        <input type="text" class="value input" name="serial_number" value="<?=$item->serial_number?>">
		        </div>
	        </div>
        </div>
        <div class="tableBody min-tablet">
	        <div class="tableRowHeader">
		        <div class="tableCell" style="width: 100%;">Description</div>
	        </div> <!-- end row header -->
	        <div class="tableRow">
		        <div class="tableCell">
			        <pre><?=strip_tags($item->description)?></pre>
		        </div>
	        </div>
	        <div class="tableRow button-bar">
		        <input type="submit" name="btn_submit" class="button" value="Update Request Item" />
		        <input type="button" name="btn_add_action" class="button" value="Add Action" onclick="showForm('action');" />
		        <input type="button" name="btn_add_rma" class="button secondary" value="Authorize Return" onclick="showForm('rma');" />
		        <input type="button" name="btn_ship_item" class="button secondary" value="Ship Product" onclick="showForm('shipping');" />
		        <input type="button" name="btn_add_note" class="button secondary" value="Add Comment" onclick="showForm('comment');" />
        <?	if ($item->status == 'CLOSED') { ?>
		        <input type="submit" name="btn_reopen_item" class="button" value="Reopen Item" />
        <?	} else { ?>
		        <input type="submit" name="btn_close_item" class="button" value="Close Item" />
        <?	} ?>
	        </div>
        </div>
        <!--End first row-->
	</form>
	<!-- END Request Form -->
	<!--	 ==================================== -->
	
	<!--	 ==================================== -->
	<!-- Action Form -->
	<div id="actionFormDiv" class="toggleContainer">
		<form name="actionForm" method="post" action="/_support/request_item">
		<input type="hidden" name="item_id" value="<?=$item->id?>" />
		<h3>Add Action</h3>
			
		<div class="tableBody min-tablet marginTop_20">
			<div class="tableRowHeader">
				<div class="tableCell" style="width: 20%;">Date Requested</div>
				<div class="tableCell" style="width: 20%;">Requested By</div>
				<div class="tableCell" style="width: 20%;">Assigned To</div>
				<div class="tableCell" style="width: 10%;">Action Type</div>
				<div class="tableCell" style="width: 10%;">Status</div>
			</div> <!-- end row header -->
			<div class="tableRow">
				<div class="tableCell">
					<input type="text" name="action_date_request" class="value input" value="now" />
				</div>
				<div class="tableCell">
					<select name="action_requested_by" class="value input">
                    <?	foreach ($admins as $admin) { ?>
					  <option value="<?=$admin->id?>"<? if ($admin->id == $GLOBALS['_SESSION_']->customer->id) print " selected";?>><?=$admin->full_name()?></option>
                    <?	} ?>
					</select>
				</div>
				<div class="tableCell">
					<select name="action_assigned_to" class="value input">
						<option value="">Unassigned</option>
                        <?	foreach ($admins as $admin) { ?>
					        <option value="<?=$admin->id?>"><?=$admin->full_name()?></option>
                        <?	} ?>
					</select>
				</div>
				<div class="tableCell">
					<select name="action_type" class="value input">
						<option value="Contact Customer">Contact Customer</option>
						<option value="Remote Evaluation">Remote Evaluation</option>
						<option value="Authorize Return">Authorize Return</option>
						<option value="Local Diagnosis">Local Diagnosis</option>
						<option value="Order Parts">Order Parts</option>
						<option value="Repair Unit">Repair Unit</option>
						<option value="Build New Unit">Build New Unit</option>
						<option value="Configure Unit">Configure Unit</option>
						<option value="Calibrate Unit">Calibrate Unit</option>
						<option value="Test Unit">Test Unit</option>
						<option value="Ship Unit">Ship Unit</option>
						<option value="Transfer Ownership">Transfer Ownership</option>
					</select>
				</div>
				<div class="tableCell">
					<select name="action_status" class="value input">
						<option value="NEW">New</option>
						<option value="ASSIGNED">Assigned</option>
						<option value="ACTIVE">Active</option>
						<option value="PENDING CUSTOMER">Pending Customer</option>
						<option value="PENDING VENDOR">Pending Vendor</option>
						<option value="CANCELLED">Cancelled</option>
						<option value="COMPLETE">Complete</option>
					</select>
				</div>
			</div>
		</div>
		<!-- END tableBody -->	
			
		<div class="tableBody min-tablet">
			<div class="tableRowHeader">
				<div class="tableCell" style="width: 20%;">Additional Information</div>
			</div> <!-- end row header -->
			<div class="tableRow">
				<div class="tableCell">
					<textarea name="action_description" class="value input wide_100per"></textarea>
				</div>
			</div>
		</div>
			
		<div class="form_footer">
			<input type="button" name="btn_cancel_action" value="Cancel" class="button" onclick="hideForm('action');" />
			<input type="submit" name="btn_add_action" value="Add Action" class="button" />
		</div>
		</form>
	</div>
	<!-- END Action Form -->
	<!--	 ==================================== -->
	
	<!-- RMA Form -->
	<div id="rmaFormDiv" class="toggleContainer">
		<form name="rmaForm" method="post" action="/_support/request_item">
		<input type="hidden" name="item_id" value="<?=$item->id?>" />
		<h2>Authorize Return</h2>
		<div class="container">
			<span class="label">Create RMA for this item?</span>
		</div>
		<div class="form_footer">
			<input type="button" name="btn_cancel_rma" value="Cancel" class="button" onclick="hideForm('rma');" />
			<input type="submit" name="btn_add_rma" value="Authorize Return" class="button" />
		</div>
		</form>
	</div>
	
	<!-- Shipping Form -->
	<div id="shippingFormDiv" class="toggleContainer">
		<form name="shippingForm" method="post" action="/_support/request_item">
		<input type="hidden" name="item_id" value="<?=$item->id?>" />
		<h2>Ship Item</h2>
		<div class="container">
			<span class="label">Shipment</span>
			<select class="value input" name="shipment_id">
				<option value="new">New</option>
			</select>
		</div>
		<div class="form_footer">
			<input type="button" name="btn_cancel_shipment" value="Cancel" class="button" onclick="hideForm('shipping');" />
			<input type="submit" name="btn_add_shipment" value="Ship Item" class="button" />
		</div>
		</form>
	</div>
	
	<!-- UNDO to HERE -->
	<!-- Comment Form -->
	<div id="commentFormDiv" class="toggleContainer">
		<form name="commentForm" method="post" action="/_support/request_item">
		<input type="hidden" name="item_id" value="<?=$item->id?>" />
		<h2>Add Comment</h2>
		<div class="tableBody min-tablet">
			<div class="tableRowHeader">
				<span class="label">New Status</span>
			</div>
			<div class="tableRow">
				<div class="tableCell">
					<select name="action_status" class="value input">
						<option value="NEW">New</option>
						<option value="ASSIGNED">Assigned</option>
						<option value="ACTIVE">Active</option>
						<option value="PENDING CUSTOMER">Pending Customer</option>
						<option value="PENDING VENDOR">Pending Vendor</option>
						<option value="CANCELLED">Cancelled</option>
						<option value="COMPLETE">Complete</option>
					</select>
				</div>
			</div>
			<div class="tableRowHeader">
				<span class="label">Comment</span>
			</div>
			<div class="tableRow">
				<div class="tableCell"><textarea class="value input" name="content"></textarea></div>
			</div>
			<div class="button-bar">
				<input type="button" name="btn_cancel_comment" value="Cancel" class="button" onclick="hideForm('comment');" />
				<input type="submit" name="btn_add_comment" value="Add Comment" class="button" />
			</div>
		</div><!-- END Table -->
			
		</form>
	</div>
	<!-- END Comment Form -->
</div>
</form>

<div style="width: 756px;">
    <br/><hr/><h2>Documents</h2><br/>
    <?php
    if ($filesUploaded) {
    ?>
        <table style="width: 100%; margin-bottom: 10px; border: 1px solid gray">
            <tr>
	            <th>File Name</th>
	            <th>User</th>
	            <th>Organization</th>
	            <th>Uploaded</th>
            </tr>
            <?php
            foreach ($filesUploaded as $fileUploaded) {
            ?>
                <tr>
	                <td><a href="/_storage/downloadfile?file_id=<?=$fileUploaded->id?>" target="_blank"><?=$fileUploaded->name?></a></td>
	                <td><?=$fileUploaded->user->first_name?> <?=$fileUploaded->user->last_name?></td>
	                <td><?=$fileUploaded->user->organization->name?></td>
	                <td><?=date("M. j, Y, g:i a", strtotime($fileUploaded->date_created))?></td>
                </tr>
            <?php
            }
            ?>
        </table>
    <?php
    }
    ?>
    <form name="repoUpload" action="/_support/request_item/<?=$item->id?>" method="post" enctype="multipart/form-data">
    <div class="container">
	    <span class="label">Upload File</span>
        <input type="hidden" name="repository_name" value="<?=$repository?>" />
	    <input type="hidden" name="type" value="support ticket" />
	    <input type="file" name="uploadFile" />
	    <input type="submit" name="btn_submit" class="button" value="Upload" />
    </div>
    </form>
    <br/><br/>
</div>

<?	if (count($actions) > 0) { ?>
<div style="width: 756px;">
<h2>Actions</h2>
<?	foreach ($actions as $action) {
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
    <h3 style="padding-top: 20px;">Action Note</h3>
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
        <tr><td colspan="2">        
            <pre><?=strip_tags($action->description)?></pre>
        </td></tr>
    </table>
    <? } else { ?>
        <h3 style="padding-top: 20px;">Action</h3>
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
            <tr><td colspan="5">
                <pre><?=strip_tags($action->description)?></pre>
            </td></tr>
        </table>
    <?php
    }
    $actionEvents = $action->getEvents();
    ?>
    <h4>History</h4>
    <?php 
    if (!empty($actionEvents)) {
        foreach ($actionEvents as $actionEvent) { 
        ?>
        <table style="width: 100%; padding-bottom: 10px;">
           <tr>
              <th>Event Date</th>
              <th>User</th>
           </tr>
           <tr>
              <td><?=$actionEvent->date_event?></td>
              <td><?=$actionEvent->user->full_name()?></td>
           </tr>
           <tr>
              <th colspan="2">Description</th>
           <tr>
              <td colspan="2">	    
                 <pre><?=strip_tags($actionEvent->description)?></pre>
              </td>
           </tr>
           </tr>
        </table>
        <?php
	        }
    } else {
    ?>
        <table style="width: 100%; padding-bottom: 10px;">
           <tr>
              <th colspan="2">No Events</th>
            </tr>
        </table>
    <?php
    }
}
?>
<br/><br/><br/>
</div>
<?	} ?>
<?	if (isset($rmalist) && $rmalist->count() > 0) { ?>
<div style="width: 756px;">
<h2>Authorized Returns</h2>
<?		foreach ($rmas as $rma) { ?>
	<div class="tableBody min-tablet">
		<div class="tableRowHeader">
			<div class="tableCell">Number</div>
	        <div class="tableCell">Date Approved</div>
	        <div class="tableCell">Approved By</div>
        </div>
        <div class="tableRow">
			<div class="tableCell"><a href="/_support/admin_rma/<?=$rma->code?>"><?=$rma->number()?></a></div>
	        <div class="tableCell"><?=$rma->date_approved?></div>
	        <div class="tableCell"><?=$rma->approvedBy()->full_name()?></div>
        </div>
    </div>
<?		} ?>
</div>
<?	} ?>
<?	if (count($comments) > 0) { ?>
    <!--	Start Request Item-->
    <h3>Comments</h3>
    <?		foreach ($comments as $comment) { ?>
    <div class="tableBody min-tablet">
	    <div class="tableRowHeader">
		    <div class="tableCell" style="width: 60%;">Date Entered</div>
		    <div class="tableCell" style="width: 40%;">Author</div>
	    </div> <!-- end row header -->
	    <div class="tableRow">
		    <div class="tableCell">
			    <?=$comment->date_comment?>
		    </div>
		    <div class="tableCell">
			    <?=$comment->author->full_name()?>
		    </div>
	    </div>
    </div>
    <div class="tableBody min-tablet marginBottom_20">
	    <div class="tableRowHeader">
		    <div class="tableCell" style="width: 100%;">Comment</div>
	    </div> <!-- end row header -->
	    <div class="tableRow">
		    <div class="tableCell">
			    <?=$comment->content?>
		    </div>
	    </div>
    </div>
<?		} ?>
<!--End Request Item -->
<!-- END Comments Section -->
<!--	 ==================================== -->
<?	} ?>
