<style>
    input[type="button"].duplicate_button {
        background: #EFEFEF;
        color:black;
    }
</style>
<script>
    function setTaskDuplicate(taskId, taskName) {
        duplicate_task_id.value = taskId;
        duplicate_task_name.value = taskName;
        document.getElementById("overlay").style.display = "none";
        document.getElementById("popup_duplicate").style.display = "none";
    }
</script>
<form id="tasksListForm">
    <h2 style="display: inline-block;">Engineering Tasks [
        <?=($page->isSearchResults)? "Matched Tasks: " : "";?>
        <?=isset($duplicateTasks) ? count($duplicateTasks) : "0"?>
    ]</h2>
    <?php
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
			    <select name="duplicate_assigned_id" class="value input">
				    <option value="">Any</option>
				    <?php	foreach ($assigners as $assigner) { ?>
				    <option value="<?=$assigner->id?>"<?php if ($assigner->id == $_REQUEST['duplicate_assigned_id']) print " selected"; ?>><?=$assigner->login?></option>
				    <?php	} ?>
				    <option value="Unassigned" <?php if ($_REQUEST['duplicate_assigned_id'] == "Unassigned") print " selected"; ?>>Unassigned</option>
			    </select>
		    </div>
		    <div class="tableCell">
			    <select name="duplicate_product_id" class="value input">
				    <option value="">Any</option>
				    <?php	foreach ($products as $product) { ?>
				    <option value="<?=$product->id?>"<?php if ($product->id == $_REQUEST['duplicate_product_id']) print " selected"; ?>><?=$product->title?></option>
				    <?php	} ?>
			    </select>
		    </div>
		    <div class="tableCell">
			    <select name="duplicate_project_id" class="value input">
				    <option value="">Any</option>
				    <?php	foreach ($projects as $project) { ?>
				    <option value="<?=$project->id?>"<?php if ($project->id == $_REQUEST['duplicate_project_id']) print " selected"; ?>><?=$project->title?></option>
				    <?php	} ?>
			    </select>
		    </div>
		    <div class="tableCell">
			    <input type="checkbox" name="duplicate_new" value="1"<?php if ($_REQUEST['duplicate_new']) print " checked"; ?> />New
			    <input type="checkbox" name="duplicate_active" value="1"<?php if ($_REQUEST['duplicate_active']) print " checked"; ?> />Active
			    <input type="checkbox" name="duplicate_broken" value="1"<?php if ($_REQUEST['duplicate_broken']) print " checked"; ?> />Broken
			    <input type="checkbox" name="duplicate_testing" value="1"<?php if ($_REQUEST['duplicate_testing']) print " checked"; ?> />Testing
			    <input type="checkbox" name="duplicate_complete" value="1"<?php if ($_REQUEST['duplicate_complete']) print " checked"; ?>/>Completed
			    <input type="checkbox" name="duplicate_cancelled" value="1"<?php if ($_REQUEST['duplicate_cancelled']) print " checked"; ?> />Cancelled
			    <input type="checkbox" name="duplicate_hold" value="1"<?php if ($_REQUEST['duplicate_hold']) print " checked"; ?> />Hold
		    </div>
	    </div>
	    <div class="form_footer" style="text-align: center; width: 100%">
		    <input type="submit" name="duplicate_btn_submit" class="button" value="Search Tasks" /><br/>
            <span style="font-size: 10px;">&gt; 1 filter required above</span>
	    </div>
    </div>
    <!--	END First Table -->	
    <?php  
    }
    ?>
    <!--	START First Table -->
	    <div class="tableBody min-tablet">
	    <div class="tableRowHeader">
		    <div class="tableCell" style="width: 23%;">Assign</div>
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
        if (!isset($duplicateTasks)) $duplicateTasks = array();
	    foreach ($duplicateTasks as $taskItem) {
		    $product = $taskItem->product();
		    $project = $taskItem->project();
		    $worker = $taskItem->assignedTo();
			$prerequisiteTask = $taskItem->prerequisite();
			if ($task->id !== $taskItem->id) {
    ?>
	    <div class="tableRow">
		    <div class="tableCell">
   			    <input type="button" name="duplicate_btn_assign" class="duplicate_button" onclick="setTaskDuplicate(<?=$taskItem->id?>, '<?=str_replace("'","", $taskItem->title)?>')" value="Set Duplicate of" />		        
		    </div>
		    <div class="tableCell">
			    <a href="/_engineering/task/<?=$taskItem->code?>"><?=$taskItem->title?></a>
		    </div>
		    <div class="tableCell">
			    <?=date('m/d/Y',$taskItem->timestamp_added)?>
		    </div>
		    <div class="tableCell">
			    <?=$worker->full_name()?>
		    </div>
		    <div class="tableCell">
			    <?=$taskItem->status?>
		    </div>
		    <div class="tableCell">
			    <?=$product->title?>
		    </div>
		    <div class="tableCell">
			    <?=$project->title?>
		    </div>
		    <div class="tableCell">
			    <?=$taskItem->priority?>
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
    <?php	}
        } 
        ?>
    </div>
</form>
