<script type="text/javascript">
    function getValue(type) {
        var selectedText = type.options[type.selectedIndex].innerHTML;
        var selectedValue = type.value;
        if (selectedValue == 's3') {
            document.getElementById('s3Settings').style.display = 'block';
			document.getElementById('localSettings').style.display = 'none';
        }
		else if (selectedValue == 'local') {
			document.getElementById('localSettings').style.display = 'block';
			document.getElementById('s3Settings').style.display = 'none';
		}
		else {
            document.getElementById('s3Settings').style.display = 'none';
			document.getElementById('localSettings').style.display = 'none';
        }
    }
	function updateIds(typeElem,entityElemName) {
		var entityElem = typeElem.form[entityElemName];

		// Remove Existing Items
		var idx, len = entityElem.options.length - 1;
		for (idx = len; idx >= 0; idx--) {
			entityElem.remove(idx);
		}

		console.log("updateIds()");
		if (typeElem.value == 'u') {
			console.log("Populating users list");
			var customerlist = Object.create(CustomerList);
			var customers = customerlist.find();
			console.log(customers);
			for (i = 0; i < customers.length; i ++) {
				console.log("Adding user "+customers[i].full_name+" to list");
				var option = document.createElement("option");
				option.text = customers[i].code;
				option.value = customers[i].id;
				entityElem.add(option);
			}
		}
		else if (typeElem.value == 'o') {
			console.log("Populating organizations list");
			var organizationlist = Object.create(OrganizationList);
			var organizations = organizationlist.find();
			console.log(organizations);
			for (i = 0; i < organizations.length; i ++) {
				console.log("Adding organization "+organizations[i].name+" to list");
				var option = document.createElement("option");
				option.text = organizations[i].name;
				option.value = organizations[i].id;
				entityElem.add(option);
			}
		}
		else if (typeElem.value == 'r') {
			console.log("Populating roles list");
			var rolelist = Object.create(RoleList);
			var roles = rolelist.find();
			console.log(roles);
			for (i = 0; i < roles.length; i ++) {
				console.log("Adding role "+roles[i].name+" to list");
				var option = document.createElement("option");
				option.text = roles[i].name;
				option.value = roles[i].id;
				entityElem.add(option);
			}
		}
	}
    <?php
    if (isset($repository->type) && $repository->type == 's3') {
    ?>
        window.addEventListener('load', function() {
            document.getElementById('s3Settings').style.display = 'block';
        });
    <?php
    }
    ?>
</script>

<?=$page->showAdminPageInfo()?>

