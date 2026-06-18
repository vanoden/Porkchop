<!--Comment for testing-->
<script type="text/javascript">
	function submitForm() {
		if (document.register.password.value.length > 0 || document.register.password_2.value.length > 0) {
			if (document.register.password.value.length < 6) {
				alert("Your password is too short.");
				return false;
			}
			
			if (document.register.password.value != document.register.password_2.value) {
				alert("Your passwords don't match.");
				return false;
			}
		}
		return true;
	}
	function submitSearch(start) {
		document.getElementById('start').value=start;
		document.getElementById('custSearch').submit();
		return true;
	}
</script>

<!-- Page Header -->
<?= $page->showAdminPageInfo() ?>

<form id="custSearch" class="filter-bar" method="get">
  <input type="text" id="searchAccountInput" name="search" placeholder="account name" value="<?=isset($_REQUEST['search']) ? htmlspecialchars($_REQUEST['search']) : ''?>">

  <label class="check-field">
    <input type="checkbox" name="hidden" value="1" <?php if (isset($_REQUEST['hidden'])) print "checked"; ?>>
    Hidden
  </label>

  <label class="check-field">
    <input type="checkbox" name="expired" value="1" <?php if (isset($_REQUEST['expired'])) print "checked"; ?>>
    Expired
  </label>

  <label class="check-field">
    <input type="checkbox" name="blocked" value="1" <?php if (isset($_REQUEST['blocked'])) print "checked"; ?>>
    Blocked
  </label>

  <label class="check-field">
    <input type="checkbox" name="deleted" value="1" <?php if (isset($_REQUEST['deleted'])) print "checked"; ?>>
    Deleted
  </label>

  <input type="hidden" id="start" name="start" value="0">

  <div class="form-field form-field--cluster">
    <label for="<?=$pagination->sizeElemName?>">Records per page:</label>
    <input type="text" id="<?=$pagination->sizeElemName?>" name="<?=$pagination->sizeElemName?>" class="value input width-45px" value="<?=$pagination->size()?>">
  </div>

  <div class="button-group">
    <button type="submit" id="searchOrganizationButton" name="btn_search" onclick="submitSearch(0)">Search</button>
  </div>
</form>

<table class="responsive-table responsive-table--banded">
  <colgroup>
    <col class="col-w-15">
    <col class="col-w-15">
    <col class="col-w-15">
    <col class="col-w-10">
    <col>
  </colgroup>
  <thead>
    <tr>
      <th scope="col">Login</th>
      <th scope="col">First Name</th>
      <th scope="col">Last Name</th>
      <th scope="col">Status</th>
      <th scope="col">Last Active</th>
    </tr>
  </thead>
  <tbody>
<?php
  if (! $page->errorCount()) {
  foreach ($customers as $customer) { 
    if (isset($greenbar)) $greenbar = ''; else $greenbar = " greenbar";
?>
    <tr>
      <td data-label="Login"><a class="value<?=$greenbar?>" href="<?=PATH."/_register/account?customer_id=".$customer->id?>"><?=$customer->code?></a></td>
      <td data-label="First Name"><?=htmlspecialchars($customer->first_name)?></td>
      <td data-label="Last Name"><?=htmlspecialchars($customer->last_name)?></td>
      <td data-label="Status"><?=htmlspecialchars($customer->status)?></td>
      <td data-label="Last Active"><?=$customer->last_active()?></td>
    </tr>
<?php		
  }}
?>
  </tbody>
</table>

<!--    Standard Page Navigation Bar -->
<div class="pagination" id="pagination">
    <?=$pagination->renderPages(); ?>
</div>

<?php
  if ($GLOBALS['_SESSION_']->customer->can('manage customers',\Register\PrivilegeLevel::ORGANIZATION_MANAGER)) {
?>

<form action="<?=PATH?>/_register/register" method="get">
    <div class="form-actions filter-bar">
        <div class="button-group filter-bar__actions">
            <button type="submit" name="button_submit" class="input button" value="Add Account">Add Account</button>
        </div>
    </div>
</form>

<?php	} ?>
<!--    [end] Standard Page Navigation Bar ADMIN ONLY-->
