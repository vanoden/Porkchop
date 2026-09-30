<?php
/** Shared tab nav for admin customer account pages. Requires $activeTab, $customer_id, $customer. */
if (!isset($activeTab)) {
	$activeTab = 'login';
}
if (empty($customer_id) && !empty($customer->id)) {
	$customer_id = $customer->id;
}

$customerThumbnailUrl = null;
$customerDisplayName = '';
if (!empty($customer->id)) {
	$customerDisplayName = trim((string)$customer->full_name());
	if ($customerDisplayName === '') {
		$customerDisplayName = (string)$customer->code;
	}
	$defaultImageId = $customer->getMetadata('default_image');
	if ($defaultImageId) {
		$thumbFile = new \Storage\File($defaultImageId);
		if ($thumbFile->id) {
			$customerThumbnailUrl = '/_storage/downloadfile?file_id=' . (int)$defaultImageId;
		}
	}
}
?>
<div class="register-admin-account">
<?php if ($customerThumbnailUrl) { ?>
	<div class="register-admin-account__identity">
		<img src="<?= htmlspecialchars($customerThumbnailUrl, ENT_QUOTES, 'UTF-8') ?>" alt="" class="register-admin-account__thumbnail" width="48" height="48">
		<span class="register-admin-account__name"><?= htmlspecialchars($customerDisplayName) ?></span>
	</div>
<?php } ?>
	<nav class="tabs register-admin-account__tabs" aria-label="Customer account sections">
		<a href="/_register/admin_account?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab === 'login' ? 'active' : '' ?>">Login / Registration</a>
		<a href="/_register/admin_account_contacts?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab === 'contacts' ? 'active' : '' ?>">Methods of Contact</a>
		<a href="/_register/admin_account_password?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab === 'password' ? 'active' : '' ?>">Change Password</a>
		<a href="/_register/admin_account_roles?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab === 'roles' ? 'active' : '' ?>">Assigned Roles</a>
		<a href="/_register/admin_account_auth_failures?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab === 'auth_failures' ? 'active' : '' ?>">Recent Auth Failures</a>
		<a href="/_register/admin_account_terms?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab === 'terms' ? 'active' : '' ?>">Terms of Use History</a>
		<a href="/_register/admin_account_locations?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab === 'locations' ? 'active' : '' ?>">Locations</a>
		<a href="/_register/admin_account_images?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab === 'images' ? 'active' : '' ?>">User Images</a>
		<a href="/_register/admin_account_backup_codes?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab === 'backup_codes' ? 'active' : '' ?>">Backup Codes</a>
		<a href="/_register/admin_account_search_tags?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab === 'search_tags' ? 'active' : '' ?>">Search Tags</a>
		<a href="/_register/admin_account_audit_log?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab === 'audit' ? 'active' : '' ?>">Audit Log</a>
		<a href="/_register/admin_account_register_audit?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab === 'register_audit' ? 'active' : '' ?>">Failed Logins</a>
		<a href="/_register/admin_account_privileges?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab === 'privileges' ? 'active' : '' ?>">Assigned Privileges</a>
	</nav>
	<div class="register-admin-account__content">