<div class="storage-repository-layout">
<form name="repositoryForm" action="/_storage/repository" method="post" class="storage-repository-form">
	<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
	<input type="hidden" name="id" value="<?=$repository->id?>" />

	<section class="storage-repository-section">
		<h3 class="storage-repository-section-title">Basic information</h3>
		<div class="storage-repository-form-grid">
			<div class="storage-repository-field">
				<label for="repo-name" class="storage-repository-label">Name</label>
				<input type="text" id="repo-name" name="name" class="storage-repository-input" value="<?= isset($form['name']) ? htmlspecialchars($form['name']) : '' ?>" />
			</div>
			<div class="storage-repository-field">
				<label for="status" class="storage-repository-label">Status</label>
				<select id="status" name="status" class="storage-repository-input">
					<option value="NEW"<?php if (isset($form['status']) && $form['status'] == 'NEW') print ' selected'; ?>>NEW</option>
					<option value="ACTIVE"<?php if (isset($form['status']) && $form['status'] == 'ACTIVE') print ' selected'; ?>>ACTIVE</option>
					<option value="DISABLED"<?php if (isset($form['status']) && $form['status'] == 'DISABLED') print ' selected'; ?>>DISABLED</option>
				</select>
			</div>
			<div class="storage-repository-field">
				<label class="storage-repository-label">Type</label>
				<?php if ($repository->id) { ?>
					<span class="storage-repository-value"><?= $repository->type ?></span>
				<?php } else { ?>
					<select id="type" name="type" class="storage-repository-input" onchange="getValue(this)">
						<?php if (isset($repository_types) && is_array($repository_types)) {
							foreach ($repository_types as $type => $name) { ?>
								<option value="<?= $type ?>"<?php if (isset($form['type']) && $form['type'] == $type) print ' selected'; ?>><?= $type ?></option>
							<?php }
						} ?>
					</select>
				<?php } ?>
			</div>
		</div>
	</section>

	<section class="storage-repository-section">
		<h3 class="storage-repository-section-title">Configuration</h3>
		<?php if (isset($repository_types) && is_array($repository_types)) {
			foreach ($repository_types as $type => $name) { ?>
		<div id="<?= $type ?>Settings" class="storage-repository-type-config<?php if (isset($form['type']) && $form['type'] != $type) { ?> display-none<?php } ?>">
			<h4 class="storage-repository-subtitle"><?= $name ?></h4>
			<div class="storage-repository-config-box">
				<?php if (isset($metadata_keys[$type]) && is_array($metadata_keys[$type])) {
					foreach ($metadata_keys[$type] as $key) { ?>
				<div class="storage-repository-field">
					<label for="repo-<?= htmlspecialchars($key) ?>" class="storage-repository-label"><?= ucfirst($key) ?></label>
					<input type="<?= preg_match('/secret/', $key) ? 'password' : 'text' ?>" id="repo-<?= htmlspecialchars($key) ?>" name="<?= htmlspecialchars($key) ?>" class="storage-repository-input" value="<?= isset($form[$key]) ? htmlspecialchars($form[$key]) : '' ?>" />
				</div>
				<?php }
				} ?>
			</div>
		</div>
		<?php }
		} ?>
	</section>

	<section class="storage-repository-section">
		<h3 class="storage-repository-section-title">Privileges</h3>
		<div class="tableBody bandedRows clean min-tablet">
			<div class="tableRowHeader">
				<div class="tableCell tableCell-width-25">Type</div>
				<div class="tableCell tableCell-width-25">ID</div>
				<div class="tableCell tableCell-width-50">Permissions</div>
			</div>
			<?php if (isset($default_privileges) && is_array($default_privileges)) {
				foreach ($default_privileges as $privilege) {
					if (empty($privilege->entity_type)) continue;
			?>
			<div class="tableRow">
				<div class="tableCell"><?= $privilege->entity_type_name() ?></div>
				<div class="tableCell"><?= $privilege->entity_name() ?></div>
				<div class="tableCell storage-repository-permissions">
					<label class="storage-repository-check-label"><input type="checkbox" name="privilege['<?= $privilege->entity_type ?>'][<?= $privilege->entity_id ?>]['r']" value="1"<?php if ($privilege->read) print ' checked'; ?> /> Read</label>
					<label class="storage-repository-check-label"><input type="checkbox" name="privilege['<?= $privilege->entity_type ?>'][<?= $privilege->entity_id ?>]['w']" value="1"<?php if ($privilege->write) print ' checked'; ?> /> Write</label>
				</div>
			</div>
			<?php }
			} ?>
			<div class="tableRow">
				<div class="tableCell">
					<select name="new_privilege_entity_type" onchange="updateIds(this,'new_privilege_entity_id')" class="storage-repository-input storage-repository-input-inline">
						<option value="u">User</option>
						<option value="o">Organization</option>
						<option value="r">Role</option>
					</select>
				</div>
				<div class="tableCell">
					<select name="new_privilege_entity_id" class="storage-repository-input storage-repository-input-inline"></select>
				</div>
				<div class="tableCell storage-repository-permissions">
					<label class="storage-repository-check-label"><input type="checkbox" name="new_privilege_read" value="1" /> Read</label>
					<label class="storage-repository-check-label"><input type="checkbox" name="new_privilege_write" value="1" /> Write</label>
				</div>
			</div>
		</div>
	</section>

	<div class="storage-repository-actions">
		<button type="submit" name="btn_submit" class="button">Update</button>
		<?php if ($repository->id && $repository->code) { ?>
		<button type="button" name="btn_files" class="button" onclick="window.location.href='/_storage/browse?code=<?= htmlspecialchars($repository->code) ?>';">Browse</button>
		<?php } ?>
		<button type="button" name="btn_back" class="button" onclick="window.location.href='/_storage/repositories';">Back</button>
	</div>
</form>

<?php if ($repository->id) { ?>
<form name="repoUpload" action="/_storage/file" method="post" enctype="multipart/form-data" class="storage-repository-upload">
	<input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
	<input type="hidden" name="repository_id" value="<?= $repository->id ?>" />
	<label for="uploadFile" class="storage-repository-label">Upload file</label>
	<div class="storage-repository-upload-row">
		<input type="file" name="uploadFile" id="uploadFile" class="storage-repository-file-input" />
		<button type="submit" name="btn_submit" class="button">Upload</button>
	</div>
</form>
<?php } ?>
</div>
