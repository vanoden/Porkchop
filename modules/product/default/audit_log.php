<!-- Page Header -->
<?=$page->showAdminPageInfo()?>
<!-- End Page Header -->

<div id="page_top_nav" style="margin-bottom: 20px;">
	<a href="/_spectros/admin_product/<?= $item->code ?>" class="button" disabled>Details</a>
	<a href="/_product/admin_product_prices/<?= $item->code ?>" class="button">Prices</a>
	<a href="/_product/admin_product_vendors/<?= $item->code ?>" class="button">Vendors</a>
	<a href="/_product/admin_images/<?= $item->code ?>" class="button">Images</a>
	<a href="/_product/admin_product_tags/<?= $item->code ?>" class="button">Tags</a>
	<a href="/_spectros/admin_asset_sensors/<?= $item->code ?>" class="button">Sensors</a>
	<a href="/_product/audit_log/<?= $item->code ?>" class="button">Audit Log</a>
</div>

<form action="/_product/audit_log/<?=$item->code?>" method="post">
  <?php if ($display_results) { ?>
  <div class="tableBody min-tablet">
    <div class="tableRowHeader">
        <div class="tableCell" style="width: 15%;">
            <a href="/_product/audit_log/<?=$item->code?>?pageNumber=<?php echo $pageNumber; ?>&btn_submit=1<?php if (isset($_REQUEST['add'])) echo '&add=' . $_REQUEST['add']; if (isset($_REQUEST['update'])) echo '&update=' . $_REQUEST['update']; if (isset($_REQUEST['delete'])) echo '&delete=' . $_REQUEST['delete']; ?>&sort_by=event_date&order_by=<?php echo $sort_direction === 'event_date' && $order_by === 'asc' ? 'desc' : 'asc'; ?>">Event Date</a>
        </div>
        <div class="tableCell" style="width: 15%;">
            <a href="/_product/audit_log/<?=$item->code?>?pageNumber=<?php echo $pageNumber; ?>&btn_submit=1<?php if (isset($_REQUEST['add'])) echo '&add=' . $_REQUEST['add']; if (isset($_REQUEST['update'])) echo '&update=' . $_REQUEST['update']; if (isset($_REQUEST['delete'])) echo '&delete=' . $_REQUEST['delete']; ?>&sort_by=user_id&order_by=<?php echo $sort_direction === 'user_id' && $order_by === 'asc' ? 'desc' : 'asc'; ?>">User</a>
        </div>
        <div class="tableCell" style="width: 10%;">
            <a href="/_product/audit_log/<?=$item->code?>?pageNumber=<?php echo $pageNumber; ?>&btn_submit=1<?php if (isset($_REQUEST['add'])) echo '&add=' . $_REQUEST['add']; if (isset($_REQUEST['update'])) echo '&update=' . $_REQUEST['update']; if (isset($_REQUEST['delete'])) echo '&delete=' . $_REQUEST['delete']; ?>&sort_by=class_method&order_by=<?php echo $sort_direction === 'class_method' && $order_by === 'asc' ? 'desc' : 'asc'; ?>">Class Method</a>
        </div>
        <div class="tableCell" style="width: 10%;">
            <a href="/_product/audit_log/<?=$item->code?>?pageNumber=<?php echo $pageNumber; ?>&btn_submit=1<?php if (isset($_REQUEST['add'])) echo '&add=' . $_REQUEST['add']; if (isset($_REQUEST['update'])) echo '&update=' . $_REQUEST['update']; if (isset($_REQUEST['delete'])) echo '&delete=' . $_REQUEST['delete']; ?>&sort_by=instance_id&order_by=<?php echo $sort_direction === 'instance_id' && $order_by === 'asc' ? 'desc' : 'asc'; ?>">Instance ID</a>
        </div>
        <div class="tableCell" style="width: 50%;">Description</div>
    </div>

    <?php foreach ($auditsCurrentPage as $audit) { ?>
    <div class="tableRow">
      <div class="tableCell"><?php echo date('M j, Y, g:i a', strtotime($audit->event_date)); ?></div>
      <div class="tableCell">
        <?php
        $registerCustomer = new \Register\Customer($audit->user_id);
        $registerOrganization = new \Register\Organization($registerCustomer->organization_id);
		$item = new \Product\Item($audit->instance_id);
        ?>        
        <strong><?=$registerOrganization->name?> </strong><br/>&nbsp;&nbsp;&nbsp;&nbsp;<?=$registerCustomer->first_name?> <?=$registerCustomer->last_name?>
      </div>
        <div class="tableCell"><?=$audit->class_method?></div>
        <div class="tableCell"><?=$item->code?> (<?=$audit->instance_id?>)</div>
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
