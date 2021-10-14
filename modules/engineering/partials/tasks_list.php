<form id="tasksListForm">
    <h2 style="display: inline-block;">Engineering Tasks [
        <?=($page->isSearchResults)? "Matched Tasks: " : "";?>
        <?=isset($tasks) ? count($tasks) : "0"?>
    ]</h2>
    <?php
     if (!isset($page->isSearchResults)) {
    ?>
        <input type="button" name="btn_new_task" value="Add Task" class="button more" onclick="newTask();"/>
    <?php
    }
        // if we're not doing a task search, show the filter bar
        if (!isset($page->isSearchResults)) {
    ?>
        <!--	START First Table -->
	    <div class="tableBody min-tablet">
	    <div class="tableRowHeader">
		    <div class="tableCell" style="width: 14%;">Assigned To</div>
		    <div class="tableCell" style="width: 18%;">Product</div>
		    <div class="tableCell" style="width: 16%;">Project</div>
		    <div class="tableCell" style="width: 52%;">Status</div>
	    </div>
	    <div class="tableRow">
		    <div class="tableCell">
			    <select name="assigned_id" class="value input">
				    <option value="">Any</option>
				    <?php	foreach ($assigners as $assigner) { ?>
				    <option value="<?=$assigner->id?>"<?php if ($assigner->id == $_REQUEST['assigned_id']) print " selected"; ?>><?=$assigner->login?></option>
				    <?php	} ?>
			    </select>
		    </div>
		    <div class="tableCell">
			    <select name="product_id" class="value input">
				    <option value="">Any</option>
				    <?php	foreach ($products as $product) { ?>
				    <option value="<?=$product->id?>"<?php if ($product->id == $_REQUEST['product_id']) print " selected"; ?>><?=$product->title?></option>
				    <?php	} ?>
			    </select>
		    </div>
		    <div class="tableCell">
			    <select name="project_id" class="value input">
				    <option value="">Any</option>
				    <?php	foreach ($projects as $project) { ?>
				    <option value="<?=$project->id?>"<?php if ($project->id == $_REQUEST['project_id']) print " selected"; ?>><?=$project->title?></option>
				    <?php	} ?>
			    </select>
		    </div>
		    <div class="tableCell">
			    <input type="checkbox" name="new" value="1"<?php if ($_REQUEST['new']) print " checked"; ?> />New
			    <input type="checkbox" name="active" value="1"<?php if ($_REQUEST['active']) print " checked"; ?> />Active
			    <input type="checkbox" name="broken" value="1"<?php if ($_REQUEST['broken']) print " checked"; ?> />Broken
			    <input type="checkbox" name="testing" value="1"<?php if ($_REQUEST['testing']) print " checked"; ?> />Testing
			    <input type="checkbox" name="complete" value="1"<?php if ($_REQUEST['complete']) print " checked"; ?>/>Completed
			    <input type="checkbox" name="cancelled" value="1"<?php if ($_REQUEST['cancelled']) print " checked"; ?> />Cancelled
			    <input type="checkbox" name="hold" value="1"<?php if ($_REQUEST['hold']) print " checked"; ?> />Hold
		    </div>
	    </div>
	    <div class="form_footer" style="text-align: center; width: 100%">
		    <input type="submit" name="btn_submit" class="button" value="Apply Filter" />
	    </div>
    </div>
    <!--	END First Table -->	
    <?php  
    }
    ?>
    <!--	START First Table -->
	    <div class="tableBody min-tablet">
	    <div class="tableRowHeader">
		    <div class="tableCell" style="width: 23%;">Title</div>
		    <div class="tableCell" style="width: 10%;">Added</div>
		    <div class="tableCell" style="width: 15%;">Assigned To</div>
		    <div class="tableCell" style="width: 7%;">Status</div>
		    <div class="tableCell" style="width: 15%;">Product</div>
		    <div class="tableCell" style="width: 20%;">Project</div>
		    <div class="tableCell" style="width: 10%;">Priority</div>
		    <div class="tableCell" style="width: 10%;">PreRequisite</div>
	    </div>
    <?php
        if (!isset($tasks)) $tasks = array();
	    foreach ($tasks as $task) {
		    $product = $task->product();
		    $project = $task->project();
		    $worker = $task->assignedTo();
			$prerequisiteTask = $task->prerequisite();
    ?>
	    <div class="tableRow">
		    <div class="tableCell">
			    <a href="/_engineering/task/<?=$task->code?>"><?=$task->title?></a>
		    </div>
		    <div class="tableCell">
			    <?=date('m/d/Y',$task->timestamp_added)?>
		    </div>
		    <div class="tableCell">
			    <?=$worker->full_name()?>
		    </div>
		    <div class="tableCell">
			    <?=$task->status?>
		    </div>
		    <div class="tableCell">
			    <?=$product->title?>
		    </div>
		    <div class="tableCell">
			    <?=$project->title?>
		    </div>
		    <div class="tableCell">
			    <?=$task->priority?>
		    </div>
		    <div class="tableCell">
	           <?php
	           if (isset($prerequisiteTask->title)) {
               ?>
                    <a href="/_engineering/task/<?=$prerequisiteTask->code?>"><?=$prerequisiteTask->title?></a>
               <?php
               }
               ?>
		    </div>
	    </div>
    <?php	} ?>
    </div>
    <!--	END First Table -->			
</form>
