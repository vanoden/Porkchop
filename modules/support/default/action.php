<style>
   .event-log-description {
        background-color: white;
        min-width: 75%; 
        overflow:auto; 
        padding: 25px;
        min-height: 100px;
        border: solid 1px #EFEFEF; 
        border-radius: 5px; 
        height: 50px;
   }
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
   	var forms = ['event','assign'];
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
<form name="action_form" method="post" action="/_support/action">
   <input type="hidden" name="action_id" value="<?=$action->id?>" />
   <div style="width: 756px;">
      <div class="breadcrumbs">
         <a href="/_support/requests">Support Home</a>
         <a href="/_support/requests" class="breadcrumbs">Requests</a>
         <a href="/_support/request_detail/<?=$request->code?>" class="breadcrumbs">Request <?=$request->code?></a>
         <a href="/_support/request_item/<?=$item->id?>" class="breadcrumbs">Line <?=$item->line?></a>
         <input type="hidden" name="action_id" value="<?=$action->id?>" />
         <?php	if ($page->errorCount()) { ?>
            <div class="form_error"><?=$page->errorString()?></div>
         <?php } ?>
         <?php	if ($page->success) { ?>
            <div class="form_success"><?=$page->success?></div>
         <?php	} ?>
      </div>
   </div>
   <h2 style="display: inline-block;"><i class='fa fa-check-square-o' aria-hidden='true'></i> Request Action <?=$request->code?>-<?=$item->line?>-<?=$action->id?></h2>
   <?php include(MODULES.'/support/partials/search_bar.php'); ?>
   <div class="tableBody min-tablet marginTop_20">
      <div class="tableRowHeader">
         <div class="tableCell">Request Code</div>
         <div class="tableCell">Request Date</div>
         <div class="tableCell">Organization</div>
         <div class="tableCell">Submitted By</div>
         <div class="tableCell">Request Status</div>
      </div>
      <div class="tableRow">
         <div class="tableCell"><a href="/_support/request_detail/<?=$request->code?>"><?=$request->code?></a></div>
         <div class="tableCell"><?=$request->date_request?></div>
         <div class="tableCell"><?=$request->customer->organization->name?></div>
         <div class="tableCell"><?=$request->customer->full_name()?></div>
         <div class="tableCell"><?=$request->status?></div>
      </div>
      <div class="tableRowHeader">
         <div class="tableCell">Ticket #</div>
         <div class="tableCell">Entered By</div>
         <div class="tableCell">Action Type</div>
         <div class="tableCell">Assigned To</div>
         <div class="tableCell">Action Status</div>
      </div>
      <div class="tableRow">
         <div class="tableCell"><a href="/_support/request_item/<?=$item->id?>"><?=$item->ticketNumber()?></a></div>
         <div class="tableCell"><?=$action->requestedBy->full_name()?></div>
         <div class="tableCell"><?=$action->type?></div>
         <div class="tableCell"><?=$assignedTo?></div>
         <div class="tableCell"><?=$action->status?></div>
      </div>
      <div class="tableRowHeader">
         <div class="tableCell">Product</div>
         <div class="tableCell">Name</div>
         <div class="tableCell">Serial Number</div>
         <div class="tableCell"></div>
         <div class="tableCell"></div>
      </div>
      <div class="tableRow">
         <div class="tableCell"><?=$item->product->code?></div>
         <div class="tableCell">
            <pre><?=strip_tags($item->product->description)?></pre>
         </div>
         <div class="tableCell"><a href="/_monitor/admin_details/<?=$item->serial_number?>/<?=$item->product->code?>"><?=$item->serial_number?></a></div>
         <div class="tableCell"></div>
         <div class="tableCell"></div>
      </div>
   </div>
   <div class="tableBody min-tablet marginTop_20">
      <div class="tableRowHeader">
         <div class="tableCell">Description</div>
      </div>
      <div class="tableRow">
         <div class="tableCell">     
             <pre><?=strip_tags($action->description)?></pre>
         </div>
      </div>
   </div>
   <div class="tableBody min-tablet marginTop_20">
      <div class="form_footer">
         <input type="button" name="btn_show" class="button" value="Add Event" onclick="showForm('event');" />
         <input type="button" name="btn_show" class="button" value="Assign Action" onclick="showForm('assign');" />
      </div>
   </div>
   <div class="toggleContainer" id="eventFormDiv">
    <form name="eventForm" method="post" action="/_support/action">
       <input type="hidden" name="action_id" value="<?=$action->id?>" />
       <h2>Add Event</h2>
       <div class="container_narrow">
          <span class="label">Event Date</span>
          <input type="text" name="date_event" class="value input" value="now" />
       </div>
       <div class="container_narrow">
          <span class="label">User</span>
          <select name="user_id" class="value input">
             <option value="">Select</option>
             <?php	foreach ($admins as $admin) { ?>
             <option value="<?=$admin->id?>"<?php if ($admin->id == $GLOBALS['_SESSION_']->customer->id) print " selected"; ?>><?=$admin->full_name()?></option>
             <?php	} ?>
          </select>
       </div>
       <div class="container_narrow">
          <span class="label">New Status</span>
          <select name="status" class="value input">
             <option value="ACTIVE">Active</option>
             <option value="PENDING CUSTOMER">Pending Customer</option>
             <option value="PENDING VENDOR">Pending Vendor</option>
             <option value="CANCELLED">Cancelled</option>
             <option value="COMPLETE">Complete</option>
          </select>
       </div>
      <div class="container_narrow">
        <span class="label">Hours Worked</span>
        <input type="text" name="hours_worked" class="value input" value="0" />
      </div>
       <div class="container">
          <span class="label">Description</span>
          <textarea name="description" class="value input" style="width: 650px"></textarea>
       </div>
       <div class="form_footer">
          <input type="submit" name="btn_add_event" class="button" value="Add Event" />
          <input type="button" name="btn_cancel" value="Cancel" class="button" onclick="hideForm('event');" />
       </div>
    </form>
