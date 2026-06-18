<script type="text/javascript">
	function resetPaginationAndSearch() {
		document.getElementById('pagination_start_id').value = '0';
	}
</script>

<?= $page->showAdminPageInfo() ?>

<form id="custSearch" method="get" class="monitor-admin-list register-admin-accounts">
	<input type="hidden" id="pagination_start_id" name="pagination_start_id" value="<?=htmlspecialchars((string)$pagination->startId(), ENT_QUOTES, 'UTF-8')?>">

	<div class="filter-bar">
		<div class="filter-bar__controls">
			<div class="form-field filter-bar__search">
				<label for="searchAccountInput">Account</label>
				<input type="text" id="searchAccountInput" name="search" placeholder="account name" value="<?=htmlspecialchars($_REQUEST['search'] ?? '', ENT_QUOTES, 'UTF-8')?>"/>
			</div>
			<div class="form-field form-field--narrow">
				<label for="pagination_size">Per page</label>
				<input type="text" id="pagination_size" name="<?=$pagination->sizeElemName?>" class="register-organizations-pagination-size" value="<?=htmlspecialchars((string)$pagination->size(), ENT_QUOTES, 'UTF-8')?>"/>
			</div>
		</div>
		<div class="form-field form-field--checks register-admin-accounts-filter-checks">
			<span class="form-field__group-label">Status</span>
			<div class="form-field__check-options">
			<label class="check-field">
				<input type="checkbox" name="hidden" value="1" <?php if (!empty($_REQUEST['hidden'])) print "checked"; ?>>
				Hidden
			</label>
			<label class="check-field">
				<input type="checkbox" name="expired" value="1" <?php if (!empty($_REQUEST['expired'])) print "checked"; ?>>
				Expired
			</label>
			<label class="check-field">
				<input type="checkbox" name="blocked" value="1" <?php if (!empty($_REQUEST['blocked'])) print "checked"; ?>>
				Blocked
			</label>
			<label class="check-field">
				<input type="checkbox" name="deleted" value="1" <?php if (!empty($_REQUEST['deleted'])) print "checked"; ?>>
				Deleted
			</label>
			</div>
		</div>
		<div class="button-group filter-bar__actions">
			<button type="submit" id="searchAccountButton" name="btn_search" value="Search" onclick="resetPaginationAndSearch()">Search</button>
<?php if ($GLOBALS['_SESSION_']->customer->can('manage customers')) { ?>
			<a class="button btn-secondary" href="<?=PATH?>/_register/register">Add Account</a>
<?php } ?>
		</div>
	</div>

	<h2>Accounts [<?=$totalRecords?>]</h2>
	<table class="responsive-table responsive-table--banded">
		<thead>
			<tr>
				<th scope="col" class="col-w-15">Login</th>
				<th scope="col" class="col-w-15">First Name</th>
				<th scope="col" class="col-w-15">Last Name</th>
				<th scope="col" class="register-accounts-organization-cell">Organization</th>
				<th scope="col" class="col-w-10">Status</th>
				<th scope="col" class="col-w-15">Last Active</th>
			</tr>
		</thead>
		<tbody>
<?php if (!$page->errorCount() && is_array($customers) && count($customers)) {
		foreach ($customers as $customer) {
			if (!empty($customer->organization_id)) {
				$organization_id = $customer->organization_id;
				$organization = $customer->organization();
				$organization_name = $organization ? $organization->name : 'Unknown Organization';
			} else {
				$organization_id = 0;
				$organization_name = '';
			}
?>
			<tr>
				<td data-label="Login"><a href="<?=PATH."/_register/admin_account?customer_id=".$customer->id?>"><?=htmlspecialchars($customer->code)?></a></td>
				<td data-label="First Name"><?=htmlspecialchars($customer->first_name)?></td>
				<td data-label="Last Name"><?=htmlspecialchars($customer->last_name)?></td>
				<td data-label="Organization" class="register-accounts-organization-cell"><a href="/_register/admin_organization?organization_id=<?=$organization_id?>"><?=htmlspecialchars($organization_name)?></a></td>
				<td data-label="Status"><?=htmlspecialchars($customer->status)?></td>
				<td data-label="Last Active"><?=htmlspecialchars((string)$customer->last_active())?></td>
			</tr>
<?php
		}
	} else { ?>
			<tr>
				<td colspan="6">No accounts found</td>
			</tr>
<?php } ?>
		</tbody>
	</table>

	<div class="pagination pagination-bar" id="pagination">
		<?=$pagination->renderPages()?>
	</div>
</form>
