<div style="width: 756px;">
   <form name="project_form" action="/_engineering/project" method="post">
      <input type="hidden" name="project_id" value="<?=$project->id?>" />
      <div class="breadcrumbs">
         <a href="/_engineering/home">Engineering</a>
         <a href="/_engineering/projects">Projects</a> > Project Details
      </div>
      <?php include(MODULES.'/engineering/partials/search_bar.php'); ?>
      <h2>Engineering Project</h2>
      <?	if ($page->error) { ?>
      	<div class="form_error"><?=$page->error?></div>
      <?	}
         if ($page->success) { ?>
      	<div class="form_success"><?=$page->success?> [<a href="/_engineering/projects">Finished</a>] | [<a href="/_engineering/project">Create Another</a>] </div>
      <?	} ?>
      <!--	START First Table -->
      <div class="tableBody min-tablet marginTop_20">
         <div class="tableRowHeader">
            <div class="tableCell" style="width: 25%;">Code</div>
            <div class="tableCell" style="width: 30%;">Title</div>
            <div class="tableCell" style="width: 25%;">Manager</div>
            <div class="tableCell" style="width: 20%;"></div>
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
                  <?	foreach ($managers as $manager) { ?>
                  <option value="<?=$manager->id?>"<? if ($manager->id == $project->manager->id) print " selected"; ?>><?=$manager->code?></option>
                  <?	} ?>
               </select>
            </div>
            <div class="tableCell">
               <!--			empty cell -->
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
	  <?php
		if (!$page->success) {
		?>
			<div class="button-bar">
				<input type="submit" name="btn_submit" class="button" value="Submit">
			</div>
		<?php
		}
		?>
   </form>
   <!--	START First Table -->
   <?	if ($project->id) { ?>
   <h3>Tasks</h3>
   <div class="tableBody min-tablet marginTop_20">
      <div class="tableRowHeader">
         <div class="tableCell" style="width: 25%;">Title</div>
         <div class="tableCell" style="width: 25%;">Added</div>
         <div class="tableCell" style="width: 25%;">Tech</div>
         <div class="tableCell" style="width: 25%;">Status</div>
      </div>
      <?	foreach ($tasks as $task) {
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
      <?	} ?>
   </div>
   <?	} ?>
   <!--	END First Table -->	
</div>
