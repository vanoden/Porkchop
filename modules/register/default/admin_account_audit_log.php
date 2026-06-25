<!-- Page Header -->
<?= $page->showAdminPageInfo() ?>
<!-- End Page Header -->

<?php
$activeTab = 'audit';
require __DIR__ . '/admin_account_tabs.php';
?>

<div class="form_instruction">View audit log entries recorded for this account.</div>

<script>
  function changeAuditPageSize(size) {
    document.getElementById('page_size').value = size;
    document.getElementById('start').value = 0;
    document.getElementById('auditLogForm').submit();
  }
  function changeAuditClass(value) {
    document.getElementById('start').value = 0;
    document.getElementById('auditLogForm').submit();
  }
</script>

<form id="auditLogForm" method="get" action="<?= PATH ?>/_register/admin_account_audit_log">
  <input type="hidden" name="customer_id" value="<?= $customer_id ?>" />
  <input type="hidden" id="start" name="start" value="<?= $start_offset ?>" />
  <input type="hidden" id="page_size" name="page_size" value="<?= $page_size ?>" />

  <div class="audit-top-controls">
    <div class="audit-class-filter">
      <label>Class:
        <select name="class_name" onchange="changeAuditClass(this.value);">
          <option value="">All</option>
          <?php foreach ($classList as $class_name) { ?>
            <option value="<?= $class_name ?>" <?= isset($current_class) && $current_class === $class_name ? 'selected' : '' ?>><?= $class_name ?></option>
          <?php } ?>
        </select>
      </label>
    </div>
    <div class="audit-page-size">
      <label>Records per page:
        <select name="page_size_select" onchange="changeAuditPageSize(this.value);">
          <?php foreach ($page_size_options as $option) { ?>
            <option value="<?= $option ?>" <?= $page_size == $option ? 'selected' : '' ?>><?= $option ?></option>
          <?php } ?>
        </select>
      </label>
    </div>
  </div>

  <div class="tableBody">
    <div class="tableRowHeader">
        <div class="tableCell width-15per">Event Date</div>
        <div class="tableCell width-20per">Acted By</div>
        <div class="tableCell width-15per">Class</div>
        <div class="tableCell width-15per">Method</div>
        <div class="tableCell width-35per">Description</div>
    </div>
    <?php if (!empty($auditRecords)) {
        $actors = [];
        foreach ($auditRecords as $record) {
            $actor = null;
            if (!empty($record->user_id)) {
                if (!isset($actors[$record->user_id])) {
                    $actors[$record->user_id] = new \Register\Customer($record->user_id);
                }
                $actor = $actors[$record->user_id];
            }
    ?>
    <div class="tableRow">
        <div class="tableCell"><?= shortDate($record->event_date) ?></div>
        <div class="tableCell"><?= $actor ? $actor->code : 'System' ?></div>
        <div class="tableCell"><?= htmlspecialchars($record->class_name ?? '') ?></div>
        <div class="tableCell"><?= htmlspecialchars($record->class_method ?? '') ?></div>
        <div class="tableCell"><?= htmlspecialchars($record->description ?? '') ?></div>
    </div>
    <?php }
    } else { ?>
    <div class="tableRow">
        <div class="tableCell width-100per">No audit log records found for this account.</div>
    </div>
    <?php } ?>
  </div>

  <?=$pagination->renderBar()?>
  <div class="audit-bottom-range">
    <span>Showing <?= $show_start ?: 0 ?> - <?= $show_end ?: 0 ?> of <?= $totalRecords ?> entries</span>
  </div>
</form>

	</div><!-- .register-admin-account__content -->
</div><!-- .register-admin-account -->
