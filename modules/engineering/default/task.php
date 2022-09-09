<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<link rel="stylesheet" href="/css/datepicker.min.css">
<script src="/js/datepicker.min.js"></script> 
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
    hr {
        border: 0;
        height: 5px;
        background-image: linear-gradient(to bottom, rgba(0, 0, 0, 0), rgba(0, 0, 0, 0.75), rgba(0, 0, 0, 0));
        width: 75%;
        margin: 50px;
        margin-left: 0px;
        margin-bottom: 25px;
    }
    
    section article.segment {
        padding-bottom: 100px;
    }

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
    
    input:disabled {
        color: #a1a1a1;
        background: #80808061;
    }
    
    #btn_submit {
        min-width: 175px;
        min-height: 50px;
        border-radius: 10px;
    }
    
    #submit-button-container {
        margin-top: 100px;
        position: fixed;
        bottom: 0;
        width: 100%;
    }
    
    #overlay {
        display:none;
        width: 100%;
        height: 100%;
        z-index: 100;
        background-color:#00000090;
        position:fixed;
        top: 0;
        left: 0px;
    }
    
    #duplicate_task_name, #prerequisite_task_name {
        color: #888888;
    }

    #popup_duplicate, #popup_prerequisite {
        border: solid 2px #000;
        border-radius: 5px;
        display: none;
        padding: 10px;
        position: absolute;
        top: 200px;
        left: 0px;
        background: #fff;
        width: 90%;
        height: 750px;
        z-index: 200;
        overflow-y: scroll;
    }
    
    #popup_duplicate_close, #popup_prerequisite_close {
        float: right;
        margin: 2px;
        font-size: 15px;
        cursor: pointer;
        font-weight: bold;
    }
    
    @media only screen and (max-width: 768px) {
        #popup_duplicate, #popup_prerequisite {
            width: 100%;
        }
    }
    
    <?php
        if ($_REQUEST['duplicate_btn_submit']) {
    ?>
        #popup_duplicate, #overlay {
            display:block;
        }
        
    <?php
        }
        if ($_REQUEST['prerequisite_btn_submit']) {
    ?>
        #popup_prerequisite, #overlay {
            display:block;
        }
        
    <?php
        }
    ?>
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
        
        $( "#btn_add_hours" ).click(function() {
            $( "#btn_add_hours" ).val("please wait...");
			$( "#method" ).val("Add Hours");
            $( "#task_form" ).submit();
            $( "#btn_add_hours" ).click(false);
        });
                
        $( "#btn_add_event" ).attr('disabled', 'disabled');
        
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
        
        $( "#date_event" ).change(function() {
            $( "#btn_add_event" ).removeAttr('disabled');
        });
        
        $( "#event_person_id" ).change(function() {
            $( "#btn_add_event" ).removeAttr('disabled');
        });
        
        $( "#hours_worked" ).change(function() {
            $( "#btn_add_event" ).removeAttr('disabled');
        });
        
        $( "#new_status" ).change(function() {
            $( "#btn_add_event" ).removeAttr('disabled');
        });
        
        $( "#notes" ).change(function() {
            $( "#btn_add_event" ).removeAttr('disabled');
        });
        
        // Initialize Duplicate Popup
        var close_duplicate_popup = document.getElementById("popup_duplicate_close");
        var overlay = document.getElementById("overlay");
        var popup_duplicate = document.getElementById("popup_duplicate");
        var btn_duplicate = document.getElementById("btn_duplicate");
        var duplicate_task_id = document.getElementById("duplicate_task_id");
        var duplicate_task_name = document.getElementById("duplicate_task_name");
        var duplicate_task_id_clear = document.getElementById("duplicate_task_id_clear");
        
        // Clear duplicate task ID
        duplicate_task_id_clear.onclick = function() {
            duplicate_task_id.value = '';
            duplicate_task_name.value = '(none)';
        };
        
        // Open Popup Event
        btn_duplicate.onclick = function() {
            popup_duplicate.style.display = 'block';
            overlay.style.display = 'block';
        };

        // Close Popup Event
        close_duplicate_popup.onclick = function() {
            popup_duplicate.style.display = 'none';
            overlay.style.display = 'none';
        };
        
        // Initialize Prerequisite Popup
        var close_prerequisite_popup = document.getElementById("popup_prerequisite_close");
        var overlay = document.getElementById("overlay");
        var popup_prerequisite = document.getElementById("popup_prerequisite");
        var btn_prerequisite = document.getElementById("btn_prerequisite");
        var prerequisite_task_id = document.getElementById("prerequisite_task_id");
        var prerequisite_task_name = document.getElementById("prerequisite_task_name");
        var prerequisite_task_id_clear = document.getElementById("prerequisite_task_id_clear");
        var popup_prerequisite_content = document.getElementById("popup_prerequisite_content");
        
        // Clear prerequisite task ID
        prerequisite_task_id_clear.onclick = function() {
            prerequisite_task_id.value = '';
            prerequisite_task_name.value = '(none)';
        };
        
        // Open Popup Event
        btn_prerequisite.onclick = function() {
            popup_prerequisite.style.display = 'block';
            overlay.style.display = 'block';
        };

        // Close Popup Event
        close_prerequisite_popup.onclick = function() {
            popup_prerequisite.style.display = 'none';
            overlay.style.display = 'none';
        }; 
          
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
               <textarea name="description" class="wide_100per" form="task_form"><?=strip_tags($form['description'])?></textarea>
            </div>
         </div>                      
      </div>
      <!-- End Fourth Row -->
      <!-- Start Fifth Row -->
      
      <div id="overlay"></div>
      
      <div class="tableBody min-tablet">
        <div class="tableRowHeader">
            <div class="tableCell">Prerequisite</div>
            <div class="tableCell">Difficulty</div>
            <div class="tableCell">Required Role</div>
            <div class="tableCell">Assign as Duplicate</div>
         </div>
         <div class="tableRow">           
            <div class="tableCell">
                <input type="hidden" id="prerequisite_task_id" name="prerequisite_task_id" value="<?=$form['prerequisite_task_id']?>" />
                <input type="text" id="prerequisite_task_name" name="prerequisite_task_name" value="<?=$form['prerequisite_task_name']?>" readonly='readonly'/>
                <input type="button" id="prerequisite_task_id_clear" name="prerequisite_task_id_clear" value="Clear" style="background:#999;"/>
               <br/>
                <input id="btn_prerequisite" type="button" name="btn_prerequisite" class="button" value="Search for Task"/>        
                <div id="popup_prerequisite">
                    <div class="popup_prerequisite_controls">
                        <span id="popup_prerequisite_close">X</span>
                    </div>
                    <div class="popup_prerequisite_content">
                        <h1>Find a task that is prerequisite to this one</h1>
                        <?php include(MODULES.'/engineering/partials/prerequisite_task_finder.php'); ?>
                    </div>
                </div>
            </div>
            <div class="tableCell">
               <select name="difficulty" class="value input">
                  <option value="EASY"<?php if ($form['difficulty'] == "EASY") print " selected"; ?>>Easy</option>
                  <option value="NORMAL"<?php if ($form['difficulty'] == "NORMAL") print " selected"; ?>>Normal</option>
                  <option value="HARD"<?php if ($form['difficulty'] == "HARD") print " selected"; ?>>Hard</option>
                  <option value="PROJECT"<?php if ($form['difficulty'] == "PROJECT") print " selected"; ?>>Project</option>
               </select>
            </div>
            <div class="tableCell">
               <select name="role_id" class="value input" style="max-width: 250px;">
                  <option value="">None</option>
                  <?php	foreach($engineeringRoles as $engineeringRole) { ?>
                    <option value="<?=$engineeringRole->id?>"<?php if ($engineeringRole->id == $form['role_id']) print " selected"; ?>><?=$engineeringRole->name?></option>
                  <?php	} ?>
               </select>
            </div>
            <div class="tableCell">
                <input type="hidden" id="duplicate_task_id" name="duplicate_task_id" value="<?=$form['duplicate_task_id']?>" />
                <input type="text" id="duplicate_task_name" name="duplicate_task_name" value="<?=$form['duplicate_task_name']?>" readonly='readonly'/>
                <input type="button" id="duplicate_task_id_clear" name="duplicate_task_id_clear" value="Clear" style="background:#999;"/>
               <br/>
                <input id="btn_duplicate" type="button" name="btn_duplicate" class="button" value="Search for Task"/>        
                <div id="popup_duplicate">
                    <div class="popup_duplicate_controls">
                        <span id="popup_duplicate_close">X</span>
                    </div>
                    <div class="popup_duplicate_content">
                        <h1>Find a task that is duplicate to this one</h1>
                        <?php include(MODULES.'/engineering/partials/duplicate_tasks_finder.php'); ?>
                    </div>
                </div>
            </div>
         </div>
      </div>
      <!-- End Fifth Row -->
      <!-- Start Fifth Row -->
      <?php	if ($task->id) { ?>      
               
      <!-- Start comment Row -->
      <h3>Testing Information</h3>
      <div class="tableBody min-tablet">
         <div class="tableRowHeader">
            <div class="tableCell">Update Testing Instructions</div>
         </div>
         <div class="tableRow">
            <div class="tableCell">
               <textarea id="testing_details" name="testing_details" class="wide_100per" form="task_form"><?=strip_tags($form['testing_details'])?></textarea>
            </div>
         </div>
      </div>
      <!-- End comment Row -->
      
      <h3>Comment Update</h3>
      <!-- Start comment Row -->
      <div class="tableBody min-tablet">
         <div class="tableRowHeader">
            <div class="tableCell">Add Task Comment</div>
         </div>
         <div class="tableRow">
            <div class="tableCell">
               <textarea id="task_comment" name="task_comment" class="wide_100per" form="task_form"></textarea>
            </div>
         </div>
      </div>
      <!-- End comment Row -->
                
      <h3>Event Update</h3>
      <div class="tableBody min-tablet">
         <div class="tableRowHeader">
            <div class="tableCell">Event Date</div>
            <div class="tableCell">Person</div>
            <div class="tableCell">Hours</div>
            <div class="tableCell">Set New Status (Currently: <?=$task->status?>)</div>
         </div>
         <div class="tableRow">
            <div class="tableCell">
               <input id="date_event" type="text" name="date_event" class="value input wide_100per" value="<?=date('m/d/Y H:i:s')?>" />
            </div>
            <div class="tableCell">
               <select id="event_person_id" name="event_person_id" class="value input wide_100per">
                  <?php	foreach ($people as $person) { ?>
                  <option value="<?=$person->id?>"<?php if ($person->id == $GLOBALS['_SESSION_']->customer->id) print " selected"; ?>><?=$person->code?></option>
                  <?php	} ?>
               </select>
            </div>
            <div class="tableCell">
               <input id="hours_worked" type="number" name="hours_worked" class="value input" value="0" form="task_form"/>
            </div>
            <div class="tableCell">
               <select id="new_status" name="new_status" class="value input wide_100per" form="task_form">
                  <option value=""></option>
                  <option value="new">New</option>
                  <option value="hold">Hold</option>
                  <option value="active">Active</option>
                  <option value="broken">Broken</option>
                  <option value="testing">Testing</option>
                  <option value="cancelled">Cancelled</option>
                  <option value="complete">Complete</option>
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
               <textarea id="notes" name="notes" class="wide_100per" form="task_form"></textarea>
            </div>
         </div>
      </div>
      <!-- End event description Row -->
      
      <!-- entire page button submit -->
      <div id="submit-button-container" class="tableBody min-tablet">
            <div class="tableRow button-bar">
                <input id="btn_submit" type="submit" name="btn_submit" class="button" value="Submit">
            </div>
      </div>
      
   </form>
   
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
         <pre>
            <?=strip_tags($comment->content)?>
         </pre>
         </div>
      </div>
      <?php	} ?>
   </div>
   
    <div style="width: 756px;">
        <br/><h3>Documents</h3><br/>
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
             <pre><?=strip_tags($event->description);?></pre>
             </div>
          </div>
      <?php	} ?>
   </div>
   <?php	} else {
   ?>
      <div id="submit-button-container" class="tableBody min-tablet">
            <div class="tableRow button-bar">
                <input id="btn_submit" type="submit" name="btn_submit" class="button" value="Submit">
            </div>
      </div>
   <?php
   }	
   ?>
</div>
