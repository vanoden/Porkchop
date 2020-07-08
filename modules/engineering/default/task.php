<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<link rel="stylesheet" href="/css/datepicker.min.css">
<script src="/js/datepicker.min.js"></script> 

<style>
    hr {
        border: 0;
        height: 5px;
        background-image: linear-gradient(to bottom, rgba(0, 0, 0, 0), rgba(0, 0, 0, 0.75), rgba(0, 0, 0, 0));
        width: 75%;
        margin: 50px;
        margin-left: 0px;
        margin-bottom: 25px;
    }
</style>

<script>
   $( function() {
        const picker = datepicker('#date_due', {
          formatter: (input, date, instance) => {
            const value = date.toLocaleDateString()
            input.value = value
          }
        });
   } );
</script>
<style>
    .eventLogEntry {
        max-width: 200px; 
        overflow:auto; 
        padding: 25px;
    }
    .event-log-description {
        border: solid 1px #EFEFEF; 
        border-radius: 5px; 
        height: 50px;
        background-color: white;
    }
</style>
<script>
    $(document).ready(function () {
        $( "textarea:odd" ).css( "background-color", "#eeeff7" );
        
        // disable buttons to prevent duplicate clicks
        $( "#btn_submit" ).click(function() {
            $( "#btn_submit" ).val("please wait...");
			$( "#method" ).val("Submit");
            $( "#task_form" ).submit();
            $( "#btn_submit" ).click(false);
        });
        $( "#btn_add_comment" ).click(function() {
            $( "#btn_add_comment" ).val("please wait...");
			$( "#method" ).val("Add Comment");
            $( "#task_form" ).submit();
            $( "#btn_add_comment" ).click(false);
        });
        
        $( "#btn_add_hours" ).click(function() {
            $( "#btn_add_hours" ).val("please wait...");
			$( "#method" ).val("Add Hours");
            $( "#task_form" ).submit();
            $( "#btn_add_hours" ).click(false);
        });
        
        $( "#btn_add_event" ).click(function() {
            $( "#btn_add_event" ).val("please wait...");
			$( "#method" ).val("Add Event");
            $( "#task_form" ).submit();
            $( "#btn_add_event" ).click(false);
        });
        $( "#btn_upload" ).click(function() {
            $( "#btn_add_event" ).val("please wait...");
			$( "#method" ).val("Upload");
            $( "#task_form" ).submit();
            $( "#btn_add_event" ).click(false);
        });
    });
