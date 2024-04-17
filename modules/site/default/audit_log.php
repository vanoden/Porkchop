<!-- Page Header -->
<?=$page->showAdminPageInfo()?>
<!-- End Page Header -->

<form action="/_site/audit_log" method="post">
  <div id="search_container">
    <div><input type="checkbox" name="add" value="1"<?php if (isset($_REQUEST['add']) && !empty($_REQUEST['add'])) print " checked"; ?> /><label>Add</label></div>
    <div><input type="checkbox" name="update" value="1"<?php if (isset($_REQUEST['update']) && !empty($_REQUEST['update'])) print " checked"; ?> /><label>Update</label></div>
    <div><input type="checkbox" name="delete" value="1"<?php if (isset($_REQUEST['delete']) && !empty($_REQUEST['delete'])) print " checked"; ?> /><label>Delete</label></div>
    <input type="submit" name="btn_submit" class="button" value="Apply Filter" />
  </div>

  <div class="tableBody min-tablet">
    <div class="tableRowHeader">
        <div class="tableCell" style="width: 15%;">
            <a href="/_site/audit_log?pageNumber=<?php echo $pageNumber; ?>&btn_submit=1<?php if (isset($_REQUEST['add'])) echo '&add=' . $_REQUEST['add']; if (isset($_REQUEST['update'])) echo '&update=' . $_REQUEST['update']; if (isset($_REQUEST['delete'])) echo '&delete=' . $_REQUEST['delete']; ?>&sort_by=event_date&order_by=<?php echo $sort_direction === 'event_date' && $order_by === 'asc' ? 'desc' : 'asc'; ?>">Event Date</a>
        </div>
        <div class="tableCell" style="width: 15%;">
            <a href="/_site/audit_log?pageNumber=<?php echo $pageNumber; ?>&btn_submit=1<?php if (isset($_REQUEST['add'])) echo '&add=' . $_REQUEST['add']; if (isset($_REQUEST['update'])) echo '&update=' . $_REQUEST['update']; if (isset($_REQUEST['delete'])) echo '&delete=' . $_REQUEST['delete']; ?>&sort_by=user_id&order_by=<?php echo $sort_direction === 'user_id' && $order_by === 'asc' ? 'desc' : 'asc'; ?>">User</a>
        </div>
        <div class="tableCell" style="width: 15%;">
            <a href="/_site/audit_log?pageNumber=<?php echo $pageNumber; ?>&btn_submit=1<?php if (isset($_REQUEST['add'])) echo '&add=' . $_REQUEST['add']; if (isset($_REQUEST['update'])) echo '&update=' . $_REQUEST['update']; if (isset($_REQUEST['delete'])) echo '&delete=' . $_REQUEST['delete']; ?>&sort_by=class_method&order_by=<?php echo $sort_direction === 'class_method' && $order_by === 'asc' ? 'desc' : 'asc'; ?>">Class Method</a>
        </div>
        <div class="tableCell" style="width: 15%;">
            <a href="/_site/audit_log?pageNumber=<?php echo $pageNumber; ?>&btn_submit=1<?php if (isset($_REQUEST['add'])) echo '&add=' . $_REQUEST['add']; if (isset($_REQUEST['update'])) echo '&update=' . $_REQUEST['update']; if (isset($_REQUEST['delete'])) echo '&delete=' . $_REQUEST['delete']; ?>&sort_by=instance_id&order_by=<?php echo $sort_direction === 'instance_id' && $order_by === 'asc' ? 'desc' : 'asc'; ?>">Instance ID</a>
        </div>
        <div class="tableCell" style="width: 15%;">
            <a href="/_site/audit_log?pageNumber=<?php echo $pageNumber; ?>&btn_submit=1<?php if (isset($_REQUEST['add'])) echo '&add=' . $_REQUEST['add']; if (isset($_REQUEST['update'])) echo '&update=' . $_REQUEST['update']; if (isset($_REQUEST['delete'])) echo '&delete=' . $_REQUEST['delete']; ?>&sort_by=class_name&order_by=<?php echo $sort_direction === 'class_name' && $order_by === 'asc' ? 'desc' : 'asc'; ?>">Class Name</a>
        </div>
        <div class="tableCell" style="width: 15%;">Description</div>
    </div>

    <?php foreach ($auditsCurrentPage as $audit) { ?>
    <div class="tableRow">
      <div class="tableCell"><?php echo date('F j, Y, g:i a', strtotime($audit->event_date)); ?></div>
      <div class="tableCell">
        <?php
        $registerCustomer = new \Register\Customer($audit->user_id);
        $registerOrganization = new \Register\Organization($registerCustomer->organization_id);
        ?>        
        <strong><?=$registerOrganization->name?> </strong><br/>&nbsp;&nbsp;&nbsp;&nbsp;<?=$registerCustomer->first_name?> <?=$registerCustomer->last_name?>
      </div>
        <div class="tableCell"><?=$audit->class_method?></div>
        <div class="tableCell"><?=$audit->instance_id?></div>
        <div class="tableCell"><?=$audit->class_name?></div>
        <div class="tableCell"><?=$audit->description?></div>
    </div>
    <?php } ?>
  </div>
  <!--    Standard Page Navigation Bar -->
  <div class="pagination" id="pagination">
    <?=$pagination->renderPages(); ?>
  </div>
</form>