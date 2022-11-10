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
<span class="title">Support</span>

<?php if ($page->errorCount() > 0) { ?>
<section id="form-message">
	<ul class="connectBorder errorText">
		<li><?=$page->errorString()?></li>
	</ul>
</section>

<?php	} else if ($page->success) { ?>
<section id="form-message">
	<ul class="connectBorder progressText">
		<li><?=$page->success?></li>
	</ul>
</section>
<?php	} ?>

<nav id="breadcrumb">
	<ul>
		<li><a href="/_support/tickets">All Tickets</a></li>
		<li><a href="/_support/ticket/<?=$item->id?>" class="value">Ticket #<?=$item->id?></a></li>
	</ul>
</nav>

<!--	 ==================================== -->
<!-- START Request Form -->
<form name="requestForm" method="post">
	<input type="hidden" name="item_id" value="<?=$item->id?>" />
	<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
	<?php	if ($page->errorCount()) { ?><div class="form_error"><?=$page->errorString()?></div><?php	} ?>
	<?php	if ($page->success) { ?><div class="form_success"><?=$page->success?></div><?php	} ?>
		        
    <!--	Start First Row-->
	<div id="ticketForm" class="connectBorder">
        <div class="tableBody">
	        <div class="tableRowHeader">
		        <div>Requestor</div><div>Status</div><div>Product</div><div>Date</div><div>Serial #</div>
	        </div> <!-- end row header -->
	        <div class="tableRow">
		        <div><span class="hiddenDesktop value">Requestor: </span><?=$request->customer->full_name()?></div>
		        <div><span class="hiddenDesktop value">Status: </span><?=$item->request->date_request?></div>
		        <div><span class="hiddenDesktop value">Product: </span><span class="value"><?=$item->status?></span></div>
		        <div><span class="hiddenDesktop value">Date: </span><span class="value"><?=$item->product->code?></span></div>
		        <div><span class="hiddenDesktop value">Serial #: </span><span class="value"><a href="/_monitor/asset/<?=$item->serial_number?>"><?=$item->serial_number?></a></span></div>
	        </div>
        </div>

        <div class="tableBody">
	        <div class="tableRowHeader secondary">
		        <div>Description</div>
	        </div> <!-- end row header -->
	        <div class="tableRow">
		        <div><pre><?=strip_tags($item->description)?></pre></div>
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
	
<!--	 ==================================== -->
<!-- START Add Comment Form -->
<div id="commentFormDiv" class="toggleContainer">
	<form id="commentForm" name="commentForm" method="post" action="/_support/ticket">
		<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
		<input type="hidden" name="item_id" value="<?=$item->id?>" />
		<div class="tableBody">
			<div class="tableRowHeader secondary"><span class="label">Insert Comment:</span></div>
			<div class="tableRow">
				<div style="padding: 0;">
					<textarea class="value input" wrap="hard" style="width: 50%; height: 70px; margin: 0;" name="content"></textarea>
				</div>
			</div>
			<div class="button-bar">
				<input type="button" name="btn_cancel_comment" value="Cancel" class="button" onclick="hideForm('comment');" />
				<input type="submit" name="btn_add_comment" value="Add Comment" class="button" />
			</div>
		</div><!-- END Table -->
	</form>
</div>
<!-- END Add Comment Form -->
<!--	 ==================================== -->

