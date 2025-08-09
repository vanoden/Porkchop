<!-- Page Header -->
<?=$page->showAdminPageInfo()?>
<!-- End Page Header -->

<style>
  .tabs { display:flex; gap:6px; margin-bottom:20px; border-bottom:1px solid #ddd; }
  .tabs .tab { color:#555; background:#f4f4f4; border:1px solid #ddd; border-bottom:none; padding:8px 12px; border-top-left-radius:6px; border-top-right-radius:6px; text-decoration:none; }
  .tabs .tab:hover { background:#eee; }
  .tabs .tab.active { background:#fff; color:#222; font-weight:600; }
</style>
<?php $activeTab = 'audit'; ?>
<?php
    // Show small default product image and title if available
    $__defImg = $item->getDefaultStorageImage();
    if ($__defImg && $__defImg->id) {
        $__thumb = "/api/media/downloadMediaImage?height=50&width=50&code=".$__defImg->code;
        $__title = htmlspecialchars($item->getMetadata('name') ?: $item->name ?: $item->code);
        echo '<div style="margin:6px 0 8px 0; display:flex; align-items:center; gap:8px;">'
            . '<img src="'. $__thumb .'" alt="Default" style="width:50px;height:50px;border:1px solid #ddd;border-radius:3px;object-fit:cover;" />'
            . '<div style="font-weight:600;">'. $__title .'</div>'
            . '</div>';
    }
?>
<div class="tabs">
    <a href="/_spectros/admin_product/<?= $item->code ?>" class="tab <?= $activeTab==='details'?'active':'' ?>">Details</a>
    <a href="/_product/admin_product_prices/<?= $item->code ?>" class="tab <?= $activeTab==='prices'?'active':'' ?>">Prices</a>
    <a href="/_product/admin_product_vendors/<?= $item->code ?>" class="tab <?= $activeTab==='vendors'?'active':'' ?>">Vendors</a>
    <a href="/_product/admin_images/<?= $item->code ?>" class="tab <?= $activeTab==='images'?'active':'' ?>">Images</a>
    <a href="/_product/admin_product_tags/<?= $item->code ?>" class="tab <?= $activeTab==='tags'?'active':'' ?>">Tags</a>
    <a href="/_spectros/admin_asset_sensors/<?= $item->code ?>" class="tab <?= $activeTab==='sensors'?'active':'' ?>">Sensors</a>
    <a href="/_product/audit_log/<?= $item->code ?>" class="tab <?= $activeTab==='audit'?'active':'' ?>">Audit Log</a>
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
