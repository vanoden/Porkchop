<!-- Page Header -->
<?= $page->showAdminPageInfo() ?>
<!-- End Page Header -->

<?php $activeTab = 'register_audit'; ?>

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

<div class="form_instruction">View authentication failure records from register_auth_failures for this account.</div>

<script>
  function goToRegisterAuditPage(offset) {
    document.getElementById('start').value = offset;
    document.getElementById('registerAuditForm').submit();
  }
  function changeRegisterAuditPageSize(size) {
    document.getElementById('page_size').value = size;
    document.getElementById('start').value = 0;
    document.getElementById('registerAuditForm').submit();
  }
</script>

<form id="registerAuditForm" method="get" action="<?= PATH ?>/_register/admin_account_register_audit">
  <input type="hidden" name="customer_id" value="<?= $customer_id ?>" />
  <input type="hidden" id="start" name="start" value="<?= $start_offset ?>" />
  <input type="hidden" id="page_size" name="page_size" value="<?= $page_size ?>" />

  <div class="audit-top-controls">
    <div class="audit-page-size">
      <label>Records per page:
        <select name="page_size_select" onchange="changeRegisterAuditPageSize(this.value);">
          <?php foreach ($page_size_options as $option) { ?>
            <option value="<?= $option ?>" <?= $page_size == $option ? 'selected' : '' ?>><?= $option ?></option>
          <?php } ?>
        </select>
      </label>
    </div>
  </div>

  <div class="tableBody">
    <div class="tableRowHeader">
        <div class="tableCell width-15per">Date</div>
        <div class="tableCell width-20per">IP Address</div>
        <div class="tableCell width-15per">Login</div>
        <div class="tableCell width-15per">Reason</div>
        <div class="tableCell width-35per">Endpoint</div>
    </div>
    <?php if (!empty($authFailureRecords)) {
        foreach ($authFailureRecords as $record) {
    ?>
    <div class="tableRow">
        <div class="tableCell"><?= shortDate($record->date) ?></div>
        <div class="tableCell"><?= htmlspecialchars($record->ip_address ?? '') ?></div>
        <div class="tableCell"><?= htmlspecialchars($record->login ?? '') ?></div>
        <div class="tableCell"><?= htmlspecialchars($record->reason ?? '') ?></div>
        <div class="tableCell"><?= htmlspecialchars($record->endpoint ?? '') ?></div>
    </div>
    <?php }
    } else { ?>
    <div class="tableRow">
        <div class="tableCell width-100per">No authentication failure records found for this account.</div>
    </div>
    <?php } ?>
  </div>

  <div class="pager_bar">
    <div class="pager_controls">
      <a href="javascript:void(0)" class="pager pagerFirst" onclick="goToRegisterAuditPage(0)">&lt;&lt;</a>
      <a href="javascript:void(0)" class="pager pagerPrevious" onclick="goToRegisterAuditPage(<?= $prev_offset ?>)">&lt;</a>
      <span>Page <?= $current_page ?> of <?= $total_pages ?></span>
      <a href="javascript:void(0)" class="pager pagerNext" onclick="goToRegisterAuditPage(<?= $next_offset ?>)">&gt;</a>
      <a href="javascript:void(0)" class="pager pagerLast" onclick="goToRegisterAuditPage(<?= $last_offset ?>)">&gt;&gt;</a>
    </div>
  </div>
  <div class="audit-bottom-range">
    <span>Showing <?= $show_start ?: 0 ?> - <?= $show_end ?: 0 ?> of <?= $totalRecords ?> entries</span>
  </div>
</form>

