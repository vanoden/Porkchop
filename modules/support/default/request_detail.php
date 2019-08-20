<script language="Javascript">
	function goForm(selectedForm) {
		document.requestForm.action = '/_support/request_'+selectedForm;
		document.requestForm.submit();
	}
</script>

<div class="breadcrumbs">
	<a href="/_support/requests">Support Home</a>
	<a href="/_support/requests">Requests</a> &gt; Request Details
</div>

<h2 style="display: inline-block;">Request: <span><?=$request->code?></span></h2>
<?php include(MODULES.'/support/partials/search_bar.php'); ?>
<div style="width: 756px;">
	<?	if ($page->errorCount()) { ?>
	    <div class="form_error"><?=$page->errorString()?></div>
	<? } ?>
	<?	if ($page->success) { ?>
	    <div class="form_success"><?=$page->success?></div>
	<?	} ?>
	<form name="requestForm" method="post">
        <input type="hidden" name="request_id" value="<?=$request->id?>" />
        <!--	Start First Row-->
        <div class="tableBody min-tablet">
	        <div class="tableRowHeader">
		        <div class="tableCell" style="width: 25%;">Date Requested</div>
		        <div class="tableCell" style="width: 25%;">Requestor</div>
		        <div class="tableCell" style="width: 25%;">Organization</div>
		        <div class="tableCell" style="width: 13%;">Type</div>
		        <div class="tableCell" style="width: 12%;">Status</div>
	        </div> <!-- end row header -->
	        <div class="tableRow">
		        <div class="tableCell">
			        <span class="value"><?=$request->date_request?></span>
		        </div>
		        <div class="tableCell">
			        <span class="value"><?=isset($request->customer) ? $request->customer->full_name() : ""?></span>
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
        </div>
        <div class="container">
	        <input type="submit" name="btn_cancel" class="button" value="Cancel Request" />
        <?	if (in_array($request->status,array('CLOSED','COMPLETE','CANCELLED'))) { ?>
	        <input type="submit" name="btn_reopen" class="button" value="Reopen Request" />
        <?	} else { ?>
	        <input type="submit" name="btn_close" class="button" value="Close Request" />
        <?	} ?>
        </div>
        <!--End first row-->		

        <h3>Request Tickets</h3>
        <!--	Start Request Item-->
        <?	foreach ($items as $item) { ?>
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
		        
        <h3>Add Ticket</h3>
		        
        <!--	Start Request Item-->
        <div class="tableBody min-tablet">
	        <div class="tableRowHeader">
		        <div class="tableCell" style="width: 30%;">Product ID</div>
		        <div class="tableCell" style="width: 30%;">Serial Number</div>
		        <div class="tableCell" style="width: 20%;">Status</div>
	        </div> <!-- end row header -->
	        <div class="tableRow">
		        <div class="tableCell">
			        <select name="product_id" class="value input">
				        <option value="">N/A</option>
				        <?	foreach ($products as $product) { ?>
				            <option value="<?=$product->id?>"><?=$product->code?></option>
				        <?	} ?>
		        </select>
		        </div>
		        <div class="tableCell">
			        <input type="text" name="serial_number" class="value input" />
		        </div>
		        <div class="tableCell">
			        <select name="item_status" class="value input">
				        <?	foreach ($statuses as $status) { ?>
				        <option value="<?=$status?>"><?=ucwords(str_replace("_"," ", strtolower($status)))?></option>
				        <?	} ?>
			        </select>
		        </div>
	        </div>
        </div>
		        
        <div class="tableBody min-tablet marginBottom_20">
	        <div class="tableRowHeader">
		        <div class="tableCell" style="width: 100%;">Description</div>
	        </div> <!-- end row header -->
	        <div class="tableRow">
		        <div class="tableCell">
			        <textarea class="value input" name="item_description" style="width: 100%"></textarea>
		        </div>
	        </div>
	        <div class="tableRow button-bar">
		        <input type="submit" name="btn_add_item" class="button" value="Add Item" />
	        </div>
        </div>		
        <!--End Request Item -->
        <?	if (count($actions) > 0) { ?>
        <div style="width: 756px;">
        <h2>Actions</h2>
        <?php
           if (!empty($actions)) {
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
        <? } else { ?>
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
        <?	    }
            } 
        }
        ?>
        </div>
        <?	} ?>
        <?	if (isset($supportItemComments) && count($supportItemComments) > 0) { ?>
            <!--	Start Request Item-->
            <h3>Comments</h3>
            <?php	
                foreach ($supportItemComments as $comment) { 
                $comment = array_pop($comment);
                if ($comment->date_comment) {
            ?>
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
			            <?=isset($comment->author) ? $comment->author->full_name() : ""?>
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
            <?	}	
             } ?>
        <?	} ?>
	</form>
</div>
