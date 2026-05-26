<!-- Page Header -->
<?=$page->showAdminPageInfo()?>
<!-- End Page Header -->

<form action="/_site/audit_log" method="post">
  <div id="search_container">
	<div><select name="class_name">
		<option value="">Select a class</option>
		<?php foreach ($classList as $class) { ?>
			<option value="<?=$class?>"<?php if (isset($parameters["class_name"]) && $parameters["class_name"] == $class) print " selected";?>><?=$class?></option>
		<?php } ?>
	</select></div>
	<div><input type="text" name="code" value="<?php if (isset($parameters['code'])) print $parameters['code']; ?>" placeholder="Instance Code" /></div>
    <input type="submit" name="btn_submit" class="button" value="Apply Filter" />
  </div>

  <?php if ($display_results) { ?>
  <div class="tableBody min-tablet">
    <div class="tableRowHeader">
        <div class="tableCell tableCell-width-15">
            <a href="/_site/audit_log?class_name=<?php echo urlencode($parameters['class_name'] ?? ''); ?>&code=<?php echo urlencode($parameters['code'] ?? ''); ?>&pageNumber=<?php echo $pageNumber; ?>&btn_submit=1<?php if (isset($_REQUEST['add'])) echo '&add=' . $_REQUEST['add']; if (isset($_REQUEST['update'])) echo '&update=' . $_REQUEST['update']; if (isset($_REQUEST['delete'])) echo '&delete=' . $_REQUEST['delete']; ?>&sort_by=event_date&order_by=<?php echo $sort_direction === 'event_date' && $order_by === 'asc' ? 'desc' : 'asc'; ?>">Event Date</a>
        </div>
        <div class="tableCell tableCell-width-15">
            <a href="/_site/audit_log?class_name=<?php echo urlencode($parameters['class_name'] ?? ''); ?>&code=<?php echo urlencode($parameters['code'] ?? ''); ?>&pageNumber=<?php echo $pageNumber; ?>&btn_submit=1<?php if (isset($_REQUEST['add'])) echo '&add=' . $_REQUEST['add']; if (isset($_REQUEST['update'])) echo '&update=' . $_REQUEST['update']; if (isset($_REQUEST['delete'])) echo '&delete=' . $_REQUEST['delete']; ?>&sort_by=user_id&order_by=<?php echo $sort_direction === 'user_id' && $order_by === 'asc' ? 'desc' : 'asc'; ?>">User</a>
        </div>
        <div class="tableCell tableCell-width-15">
            <a href="/_site/audit_log?class_name=<?php echo urlencode($parameters['class_name'] ?? ''); ?>&code=<?php echo urlencode($parameters['code'] ?? ''); ?>&pageNumber=<?php echo $pageNumber; ?>&btn_submit=1<?php if (isset($_REQUEST['add'])) echo '&add=' . $_REQUEST['add']; if (isset($_REQUEST['update'])) echo '&update=' . $_REQUEST['update']; if (isset($_REQUEST['delete'])) echo '&delete=' . $_REQUEST['delete']; ?>&sort_by=class_method&order_by=<?php echo $sort_direction === 'class_method' && $order_by === 'asc' ? 'desc' : 'asc'; ?>">Class Method</a>
        </div>
        <div class="tableCell tableCell-width-15">
            <a href="/_site/audit_log?class_name=<?php echo urlencode($parameters['class_name'] ?? ''); ?>&code=<?php echo urlencode($parameters['code'] ?? ''); ?>&pageNumber=<?php echo $pageNumber; ?>&btn_submit=1<?php if (isset($_REQUEST['add'])) echo '&add=' . $_REQUEST['add']; if (isset($_REQUEST['update'])) echo '&update=' . $_REQUEST['update']; if (isset($_REQUEST['delete'])) echo '&delete=' . $_REQUEST['delete']; ?>&sort_by=instance_id&order_by=<?php echo $sort_direction === 'instance_id' && $order_by === 'asc' ? 'desc' : 'asc'; ?>">Instance ID</a>
        </div>
        <div class="tableCell tableCell-width-15">
            <a href="/_site/audit_log?class_name=<?php echo urlencode($parameters['class_name'] ?? ''); ?>&code=<?php echo urlencode($parameters['code'] ?? ''); ?>&pageNumber=<?php echo $pageNumber; ?>&btn_submit=1<?php if (isset($_REQUEST['add'])) echo '&add=' . $_REQUEST['add']; if (isset($_REQUEST['update'])) echo '&update=' . $_REQUEST['update']; if (isset($_REQUEST['delete'])) echo '&delete=' . $_REQUEST['delete']; ?>&sort_by=class_name&order_by=<?php echo $sort_direction === 'class_name' && $order_by === 'asc' ? 'desc' : 'asc'; ?>">Class Name</a>
        </div>
        <div class="tableCell tableCell-width-15">Description</div>
    </div>

    <?php
    $customerCache = [];
    $organizationCache = [];
    foreach ($auditsCurrentPage as $audit) {
    ?>
    <div class="tableRow">
      <div class="tableCell"><?php echo date('F j, Y, g:i a', strtotime($audit->event_date)); ?></div>
      <div class="tableCell">
        <?php
        if (!isset($customerCache[$audit->user_id])) {
            $customerCache[$audit->user_id] = new \Register\Customer($audit->user_id);
        }
        $registerCustomer = $customerCache[$audit->user_id];

        $organizationId = (int)($registerCustomer->organization_id ?? 0);
        if ($organizationId > 0 && !isset($organizationCache[$organizationId])) {
            $organizationCache[$organizationId] = new \Register\Organization($organizationId);
        }
        $registerOrganization = $organizationId > 0 ? $organizationCache[$organizationId] : null;
        ?>        
        <strong><?=htmlspecialchars($registerOrganization->name ?? 'Unknown Organization')?> </strong><br/>&nbsp;&nbsp;&nbsp;&nbsp;<?=htmlspecialchars(trim(($registerCustomer->first_name ?? '') . ' ' . ($registerCustomer->last_name ?? '')))?>
      </div>
        <div class="tableCell"><?=$audit->class_method?></div>
        <div class="tableCell"><?=$audit->instance_id?></div>
        <div class="tableCell"><?=$audit->class_name?></div>
        <div class="tableCell"><?=strip_tags($audit->description)?></div>
    </div>
    <?php } ?>
  </div>
  <!--    Standard Page Navigation Bar -->
  <div class="pagination" id="pagination">
    <?=$pagination->renderPages(); ?>
	</div>
  <?php	} ?>
</form>
