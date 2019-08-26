<style>
    .eventLogEntry {
        max-width: 200px; 
        overflow:auto; 
        padding: 25px;
    }
    .event-log-description {
        background-color: white;
    }
</style>
<script>
    window.onload = function() {
        $( "textarea:odd" ).css( "background-color", "#eeeff7" );
        //$( "#date_added" ).datepicker();
        //$( "#date_due" ).datepicker();
    };
</script>
<div>
  <div class="breadcrumbs">
     <a class="breadcrumb" href="/_engineering/home">Engineering</a>
     <a class="breadcrumb" href="/_engineering/tasks">Tasks</a> > Task Detail
  </div>
   <?php include(MODULES.'/engineering/partials/search_bar.php'); ?> 
   <form name="task_form" action="/_engineering/task" method="post">
      <input type="hidden" name="task_id" value="<?=$task->id?>" />
      <h2>Engineering Task: 
	  	<? if ($form['code']) { ?>
	  		<span><?php print " ".$form['code'];?></span>
		<? } ?>
	  </h2>
      <?	if ($page->errorCount()) { ?>
      <div class="form_error"><?=$page->errorString()?></div>
      <?	}
         if ($page->success) { ?>
      		<div class="form_success"><?=$page->success?> [<a href="/_engineering/tasks">Finished</a>] | [<a href="/_engineering/task">Create Another</a>] </div>
      <?	} ?>
      <?	if (! isset($task->id)) { ?>
      <div class="container_narrow">
         <div class="label">Code</div>
         <input type="text" name="code" class="value input" value="<?=$form['code']?>" />
      </div>
      <?	} ?>
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
            <div class="tableCell"><input type="text" name="title" class="value input wide_100per" value="<?=$form['title']?>" /></div>
            <div class="tableCell">
               <select name="product_id" class="value input wide_100per">
                  <option value="">Select</option>
                  <?	foreach ($products as $product) { ?>
                  <option value="<?=$product->id?>"<? if ($product->id == $form['product_id']) print " selected"; ?>><?=$product->title?></option>
                  <?	} ?>
               </select>
            </div>
            <div class="tableCell">
               <?	if (isset($task->id)) { ?>
                <span class="value"><?=$form['date_added']?></span>
               <?	} else { ?>
                <input id="date_added" type="text" name="date_added" class="value input wide_100per" value="<?=$form['date_added']?>" />
               <?	} ?>
            </div>
            <div class="tableCell">
               <input id="date_due" type="text" name="date_due" class="value input wide_100per" value="<?=$form['date_due']?>" />
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
                  <option value="bug"<? if ($form['type'] == "BUG") print " selected"; ?>>Bug</option>
                  <option value="feature"<? if ($form['type'] == "FEATURE") print " selected"; ?>>Feature</option>
                  <option value="test"<? if ($form['type'] == "TEST") print " selected"; ?>>Test</option>
               </select>
            </div>
            <div class="tableCell">
               <?	if (isset($task->id)) { ?>
               <span class="value"><?=$task->status?></span>
               <?	} else { ?>
               <select name="status" class="value input wide_100per">
                  <option value="new"<? if ($form['status'] == "NEW") print " selected"; ?>>New</option>
                  <option value="hold"<? if ($form['status'] == "HOLD") print " selected"; ?>>Hold</option>
                  <option value="active"<? if ($form['status'] == "ACTIVE") print " selected"; ?>>Active</option>
                  <option value="cancelled"<? if ($form['status'] == "CANCELLED") print " selected"; ?>>Cancelled</option>
                  <option value="testing"<? if ($form['status'] == "TESTING") print " selected"; ?>>Testing</option>
                  <option value="complete"<? if ($form['status'] == "COMPLETE") print " selected"; ?>>Complete</option>
               </select>
               <?	}	?>
            </div>
            <div class="tableCell">
               <select name="priority" class="value input wide_100per">
                  <option value="normal"<? if ($form['priority'] == "NORMAL") print " selected"; ?>>Normal</option>
                  <option value="important"<? if ($form['priority'] == "IMPORTANT") print " selected"; ?>>Important</option>
                  <option value="urgent"<? if ($form['priority'] == "URGENT") print " selected"; ?>>Urgent</option>
                  <option value="critical"<? if ($form['priority'] == "CRITICAL") print " selected"; ?>>Critical</option>
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
               <?	if (isset($task->id)) {
                  $requestor = $task->requestedBy(); ?>
               <span class="value"><?=$requestor->first_name?> <?=$requestor->last_name?></span>
               <?	} else { ?>
               <select name="requested_id" class="value input wide_100per">
                  <option value="">Select</option>
                  <?	foreach($people as $person) { ?>
                  <option value="<?=$person->id?>"<? if ($person->id == $form['requested_id']) print " selected"; ?>><?=$person->login?></option>
                  <?	} ?>
               </select>
               <?	}	?>
            </div>
            <div class="tableCell">
               <select name="assigned_id" class="value input wide_100per">
                  <option value="">Unassigned</option>
                  <?	foreach($techs as $person) { ?>
                    <option value="<?=$person->id?>"<? if ($person->id == $form['assigned_id']) print " selected"; ?>><?=$person->login?></option>
                  <?	} ?>
               </select>
            </div>
            <div class="tableCell">
               <select name="release_id" class="value input wide_100per">
                  <option value="">Not Scheduled</option>
                  <?	foreach($releases as $release) { ?>
                  <option value="<?=$release->id?>"<? if ($release->id == $form['release_id']) print " selected"; ?>><?=$release->title?></option>
                  <?	} ?>
               </select>
            </div>
            <div class="tableCell">
               <select name="project_id" class="value input wide_100per">
                  <option value="">No Project</option>
                  <?	foreach($projects as $project) { ?>
                  <option value="<?=$project->id?>"<? if ($project->id == $form['project_id'] || $project->id == $_REQUEST['project_id']) print " selected"; ?>><?=$project->title?></option>
                  <?	} ?>
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
               <textarea name="description" class="wide_100per"><?=$form['description']?></textarea>
            </div>
         </div>
         <div class="tableRow">
            <div class="tableCell">
               <strong>Prerequisite</strong>
               <select name="prerequisite_id" class="value input" style="max-width: 250px;">
                  <option value="">None</option>
                  <?php	foreach($tasklist as $prerequisiteTask) { ?>
                    <option value="<?=$prerequisiteTask->id?>"<? if ($prerequisiteTask->id == $form['prerequisite_id']) print " selected"; ?>><?=$prerequisiteTask->title?></option>
                  <?php	} ?>
               </select>
            </div>
         </div>
       <div class="tableRow button-bar">
        <input type="submit" name="btn_submit" class="button" value="Submit">
       </div>
      </div>
      <!-- End Fourth Row -->
      <!-- Start Fifth Row -->
      <?	if ($task->id) { ?>
      <h3>Event Update</h3>
      <div class="tableBody min-tablet">
         <div class="tableRowHeader">
            <div class="tableCell">Event Date</div>
            <div class="tableCell">Person</div>
            <div class="tableCell">New Status</div>
         </div>
         <div class="tableRow">
            <div class="tableCell">
               <input type="text" name="date_event" class="value input wide_100per" value="<?=date('m/d/Y H:i:s')?>" />
            </div>
            <div class="tableCell">
               <select name="event_person_id" class="value input wide_100per">
                  <?	foreach ($people as $person) { ?>
                  <option value="<?=$person->id?>"<? if ($person->id == $GLOBALS['_SESSION_']->customer->id) print " selected"; ?>><?=$person->code?></option>
                  <?	} ?>
               </select>
            </div>
            <div class="tableCell">
               <select name="new_status" class="value input wide_100per">
                  <option value="new"<? if ($task->status == 'NEW') print ' selected'; ?>>New</option>
                  <option value="hold"<? if ($task->status == 'HOLD') print ' selected'; ?>>Hold</option>
                  <option value="active"<? if ($task->status == 'ACTIVE') print ' selected'; ?>>Active</option>
                  <option value="broken"<? if ($task->status == 'BROKEN') print ' selected'; ?>>Broken</option>
                  <option value="testing"<? if ($task->status == 'TESTING') print ' selected'; ?>>Testing</option>
                  <option value="cancelled"<? if ($task->status == 'CANCELLED') print ' selected'; ?>>Cancelled</option>
                  <option value="complete"<? if ($task->status == 'COMPLETE') print ' selected'; ?>>Complete</option>
               </select>
            </div>
         </div>
      </div>
      <!-- End Fifth Row -->	
      <!-- Start Sixth Row -->
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
            <input type="submit" name="btn_add_event" class="button" value="Add Event" />
         </div>
      </div>
      <!-- End Sixth Row -->
   </form>
   <!--	Start First Row-->
   <h3>Event Log</h3>
   <div class="tableBody min-tablet">
      <div class="tableRowHeader">
         <div class="tableCell" style="width: 20%;">Date</div>
         <div class="tableCell" style="width: 15%;">Person</div>
         <div class="tableCell" style="width: 65%;">Description</div>
      </div>
      <?	foreach ($events as $event) {
         $person = $event->person();
         ?>
      <div class="tableRow">
         <div class="tableCell">
            <i><?=$event->date_event?></i>
         </div>
         <div class="tableCell">
            <strong><?=$person->login?></strong>
         </div>
         <div class="tableCell eventLogEntry">
         <textarea class="event-log-description" readonly="readonly" style="border: solid 1px #EFEFEF; border-radius: 5px; height: 50px;"><?=str_replace(" ","&nbsp;",str_replace("\n","<br/>\n",$event->description)) ;?></textarea>
         </div>
      </div>
      <?	} ?>
   </div>
   <?	}	?>
</div>