</div>
<div class="toggleContainer" id="assignFormDiv">
   <form name="assignForm" method="post" action="/_support/action">
      <input type="hidden" name="action_id" value="<?=$action->id?>" />
      <h2>Assign Action</h2>
      <div class="container">
         <span class="label">User</span>
         <select name="assigned_id" class="value input">
            <option value="">Select</option>
            <?php	foreach ($admins as $admin) { ?>
            <option value="<?=$admin->id?>"<?php if ($admin->id == $GLOBALS['_SESSION_']->customer->id) print " selected"; ?>><?=$admin->full_name()?></option>
            <?php	} ?>
         </select>
      </div>
      <div class="form_footer">
         <input type="submit" name="btn_assign_action" class="button" value="Assign Action" />
         <input type="button" name="btn_cancel" value="Cancel" class="button" onclick="hideForm('assign');" />
      </div>
   </form>
</div>

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
    <form name="repoUpload" action="/_support/action/<?=$action->id?>" method="post" enctype="multipart/form-data">
    <div class="container">
        <span class="label">Upload File</span>
        <input type="hidden" name="repository_name" value="<?=$repository?>" />
        <input type="hidden" name="type" value="support action" />
        <input type="file" name="uploadFile" />
        <input type="submit" name="btn_submit" class="button" value="Upload" />
    </div>
    </form>
    <br/><br/>
</div>

<h2>History</h2>
<?php	foreach ($events as $event) {?>
<table style="width: 100%; padding-bottom: 10px;">
   <tr>
      <th>Event Date</th>
      <th>User</th>
      <th>Hours Worked</th>
   </tr>
   <tr>
      <td><?=$event->date_event?></td>
      <td><?=$event->user->full_name()?></td>
      <td><?=$event->hours?></td>
   </tr>
   <tr>
      <th colspan="3">Description</th>
   <tr>
   <td colspan="3">	    
     <pre><?=strip_tags($event->description)?></pre>
   </td>
   </tr>
   </tr>
</table>
<?php	}  ?>
</div>
