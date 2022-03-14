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
<h2 style="display: inline-block;"><i class='fa fa-check-square' aria-hidden='true'></i> Ticket: <span><?=$item->ticketNumber()?></span></h2>
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
        <div class="tableBody min-tablet marginTop_20">
	        <div class="tableRowHeader">
		        <div class="tableCell" style="width: 20%;">Requested By</div>
		        <div class="tableCell" style="width: 20%;">Requested On</div>
		        <div class="tableCell" style="width: 10%;">Status</div>
		        <div class="tableCell" style="width: 20%;">Product</div>
		        <div class="tableCell" style="width: 20%;">Serial #</div>
	        </div> <!-- end row header -->
	        <div class="tableRow">
		        <div class="tableCell">
			        <?=$request->customer->full_name()?>
		        </div>
		        <div class="tableCell">
			        <?=$item->request->date_request?>
		        </div>
		        <div class="tableCell">
			        <span class="value"><?=$item->status?></span>
		        </div>
		        <div class="tableCell">
			        <span class="value"><?=$item->product->code?></span>
		        </div>
		        <div class="tableCell">
			        <span class="value"><a href="/_monitor/asset/<?=$item->serial_number?>"><?=$item->serial_number?></a></span>
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
				<input type="button" name="btn_add_note" class="button" value="Add Comment" onclick="showForm('comment');" />
        <?php	if ($item->status == 'CLOSED') { ?>
		        <input type="submit" name="btn_reopen_item" class="button" value="Reopen Item" />
        <?php	} else { ?>
		        <input type="submit" name="btn_close_item" class="button" value="Close Item" />
        <?php	} ?>
	        </div>
        </div>
        <!--End first row-->
	</form>
	<!-- END Request Form -->
	<!--	 ==================================== -->
	
	<!-- Comment Form -->
	<div id="commentFormDiv" class="toggleContainer">
		<form name="commentForm" method="post" action="/_support/ticket">
		<input type="hidden" name="item_id" value="<?=$item->id?>" />
		<h2>Add Comment</h2>
		<div class="tableBody min-tablet">
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
    <br/><hr/><h2>Documents</h2>
    <?php
    if ($filesUploaded) {
    ?><br/>
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
<div style="width: 756px;">
<h2>Actions</h2>
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
    <?php	} else { ?>
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
<?php	} ?>
<?php	if (isset($rmalist) && $rmalist->count() > 0) { ?>
<div style="width: 756px;">
<h2>Authorized Returns</h2>
<?php		foreach ($rmas as $rma) { ?>
	<div class="tableBody min-tablet">
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
    <h3>Comments</h3>
    <?php		foreach ($comments as $comment) { ?>
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
<?php		} ?>
<!--End Request Item -->
<!-- END Comments Section -->
<!--	 ==================================== -->
<?php	} ?>
