<div class="breadcrumbs">
 <a href="/_engineering/home">Engineering</a>
 <a href="/_engineering/projects">Projects</a> > Project Details
</div>
<?php include(MODULES.'/engineering/partials/search_bar.php'); ?>
<div style="width: 756px;">
   <form name="project_form" action="/_engineering/project" method="post">
      <input type="hidden" name="project_id" value="<?=$project->id?>" />
      <h2>Engineering Project</h2>
      <?php	if ($page->errorCount()) { ?>
      	<div class="form_error"><?=$page->errorString()?></div>
      <?php	}
         if ($page->success) { ?>
      	<div class="form_success"><?=$page->success?> [<a href="/_engineering/projects">Finished</a>] | [<a href="/_engineering/project">Create Another</a>] </div>
      <?php	} ?>
      <!--	START First Table -->
      <div class="tableBody min-tablet marginTop_20">
         <div class="tableRowHeader">
            <div class="tableCell" style="width: 25%;">Code</div>
            <div class="tableCell" style="width: 30%;">Title</div>
            <div class="tableCell" style="width: 25%;">Manager</div>
            <div class="tableCell" style="width: 20%;">Status</div>
         </div>
         <div class="tableRow">
            <div class="tableCell">
               <input type="text" name="code" class="value input" value="<?=$form['code']?>" />
            </div>
            <div class="tableCell">
               <input type="text" name="title" class="value input" style="width: 240px" value="<?=$form['title']?>" />
            </div>
            <div class="tableCell">
               <select name="manager_id" class="value input" style="width: 240px">
                  <option value="">Unassigned</option>
                  <?php	foreach ($managers as $manager) { ?>
                  <option value="<?=$manager->id?>"<? if ($manager->id == $project->manager->id) print " selected"; ?>><?=$manager->code?></option>
                  <?php	} ?>
               </select>
            </div>
            <div class="tableCell" style="min-width: 100px;">
               <select name="status" class="value input wide_100per">
                  <option value="new"<? if ($form['status'] == "NEW") print " selected"; ?>>New</option>
                  <option value="open"<? if ($form['status'] == "OPEN") print " selected"; ?>>Open</option>
                  <option value="hold"<? if ($form['status'] == "HOLD") print " selected"; ?>>Hold</option>
                  <option value="cancelled"<? if ($form['status'] == "CANCELLED") print " selected"; ?>>Cancelled</option>
                  <option value="complete"<? if ($form['status'] == "COMPLETE") print " selected"; ?>>Complete</option>
               </select>
            </div>	  
         </div>
      </div>
      <!--	END First Table -->
      <!--	START First Table -->
      <div class="tableBody half clean min-tablet marginTop_20">
         <div class="tableRowHeader">
            <div class="tableCell" style="width: 25%;">Description</div>
         </div>
         <div class="tableRow">
            <div class="tableCell">
               <textarea name="description"><?=$form['description']?></textarea>
            </div>
         </div>
      </div>
      <!--	END First Table -->
	<div class="button-bar">
		<input type="submit" name="btn_submit" class="button" value="Submit"/>
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
        <form name="repoUpload" action="/_engineering/project/<?=$form['code']?>" method="post" enctype="multipart/form-data">
        <div class="container">
	        <span class="label">Upload File</span>
            <input type="hidden" name="repository_name" value="<?=$repository?>" />
	        <input type="hidden" name="type" value="engineering project" />
	        <input type="file" name="uploadFile" />
	        <input type="submit" name="btn_submit" class="button" value="Upload" />
        </div>
        </form>
        <br/><br/>
    </div>

   <!--	START First Table -->
   <?php	if ($project->id) { ?>
   <h3>Tasks</h3>
   <div class="tableBody min-tablet marginTop_20">
      <div class="tableRowHeader">
         <div class="tableCell" style="width: 25%;">Title</div>
         <div class="tableCell" style="width: 25%;">Added</div>
         <div class="tableCell" style="width: 25%;">Tech</div>
         <div class="tableCell" style="width: 25%;">Status</div>
      </div>
      <?php	foreach ($tasks as $task) {
         $worker = $task->assignedTo(); ?>
      <div class="tableRow">
         <div class="tableCell">
            <a href="/_engineering/task/<?=$task->code?>"><?=$task->title?></a>
         </div>
         <div class="tableCell">
            <?=$task->date_added?>
         </div>
         <div class="tableCell">
            <?=$worker->login?>
         </div>
         <div class="tableCell">
            <?=$task->status?>
         </div>
      </div>
      <?php	} ?>
   </div>
   <br/><br/>
   <a href="/_engineering/task?project_id=<?=$project->id?>" class="button">Add Task</a>
   <?php	} ?>
   <!--	END First Table -->	
</div>
