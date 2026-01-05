<!-- Page Header -->
<?= $page->showAdminPageInfo() ?>
<!-- End Page Header -->

<?php $activeTab = 'audit'; ?>

<div class="tabs">
    <a href="/_register/admin_account?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='login'?'active':'' ?>">Login / Registration</a>
    <a href="/_register/admin_account_contacts?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='contacts'?'active':'' ?>">Methods of Contact</a>
    <a href="/_register/admin_account_password?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='password'?'active':'' ?>">Change Password</a>
    <a href="/_register/admin_account_roles?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='roles'?'active':'' ?>">Assigned Roles</a>
    <a href="/_register/admin_account_auth_failures?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='auth_failures'?'active':'' ?>">Recent Auth Failures</a>
    <a href="/_register/admin_account_terms?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='terms'?'active':'' ?>">Terms of Use History</a>
    <a href="/_register/admin_account_locations?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='locations'?'active':'' ?>">Locations</a>
    <a href="/_register/admin_account_images?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='images'?'active':'' ?>">User Images</a>
    <a href="/_register/admin_account_backup_codes?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='backup_codes'?'active':'' ?>">Backup Codes</a>
    <a href="/_register/admin_account_search_tags?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='search_tags'?'active':'' ?>">Search Tags</a>
    <a href="/_register/admin_account_audit_log?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='audit'?'active':'' ?>">Audit Log</a>
    <a href="/_register/admin_account_register_audit?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='register_audit'?'active':'' ?>">Register Audit</a>
</div>

<div class="form_instruction">View audit log entries recorded for this account.</div>

<script>
  function goToAuditPage(offset) {
    document.getElementById('start').value = offset;
    document.getElementById('auditLogForm').submit();
  }
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

  <div class="pager_bar">
    <div class="pager_controls">
      <a href="javascript:void(0)" class="pager pagerFirst" onclick="goToAuditPage(0)">&lt;&lt;</a>
      <a href="javascript:void(0)" class="pager pagerPrevious" onclick="goToAuditPage(<?= $prev_offset ?>)">&lt;</a>
      <span>Page <?= $current_page ?> of <?= $total_pages ?></span>
      <a href="javascript:void(0)" class="pager pagerNext" onclick="goToAuditPage(<?= $next_offset ?>)">&gt;</a>
      <a href="javascript:void(0)" class="pager pagerLast" onclick="goToAuditPage(<?= $last_offset ?>)">&gt;&gt;</a>
    </div>
  </div>
  <div class="audit-bottom-range">
    <span>Showing <?= $show_start ?: 0 ?> - <?= $show_end ?: 0 ?> of <?= $totalRecords ?> entries</span>
  </div>
</form>

