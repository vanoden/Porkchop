<style>
    .sortableHeader{
        white-space: nowrap;
    }
</style>
<script src="/js/sort.js"></script>
<script>
    // document loaded - start table sort
    window.addEventListener('DOMContentLoaded', (event) => {     
        <?php
        $sortDirection = 'desc';
        if ($_REQUEST['sort_direction'] == 'desc') $sortDirection = 'asc';
        
		switch ($parameters['sort_by']) { 
            case 'title':
                ?>
                SortableTable.sortColumn('title-sortable-column', '<?=($_REQUEST['sort_direction'] == 'desc') ? 'up': 'down';?>');
                <?php
            break;
            case 'added':
                ?>
                SortableTable.sortColumn('added-sortable-column', '<?=($_REQUEST['sort_direction'] == 'desc') ? 'up': 'down';?>');
                <?php
            break;
            case 'assigned':
                ?>
                SortableTable.sortColumn('assigned-sortable-column', '<?=($_REQUEST['sort_direction'] == 'desc') ? 'up': 'down';?>');
                <?php
            break;
            case 'status':
                ?>
                SortableTable.sortColumn('status-sortable-column', '<?=($_REQUEST['sort_direction'] == 'desc') ? 'up': 'down';?>');
                <?php
            break;
            case 'product':
                ?>
                SortableTable.sortColumn('product-sortable-column', '<?=($_REQUEST['sort_direction'] == 'desc') ? 'up': 'down';?>');
                <?php
            break;
            case 'project':
                ?>
                SortableTable.sortColumn('project-sortable-column', '<?=($_REQUEST['sort_direction'] == 'desc') ? 'up': 'down';?>');
                <?php
            break;
            case 'priority':
                ?>
                SortableTable.sortColumn('priority-sortable-column', '<?=($_REQUEST['sort_direction'] == 'desc') ? 'up': 'down';?>');
                <?php
            break;
            case 'prerequisite':
                ?>
                SortableTable.sortColumn('prerequisite-sortable-column', '<?=($_REQUEST['sort_direction'] == 'desc') ? 'up': 'down';?>');
                <?php
            break;
            case 'role':
                ?>
                SortableTable.sortColumn('role-sortable-column', '<?=($_REQUEST['sort_direction'] == 'desc') ? 'up': 'down';?>');
                <?php
            break;
            default:
                ?>
                SortableTable.sortColumn('ticket-sortable-column', '<?=($_REQUEST['sort_direction'] == 'desc') ? 'up': 'down';?>');
                <?php
            break;
		}
        ?>
    });

    // update report from UI change
	function updateReport() {
		var pageForm = document.getElementById('tasksListForm');	
		pageForm.filtered.value = 1;
		pageForm.submit();
		return true;
	}