</script>
<div>
  <div class="breadcrumbs">
     <a class="breadcrumb" href="/_engineering/home">Engineering</a>
     <a class="breadcrumb" href="/_engineering/tasks">Tasks</a> > Task Detail
  </div>
   <?php include(MODULES.'/engineering/partials/search_bar.php'); ?> 
   <form id="task_form" name="task_form" action="/_engineering/task" method="post">
      <input type="hidden" name="task_id" value="<?=$task->id?>" />
	  <input type="hidden" name="method" id="method" value="" />
      <h2>Engineering Task: 
	  	<?php if ($form['code']) { ?>
	  		<span><a href="/_engineering/task/<?=$form['code'];?>"><?php print " ".$form['code'];?></a></span>
		<?php } ?>
	  </h2>
      <?php	if ($page->errorCount()) { ?>
      <div class="form_error"><?=$page->errorString()?></div>
      <?php	}
         if ($page->success) { ?>
      		<div class="form_success"><?=$page->success?> [<a href="/_engineering/tasks">Finished</a>] | [<a href="/_engineering/task">Create Another</a>] </div>
      <?php	} ?>
      <?php	if (! isset($task->id)) { ?>
      <div class="container_narrow">
         <div class="label">Code</div>
         <input type="text" name="code" class="value input" value="<?=$form['code']?>" />
      </div>
      <?php	} ?>
      <!--	Start First Row-->
      <div class="tableBody min-tablet">
         <div class="tableRowHeader">
            <div class="tableCell" style="width: 25%;">Title</div>
            <div class="tableCell" style="width: 25%;">Product</div>
            <div class="tableCell" style="width: 25%;">Date Requested</div>
            <div class="tableCell" style="width: 25%;">Date Due</div>
         </div>
         <!-- end row header -->
         <div class="tableRow">         
            <div class="tableCell"><input type="text" name="title" class="value input wide_100per" value="<?=preg_replace("/['|\"]/", "", $form['title']);?>" /></div>
            <div class="tableCell">
               <select name="product_id" class="value input wide_100per">
                  <option value="">Select</option>
                  <?php	foreach ($products as $product) { ?>
                  <option value="<?=$product->id?>"<?php if ($product->id == $form['product_id']) print " selected"; ?>><?=$product->title?></option>
                  <?php	} ?>
               </select>
            </div>
            <div class="tableCell">
               <?php	if (isset($task->id)) { ?>
                <span class="value"><?=$form['date_added']?></span>
               <?php	} else { ?>
                <input id="date_added" type="text" name="date_added" class="value input wide_100per" value="<?=$form['date_added']?>" />
               <?php	} ?>
            </div>
            <div class="tableCell">
               <input id="date_due" type="text" name="date_due" class="value input wide_100per" value="<?=$form['date_due']?>" autocomplete="off"/>
            </div>
         </div>
      </div>
      <!--End first row-->
      <!--	Start Second Row-->
      <div class="tableBody min-tablet">
         <div class="tableRowHeader">
            <div class="tableCell" style="width: 25%;">Time Estimate (hrs)</div>
            <div class="tableCell" style="width: 25%;">Type</div>
            <div class="tableCell" style="width: 25%;">Status</div>
            <div class="tableCell" style="width: 25%;">Priority</div>
         </div>
         <div class="tableRow">
            <div class="tableCell"><input type="text" name="estimate" class="value input wide_100per" value="<?=$form['estimate']?>" /></div>
            <div class="tableCell">
               <select name="type" class="value input wide_100per">
                  <option value="bug"<?php if ($form['type'] == "BUG") print " selected"; ?>>Bug</option>
                  <option value="feature"<?php if ($form['type'] == "FEATURE") print " selected"; ?>>Feature</option>
                  <option value="test"<?php if ($form['type'] == "TEST") print " selected"; ?>>Test</option>
               </select>
            </div>
            <div class="tableCell">
               <?php	if (isset($task->id)) { ?>
               <span class="value"><?=$task->status?></span>
               <?php	} else { ?>
               <select name="status" class="value input wide_100per">
                  <option value="new"<?php if ($form['status'] == "NEW") print " selected"; ?>>New</option>
                  <option value="hold"<?php if ($form['status'] == "HOLD") print " selected"; ?>>Hold</option>
                  <option value="active"<?php if ($form['status'] == "ACTIVE") print " selected"; ?>>Active</option>
                  <option value="cancelled"<?php if ($form['status'] == "CANCELLED") print " selected"; ?>>Cancelled</option>
                  <option value="testing"<?php if ($form['status'] == "TESTING") print " selected"; ?>>Testing</option>
                  <option value="complete"<?php if ($form['status'] == "COMPLETE") print " selected"; ?>>Complete</option>
               </select>
               <?php	}	?>
            </div>
            <div class="tableCell">
               <select name="priority" class="value input wide_100per">
                  <option value="normal"<?php if ($form['priority'] == "NORMAL") print " selected"; ?>>Normal</option>
                  <option value="important"<?php if ($form['priority'] == "IMPORTANT") print " selected"; ?>>Important</option>
                  <option value="urgent"<?php if ($form['priority'] == "URGENT") print " selected"; ?>>Urgent</option>
                  <option value="critical"<?php if ($form['priority'] == "CRITICAL") print " selected"; ?>>Critical</option>
               </select>
            </div>
         </div>
      </div>
      <!--End second row-->
      <!--	Start Third Row-->
      <div class="tableBody min-tablet">
         <div class="tableRowHeader">
            <div class="tableCell" style="width: 25%;">Request By</div>
            <div class="tableCell" style="width: 25%;">Assigned To</div>
            <div class="tableCell" style="width: 25%;">Release</div>
            <div class="tableCell" style="width: 25%;">Project</div>
         </div>
         <div class="tableRow">
            <div class="tableCell">
               <?php	if (isset($task->id)) {
                  $requestor = $task->requestedBy(); ?>
               <span class="value"><?=$requestor->first_name?> <?=$requestor->last_name?></span>
               <?php	} else { ?>
               <select name="requested_id" class="value input wide_100per">
                  <option value="">Select</option>
                  <?php	foreach($people as $person) { ?>
                  <option value="<?=$person->id?>"<?php if ($person->id == $form['requested_id']) print " selected"; ?>><?=$person->login?></option>
                  <?php	} ?>
               </select>
               <?php	}	?>
            </div>
            <div class="tableCell">
               <select name="assigned_id" class="value input wide_100per">
                  <option value="">Unassigned</option>
                  <?php	foreach($techs as $person) { ?>
                    <option value="<?=$person->id?>"<?php if ($person->id == $form['assigned_id']) print " selected"; ?>><?=$person->login?></option>
                  <?php	} ?>
               </select>
            </div>
            <div class="tableCell">
               <select name="release_id" class="value input wide_100per">
                  <option value="">Not Scheduled</option>
                  <?php	foreach($releases as $release) { ?>
                  <option value="<?=$release->id?>"<?php if ($release->id == $form['release_id']) print " selected"; ?>><?=$release->title?></option>
                  <?php	} ?>
               </select>
            </div>
            <div class="tableCell">
               <select name="project_id" class="value input wide_100per">
                  <option value="">No Project</option>
                  <?php	foreach($projects as $project) { ?>
                  <option value="<?=$project->id?>"<?php if ($project->id == $form['project_id'] || $project->id == $_REQUEST['project_id']) print " selected"; ?>><?=$project->title?></option>
                  <?php	} ?>
               </select>
            </div>
         </div>
      </div>
      <!--End Third row-->	
      <!-- Start Fourth Row -->
      <div class="tableBody min-tablet">
         <div class="tableRowHeader">
            <div class="tableCell">Description</div>
         </div>
         <div class="tableRow">
            <div class="tableCell">
               <textarea name="description" class="wide_100per"><?=strip_tags($form['description'])?></textarea>
            </div>
         </div>
         <div class="tableRow">
            <div class="tableCell">
               <strong>Prerequisite</strong>
               <select name="prerequisite_id" class="value input" style="max-width: 250px;">
                  <option value="">None</option>
                  <?php	foreach($tasklist as $prerequisiteTask) { ?>
                    <option value="<?=$prerequisiteTask->id?>"<?php if ($prerequisiteTask->id == $form['prerequisite_id']) print " selected"; ?>><?=$prerequisiteTask->title?></option>
                  <?php	} ?>
               </select>
            </div>
         </div>
       <div class="tableRow button-bar">
        <input id="btn_submit" type="submit" name="btn_submit" class="button" value="Submit">
       </div>
      </div>
      <!-- End Fourth Row -->

      <!-- Start Fifth Row -->
      <?php	if ($task->id) { ?>      
      
      <hr/>
      <h3>Comment Update</h3>
      <!-- Start comment Row -->
      <div class="tableBody min-tablet">
         <div class="tableRowHeader">
            <div class="tableCell">Add Task Comment</div>
         </div>
         <div class="tableRow">
            <div class="tableCell">
               <textarea name="content" class="wide_100per"></textarea>
            </div>
         </div>
         <div class="tableRow button-bar">
            <input id="btn_add_comment" type="submit" name="btn_add_comment" class="button" value="Add Comment" />
         </div>
      </div>
      <!-- End comment Row -->
          
       <!--	Start First Row-->
       <h3>Comments</h3>
       <div class="tableBody min-tablet">
          <div class="tableRowHeader">
             <div class="tableCell" style="width: 20%;">Date</div>
             <div class="tableCell" style="width: 15%;">Person</div>
             <div class="tableCell" style="width: 65%;">Description</div>
          </div>
          <?php	
            foreach ($commentsList as $comment) {
            $person = $comment->person();
             ?>
          <div class="tableRow">
             <div class="tableCell">
                <i><?=date("M dS Y h:i A", $comment->timestamp_added)?></i>
             </div>
             <div class="tableCell">
               <strong><?=$person->login?></strong>
             </div>
             <div class="tableCell eventLogEntry">
             <textarea class="event-log-description" readonly="readonly">
                <?=strip_tags($comment->content)?>
             </textarea>
             </div>
          </div>
          <?php	} ?>
       </div>
      <hr/>
      <h3>Event Update</h3>
      <div class="tableBody min-tablet">
         <div class="tableRowHeader">
            <div class="tableCell">Event Date</div>
            <div class="tableCell">Person</div>
            <div class="tableCell">Hours</div>
            <div class="tableCell">New Status</div>
         </div>
         <div class="tableRow">
            <div class="tableCell">
               <input type="text" name="date_event" class="value input wide_100per" value="<?=date('m/d/Y H:i:s')?>" />
            </div>
            <div class="tableCell">
               <select name="event_person_id" class="value input wide_100per">
                  <?php	foreach ($people as $person) { ?>
                  <option value="<?=$person->id?>"<?php if ($person->id == $GLOBALS['_SESSION_']->customer->id) print " selected"; ?>><?=$person->code?></option>
                  <?php	} ?>
               </select>
            </div>
            <div class="tableCell">
               <input type="text" name="hours_worked" class="value input" value="0" />
            </div>
            <div class="tableCell">
               <select name="new_status" class="value input wide_100per">
                  <option value="new"<?php if ($task->status == 'NEW') print ' selected'; ?>>New</option>
                  <option value="hold"<?php if ($task->status == 'HOLD') print ' selected'; ?>>Hold</option>
                  <option value="active"<?php if ($task->status == 'ACTIVE') print ' selected'; ?>>Active</option>
                  <option value="broken"<?php if ($task->status == 'BROKEN') print ' selected'; ?>>Broken</option>
                  <option value="testing"<?php if ($task->status == 'TESTING') print ' selected'; ?>>Testing</option>
                  <option value="cancelled"<?php if ($task->status == 'CANCELLED') print ' selected'; ?>>Cancelled</option>
                  <option value="complete"<?php if ($task->status == 'COMPLETE') print ' selected'; ?>>Complete</option>
               </select>
            </div>
         </div>
      </div>
      <!-- End Event Update Row -->

      <!-- Start event description Row -->
      <div class="tableBody min-tablet">
         <div class="tableRowHeader">
            <div class="tableCell">Event Description</div>
         </div>
         <div class="tableRow">
            <div class="tableCell">
               <textarea name="notes" class="wide_100per"></textarea>
            </div>
         </div>
         <div class="tableRow button-bar">
            <input id="btn_add_event" type="submit" name="btn_add_event" class="button" value="Add Event" />
         </div>
      </div>
      <!-- End event description Row -->
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
        <form name="repoUpload" action="/_engineering/task/<?=$form['code'];?>" method="post" enctype="multipart/form-data">
        <div class="container">
            <span class="label">Upload File</span>
            <input type="hidden" name="repository_name" value="<?=$repository?>" />
            <input type="hidden" name="type" value="engineering task" />
            <input type="file" name="uploadFile" />
            <input type="submit" name="btn_upload" class="button" value="Upload" />
        </div>
        </form>
        <br/><br/>
    </div>

   <!--	Start First Row-->
   <h3>Event Log</h3>
   <div class="tableBody min-tablet">
      <div class="tableRowHeader">
         <div class="tableCell" style="width: 15%;">Date</div>
         <div class="tableCell" style="width: 12%;">Person</div>
         <div class="tableCell" style="width: 10%;">Hours</div>
         <div class="tableCell" style="width: 63%;">Description</div>
      </div>
      <?php	
        foreach ($events as $event) {
         $person = $event->person();
         ?>
          <div class="tableRow">
             <div class="tableCell">
                <i><?=$event->date_event?></i>
             </div>
             <div class="tableCell">
                <strong><?=$person->login?></strong>
             </div>
			 <div class="tableCell">
				<span><?=$event->hours_worked?></span>
			 </div>
             <div class="tableCell eventLogEntry">
             <textarea class="event-log-description" readonly="readonly"><?=strip_tags($event->description);?></textarea>
             </div>
          </div>
      <?php	} ?>
   </div>
   <?php	}	?>
</div>
