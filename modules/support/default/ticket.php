<style>
	div.toggleContainer {	display: none; }
</style>
<script>
	function showForm(form) {
		var forms = ['comment'];
		forms.forEach(function(form) {
			console.log("Show "+form+" Form");
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
<div class="secondaryHeader">
	<h2 style="display: inline-block;"><a href="/_support/tickets">Tickets</a> > </h2><h2 style="display: inline-block;"><i class='fa fa-check-square' aria-hidden='true'></i> Ticket: <span><?=$item->ticketNumber()?></span></h2>
</div>

<form name="request_form" method="post" action="/_support/ticket">
<input type="hidden" name="request_id" value="<?=$request->id?>" />

<div><!-- START Main Div -->
    <!--	 ==================================== -->
	<!-- START Request Form -->
	<form name="requestForm" method="post">
        <input type="hidden" name="item_id" value="<?=$item->id?>" />
        <?php	if ($page->errorCount()) { ?>
        <div class="form_error"><?=$page->errorString()?></div>
        <?php	} ?>
        <?php	if ($page->success) { ?>
        <div class="form_success"><?=$page->success?></div>
        <?php	} ?>
		        
	    <!--	Start First Row-->
			<div id="ticketForm">
        <div class="tableBody">
	        <div class="tableRowHeader">
		        <div class="tableCell">Requestor</div>
		        <div class="tableCell">Status</div>
		        <div class="tableCell">Product</div>
		        <div class="tableCell">Date</div>
		        <div class="tableCell">Serial #</div>
	        </div> <!-- end row header -->
	        <div class="tableRow">
		        <div class="tableCell"><?=$request->customer->full_name()?></div>
		        <div class="tableCell"><?=$item->request->date_request?></div>
		        <div class="tableCell"><span class="value"><?=$item->status?></span></div>
		        <div class="tableCell"><span class="value"><?=$item->product->code?></span></div>
		        <div class="tableCell"><span class="value"><a href="/_monitor/asset/<?=$item->serial_number?>"><?=$item->serial_number?></a></span></div>
	        </div>
        </div>

        <div class="tableBody">
	        <div class="tableRowHeader secondary">
		        <div class="tableCell">Description</div>
	        </div> <!-- end row header -->
	        <div class="tableRow">
		        <div class="tableCell"><pre><?=strip_tags($item->description)?></pre></div>
	        </div>
	        <div class="tableRow button-bar">
						<input type="button" name="btn_add_note" class="button iconButton addIcon" value="Add Comment" onclick="showForm('comment');" />
        		<?php	if ($item->status == 'CLOSED') { ?>
		        <input type="submit" name="btn_reopen_item" class="button iconButton openIcon" value="Reopen Item" />
        		<?php	} else { ?>
		        <input type="submit" name="btn_close_item" class="button iconButton closeIcon" value="Close Item" />
        		<?php	} ?>
	        </div>
        </div>
						</div><!-- end ticketForm -->
        <!--End first row-->
	</form>
	<!-- END Request Form -->
	<!--	 ==================================== -->
	
	<!-- Comment Form -->
	<div id="commentFormDiv" class="toggleContainer">
		<form id="commentForm" name="commentForm" method="post" action="/_support/ticket">
		<input type="hidden" name="item_id" value="<?=$item->id?>" />
		<div class="tableBody">
			<div class="tableRowHeader secondary"><span class="label">Insert Comment:</span></div>
			<div class="tableRow">
				<div class="tableCell" style="padding: 0;">
					<textarea class="value input" style="width: 50%; height: 70px; margin: 0;" name="content"></textarea>
				</div>
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

<div>
	<h3 class="eyebrow">Documents</h3>
    <?php
    if ($filesUploaded) {
    ?>
		<div id="documentsForm">
			<div class="tableBody">
				<div class="tableRowHeader">
					<div class="tableCell">File Name</div>
					<div class="tableCell">User</div>
					<div class="tableCell">Organization</div>
					<div class="tableCell">Uploaded</div>
				</div>
		<?php
		foreach ($filesUploaded as $fileUploaded) {
		?>
				<div class="tableRow">
					<div class="tableCell"><a href="/_storage/downloadfile?file_id=<?=$fileUploaded->id?>" target="_blank"><?=$fileUploaded->name?></a></div>
					<div class="tableCell"><?=$fileUploaded->user->first_name?> <?=$fileUploaded->user->last_name?></div>
					<div class="tableCell"><?=$fileUploaded->user->organization->name?></div>
					<div class="tableCell"><?=date("M. j, Y, g:i a", strtotime($fileUploaded->date_created))?></div>
				</div>
		<?php
		}
		?>
        </div><!-- end table -->
	</div><!-- end documentsForm -->
    <?php
    }
	else { 
    ?>
	<span class="value">No Uploads Found</span>
	<?php	} ?>
<!--
    <form name="repoUpload" action="/_support/ticket/<?=$item->id?>" method="post" enctype="multipart/form-data">
    <div class="container">
	    <span class="label">Upload File</span>
        <input type="hidden" name="repository_name" value="<?=$repository?>" />
	    <input type="hidden" name="type" value="support ticket" />
	    <input type="file" name="uploadFile" />
	    <input type="submit" name="btn_submit" class="button" value="Upload" />
    </div>
    </form>
	<br/>
    <br/>
-->
</div>

<?php	if (is_array($actions) && count($actions) > 0) { ?>
<div>
<h3 class="eyebrow">Actions</h3>
<?php	foreach ($actions as $action) {
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
    <h3>Action Note</h3>
    <div class="tableBody">
				<div class="tableRowHeader">
					<div class="tableCell">Posted On</div>
	        <div class="tableCell">Posted By</div>
				</div>
        <div class="tableRow">
					<div class="tableCell"><?=$action->date_requested?></div>
					<div class="tableCell"><?=$requested_by?></div>
				</div>
        <div class="tableRowHeader">
					<div class="tableCell">Note</div>
				</div>
        <div class="tableRow">
					<div class="tableCell"><pre><?=strip_tags($action->description)?></pre></div>
				</div>
		</div><!-- end table -->
    </div>
		
    <?php	} else { ?>
			<div id="actionsForm">
				<div class="tableBody">
					<div class="tableRowHeader">
						<div class="tableCell">Date Requested</div>
						<div class="tableCell">Requested By</div>
						<div class="tableCell">Assigned To</div>
						<div class="tableCell">Type</div>
						<div class="tableCell">Status</div>
					</div>
					<div class="tableRow">
						<div class="tableCell"><a href="/_support/action/<?=$action->id?>"><?=$action->date_requested?></a></div>
						<div class="tableCell"><?=$requested_by?></div>
						<div class="tableCell"><?=$assigned_to?></div>
						<div class="tableCell"><?=$action->type?></div>
						<div class="tableCell"><?=$action->status?></div>
					</div>
				</div><!-- end table -->
				<div class="tableBody">
					<div class="tableRowHeader secondary">
						<div class="tableCell">Description</div>							
					</div>
					<div class="tableRow">
						<div class="tableCell"><pre><?=strip_tags($action->description)?></pre></div>
					</div>
				</div><!-- end table -->
    	<?php
    	}
    	$actionEvents = $action->getEvents();
    	?>	
			<?php 
				if (!empty($actionEvents)) {
					foreach ($actionEvents as $actionEvent) { 
					?>
					<div class="tableBody">
						<div class="tableRowHeader secondary">
							<div class="tableCell" style="width: 10%;">Event Date</div>
							<div class="tableCell" style="width: 10%;">User</div>
							<div class="tableCell" style="width: 30%;">Description</div>
						</div>
						<div class="tableRow">
							<div class="tableCell"><?=$actionEvent->date_event?></div>
							<div class="tableCell"><?=$actionEvent->user->full_name()?></div>
								<div class="tableCell"><?=strip_tags($actionEvent->description)?></div>
						</div>
					</div><!-- end table -->
				</div><!-- end actionsForm -->
			<?php
	    }
    	} else {
			?>
				<div class="tableBody">
					<div class="tableRowHeader">
						<div class="tableCell" style="width: 10%;">No Events</div>
					</div>
				</div>
    	<?php
    	}
		}
	?>
<!-- Try this here -->
<?php	if (isset($rmalist) && $rmalist->count() > 0) { ?>
<div>
<h3 class="eyebrow">Authorized Returns</h3>
<?php		foreach ($rmas as $rma) { ?>
	<div class="tableBody">
		<div class="tableRowHeader">
			<div class="tableCell">Number</div>
			<div class="tableCell">Date Approved</div>
			<div class="tableCell">Approved By</div>
		</div>
		<div class="tableRow">
			<div class="tableCell"><a href="/_support/rma_form/<?=$rma->code?>"><?=$rma->number()?></a></div>
			<div class="tableCell"><?=$rma->date_approved?></div>
			<div class="tableCell"><?=$rma->approvedBy()->full_name()?></div>
		</div>
	</div>
<?php		} ?>
</div>
<?php	} ?>

<?php	if (is_array($comments) && count($comments) > 0) { ?>
    <!--	Start Request Item-->
    <h3 class="eyebrow">Comments</h3>
    <?php		foreach ($comments as $comment) { ?>
    <div class="tableBody">
	    <div class="tableRowHeader secondary">
		    <div class="tableCell" style="width: 10%;">Date Entered</div>
		    <div class="tableCell" style="width: 10%;">Author</div>
		    <div class="tableCell" style="width: 30%;">Comment</div>
	    </div> <!-- end row header -->
	    <div class="tableRow">
		    <div class="tableCell"><?=$comment->date_comment?></div>
		    <div class="tableCell"><?=$comment->author->full_name()?></div>
		    <div class="tableCell"><?=$comment->content?></div>
	    </div>
    </div>
<?php		} ?>
</div>
<?php	} ?>
<?php	} ?>