<!--	 ==================================== -->
<!-- START Documents Section -->
	<h3 class="eyebrow">Documents</h3>
	<?php
	if ($filesUploaded) {
	?>
	<div id="documentsForm" class="connectBorder">
		<div class="tableBody">
			<div class="tableRowHeader">
				<div>File Name</div><div>User</div><div>Organization</div><div>Uploaded</div>
			</div>
			<?php	foreach ($filesUploaded as $fileUploaded) {	?>
				<div class="tableRow">
					<div><span class="hiddenDesktop value">File Name: </span><a href="/_storage/downloadfile?file_id=<?=$fileUploaded->id?>" target="_blank"><?=$fileUploaded->name?></a></div>
					<div><span class="hiddenDesktop value">User: </span><?=$fileUploaded->user->first_name?> <?=$fileUploaded->user->last_name?></div>
					<div><span class="hiddenDesktop value">Organization: </span><?=$fileUploaded->user->organization->name?></div>
					<div><span class="hiddenDesktop value">Uploaded: </span><?=date("M. j, Y, g:i a", strtotime($fileUploaded->date_created))?></div>
				</div>
			<?php	}	?>
		</div><!-- end table -->
	</div><!-- end documentsForm -->
	<?php } else { ?>
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
		-->
<!-- END Documents Section -->
<!--	 ==================================== -->

<!--	 ==================================== -->
<!-- START Actions Section -->
<?php	if (is_array($actions) && count($actions) > 0) { ?>
	<h3 class="eyebrow">Actions</h3>
	<div class="connectBorder">
		<?php	foreach ($actions as $action) {
			if (isset($action->requestedBy)) { $requested_by = $action->requestedBy->full_name();
			} else { $requested_by = "Unknown";	}
			
			if (isset($action->assignedTo) && isset($action->assignedTo->id)) {	$assigned_to = $action->assignedTo->full_name();
			} else { $assigned_to = "Unassigned";	}
			
			if ($action->type == "Note") {
		?>
			<h3>Action Note</h3>
			<div class="tableBody">
				<div class="tableRowHeader">
					<div>Posted On</div><div>Posted By</div>
				</div>
				<div class="tableRow">
					<div><span class="hiddenDesktop value">Date requested: </span><?=$action->date_requested?></div>
					<div><span class="hiddenDesktop value">Requested by: </span><?=$requested_by?></div>
				</div>
				<div class="tableRowHeader">
					<div>Note</div>
				</div>
				<div class="tableRow">
					<div><span class="hiddenDesktop value">Description: </span><pre><?=strip_tags($action->description)?></pre></div>
				</div>
			</div><!-- end table -->
		
    <?php	} else { ?>
			<div id="actionsForm">
				<div class="tableBody">
					<div class="tableRowHeader">
						<div>Date Requested</div><div>Requestor</div><div>Assigned To</div><div>Type</div><div>Status</div>
					</div>
					<div class="tableRow">
						<div><span class="hiddenDesktop value">Date requested: </span><a href="/_support/action/<?=$action->id?>"><?=$action->date_requested?></a></div>
						<div><span class="hiddenDesktop value">Requested by: </span><?=$requested_by?></div>
						<div><span class="hiddenDesktop value">Assigned to: </span><?=$assigned_to?></div>
						<div><span class="hiddenDesktop value">Type: </span><?=$action->type?></div>
						<div><span class="hiddenDesktop value">Status: </span><?=$action->status?></div>
					</div>
				</div><!-- end table -->
				<div class="tableBody">
					<div class="tableRowHeader secondary">
						<div>Description</div>							
					</div>
					<div class="tableRow">
						<div><span class="hiddenDesktop value">Description: </span><pre><?=strip_tags($action->description)?></pre></div>
					</div>
				</div><!-- end table -->
		<?php	}	$actionEvents = $action->getEvents();	?>	

		<?php 
			if (!empty($actionEvents)) {
				foreach ($actionEvents as $actionEvent) { ?>
				<div class="tableBody">
					<div class="tableRowHeader secondary">
						<div style="width: 10%;">Event Date</div><div style="width: 10%;">User</div><div style="width: 30%;">Description</div>
					</div>
					<div class="tableRow">
						<div><span class="hiddenDesktop value">Event Date: </span><?=$actionEvent->date_event?></div>
						<div><span class="hiddenDesktop value">User: </span><?=$actionEvent->user->full_name()?></div>
						<div><span class="hiddenDesktop value">Description: </span><?=strip_tags($actionEvent->description)?></div>
					</div>
				</div><!-- end table -->
				<?php	}
    	} else {
			?>
				<div class="tableBody">
					<div class="tableRowHeader secondary">
						<div>No Events</div>
					</div>
				</div>
			<?php	} ?>
			</div><!-- end actionsForm -->
		<?php	}	?>
	</div><!-- END Connect border -->
<?php } ?>
<!-- END Actions Section -->
<!--	 ==================================== -->


	<!-- ========== SECTION ========== -->
	<!-- Authorized Returns -->
	<?php	if (isset($rmalist) && $rmalist->count() > 0) { ?>
		<h3 class="eyebrow">Authorized Returns</h3>
		<div class="connectBorder">
			<div class="tableBody">
				<div class="tableRowHeader">
					<div>Number</div>
					<div>Date Approved</div>
					<div>Approved By</div>
				</div>
				<?php		foreach ($rmas as $rma) { ?>
				<div class="tableRow">
					<div><span class="hiddenDesktop value">Number: </span><a href="/_support/rma_form/<?=$rma->code?>"><?=$rma->number()?></a></div>
					<div><span class="hiddenDesktop value">Date approved: </span><?=$rma->date_approved?></div>
					<div><span class="hiddenDesktop value">Approved by: </span><?=$rma->approvedBy()->full_name()?></div>
				</div>
				<?php		} ?>
			</div>
		</div><!-- END Connect Border -->
	<?php	} ?>
	<!-- ========== SECTION ========== -->
	<!-- Comments -->
	<?php	if (is_array($comments) && count($comments) > 0) { ?>
    <!--	Start Request Item-->
    <h3 class="eyebrow">Comments</h3>
		<div class="tableBody connectBorder bandedRows">
			<div class="tableRowHeader secondary">
				<div style="width: 10%;">Date entered</div>
				<div style="width: 10%;">Author</div>
				<div style="width: 30%;">Comment</div>
			</div> <!-- end row header -->
			<?php		foreach ($comments as $comment) { ?>
			<div class="tableRow">
				<div><span class="hiddenDesktop value">Date: </span><?=$comment->date_comment?></div>
				<div><span class="hiddenDesktop value">Author: </span><?=$comment->author->full_name()?></div>
				<div><span class="hiddenDesktop value">Comment: </span><?=$comment->content?></div>
			</div>
			<?php		} ?>
		</div>
	<?php	} ?>