</script>
<form id="tasksListForm">
    <input id="sort_by" type="hidden" name="sort_by" value="" />
	<input type="hidden" name="filtered" value="<?=$_REQUEST['filtered']?>" />	      
	<input id="sort_direction" type="hidden" name="sort_direction" value="<?=($_REQUEST['sort_direction'] == 'desc') ? 'asc': 'desc';?>" />  
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
		    <div class="tableCell" style="width: 16%;">Role</div>
	    </div>
	    <div class="tableRow">
		    <div class="tableCell">
			    <select name="assigned_id" class="value input">
				    <option value="">Any</option>
				    <?php	foreach ($assigners as $assigner) { ?>
				    <option value="<?=$assigner->id?>"<?php if ($assigner->id == $_REQUEST['assigned_id']) print " selected"; ?>><?=$assigner->login?></option>
				    <?php	} ?>
				    <option value="Unassigned" <?php if ($_REQUEST['assigned_id'] == "Unassigned") print " selected"; ?>>Unassigned</option>
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
               <select name="role_id" class="value input" style="max-width: 250px;">
                  <option value="">None</option>
                  <?php	foreach($engineeringRoles as $engineeringRole) { ?>
                    <option value="<?=$engineeringRole->id?>"<?php if ($engineeringRole->id == $_REQUEST['role_id']) print " selected"; ?>><?=$engineeringRole->name?></option>
                  <?php	} ?>
               </select>
		    </div>
	    </div>
    </div>
    <div style="padding-top:10px;">
        <input type="checkbox" name="new" value="1"<?php if ($_REQUEST['new']) print " checked"; ?> />New
        <input type="checkbox" name="active" value="1"<?php if ($_REQUEST['active']) print " checked"; ?> />Active
        <input type="checkbox" name="broken" value="1"<?php if ($_REQUEST['broken']) print " checked"; ?> />Broken
        <input type="checkbox" name="testing" value="1"<?php if ($_REQUEST['testing']) print " checked"; ?> />Testing
        <input type="checkbox" name="complete" value="1"<?php if ($_REQUEST['complete']) print " checked"; ?>/>Completed
        <input type="checkbox" name="cancelled" value="1"<?php if ($_REQUEST['cancelled']) print " checked"; ?> />Cancelled
        <input type="checkbox" name="hold" value="1"<?php if ($_REQUEST['hold']) print " checked"; ?> />Hold
        <input type="checkbox" name="duplicate" value="1"<?php if ($_REQUEST['duplicate']) print " checked"; ?> /><i>Include Duplicates</i>
	</div>
    <div class="form_footer" style="text-align: left; width: 100%">
	    <input type="submit" name="btn_submit" class="button" value="Apply Filter" />
    </div>
    <br/>
    <!--	END First Table -->	
    <?php  
    }
    ?>
    <!--	START First Table -->
	    <div class="tableBody min-tablet">
	    <div class="tableRowHeader">
		    <div id="title-sortable-column" class="tableCell sortableHeader" style="width: 25%;" onclick="document.getElementById('sort_by').value = 'title'; updateReport()">Title</div>
		    <div id="added-sortable-column" class="tableCell sortableHeader" style="width: 10%;" onclick="document.getElementById('sort_by').value = 'added'; updateReport()">Added</div>
		    <div id="assigned-sortable-column" class="tableCell sortableHeader" style="width: 15%;" onclick="document.getElementById('sort_by').value = 'assigned'; updateReport()">Assigned To</div>
		    <div id="status-sortable-column" class="tableCell sortableHeader" style="width: 7%;" onclick="document.getElementById('sort_by').value = 'status'; updateReport()">Status</div>
		    <div id="product-sortable-column" class="tableCell sortableHeader" style="width: 15%;" onclick="document.getElementById('sort_by').value = 'product'; updateReport()">Product</div>
		    <div id="project-sortable-column" class="tableCell sortableHeader" style="width: 10%;" onclick="document.getElementById('sort_by').value = 'project'; updateReport()">Project</div>
		    <div id="priority-sortable-column" class="tableCell sortableHeader" style="width: 8%;" onclick="document.getElementById('sort_by').value = 'priority'; updateReport()">Priority</div>
		    <div id="prerequisite-sortable-column" class="tableCell sortableHeader" style="width: 20%;" onclick="document.getElementById('sort_by').value = 'prerequisite'; updateReport()">PreRequisite</div>
		    <div id="role-sortable-column" class="tableCell sortableHeader" style="width: 20%;" onclick="document.getElementById('sort_by').value = 'role'; updateReport()">Role</div>
	    </div>
    <?php
        if (!isset($tasks)) $tasks = array();
	    foreach ($tasks as $taskItem) {
		    $product = $taskItem->product();
		    $project = $taskItem->project();
		    $worker = $taskItem->assignedTo();
			$prerequisiteTask = $taskItem->prerequisite();
			$roleRequired = $taskItem->roleRequired();
    ?>
	    <div class="tableRow">
		    <div class="tableCell">
			    <a href="/_engineering/task/<?=$taskItem->code?>"><?=$taskItem->title?></a>
                <?php
                    if (!empty($taskItem->duplicate_task_id)) {
                    $taskDuplicated = new Engineering\Task($taskItem->duplicate_task_id);
                ?>
                    [duplicate of <a href="/_engineering/task/<?=$taskDuplicated->code?>"><?=$taskDuplicated->title?></a>]
            	<?php
	                }
	            ?>
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
		    <div class="tableCell">
			    <?=$roleRequired->name?>
		    </div>
		    
	    </div>
    <?php	} ?>
    </div>
    <!--	END First Table -->			
</form>
