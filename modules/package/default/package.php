<?= $page->showAdminPageInfo() ?>

<div class="package-edit-layout">
<form name="packageForm" method="POST" action="/_package/package" class="package-edit-form">
	<input type="hidden" name="package_id" value="<?= $package->id ?>" />
	<input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>" />

	<section class="package-edit-section">
		<h3 class="package-edit-section-title">Package details</h3>
		<div class="package-edit-grid">
			<?php if ($package->id) { ?>
			<div class="package-edit-field">
				<span class="package-edit-label">Created</span>
				<span class="package-edit-value"><?= $package->date_created ?></span>
			</div>
			<?php } else { ?>
			<div class="package-edit-field">
				<label for="package-code" class="package-edit-label">Code</label>
				<input type="text" id="package-code" name="code" class="package-edit-input" value="" />
			</div>
			<?php } ?>

			<div class="package-edit-field">
				<label for="package-name" class="package-edit-label">Name</label>
				<input type="text" id="package-name" name="name" class="package-edit-input" value="<?= htmlspecialchars($package->name ?? '') ?>" />
			</div>

			<div class="package-edit-field package-edit-field-wide">
				<label for="package-description" class="package-edit-label">Description</label>
				<textarea id="package-description" name="description" class="package-edit-input package-edit-textarea" rows="3"><?= htmlspecialchars(strip_tags($package->description ?? '')) ?></textarea>
			</div>

			<div class="package-edit-field">
				<label for="package-platform" class="package-edit-label">Platform</label>
				<input type="text" id="package-platform" name="platform" class="package-edit-input" value="<?= htmlspecialchars($package->platform ?? '') ?>" />
			</div>

			<div class="package-edit-field">
				<label for="package-license" class="package-edit-label">License</label>
				<input type="text" id="package-license" name="license" class="package-edit-input" value="<?= htmlspecialchars($package->license ?? '') ?>" />
			</div>

			<div class="package-edit-field">
				<label for="package-owner" class="package-edit-label">Owner</label>
				<select id="package-owner" name="owner_id" class="package-edit-input">
					<?php foreach ($admins as $owner) { ?>
					<option value="<?= $owner->id ?>"<?php if (isset($package->owner->id) && $package->owner->id == $owner->id) print ' selected'; ?>><?= htmlspecialchars($owner->code) ?></option>
					<?php } ?>
				</select>
			</div>

			<div class="package-edit-field">
				<label class="package-edit-label">Repository</label>
				<?php if ($package->id && isset($package->repository)) { ?>
				<span class="package-edit-value"><?= htmlspecialchars($package->repository->name) ?></span>
				<?php } else { ?>
				<select name="repository_id" class="package-edit-input">
					<?php foreach ($repositories as $repository) { ?>
					<option value="<?= $repository->id ?>"><?= htmlspecialchars($repository->name) ?></option>
					<?php } ?>
				</select>
				<?php } ?>
			</div>

			<div class="package-edit-field">
				<label for="package-status" class="package-edit-label">Status</label>
				<select id="package-status" name="status" class="package-edit-input">
					<?php foreach ($statii as $status) { ?>
					<option value="<?= $status ?>"<?php if (isset($package->status) && $package->status == $status) print ' selected'; ?>><?= $status ?></option>
					<?php } ?>
				</select>
			</div>
		</div>
	</section>

	<div class="package-edit-actions">
		<button type="submit" name="btn_submit" class="button">Update</button>
		<button type="button" name="btn_ver" class="button" onclick="window.location.href='/_package/versions?code=<?= htmlspecialchars($package->code ?? '') ?>';">Versions</button>
		<button type="button" name="btn_back" class="button" onclick="window.location.href='/_package/packages';">Back</button>
	</div>
</form>
</div>
