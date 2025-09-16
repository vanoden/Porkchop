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
    if ($repository->type == 's3') {
    ?>
        window.addEventListener('load', function() {
            document.getElementById('s3Settings').style.display = 'block';
        });
    <?php
    }
    ?>
</script>

<!-- Page Header -->
<?=$page->showAdminPageInfo()?>
<!-- End Page Header -->

<form name="repositoryForm" action="/_storage/repository" method="post">
	<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
    <input type="hidden" name="id" value="<?=$repository->id?>" />
    
    <div class="form_instruction">Configure repository settings and click 'Update' to save changes.</div>

    <!-- ============================================== -->
    <!-- REPOSITORY BASIC INFORMATION -->
    <!-- ============================================== -->
    <h3>Repository Information</h3>
    <section class="tableBody clean min-tablet">
        <div class="tableRowHeader">
            <div class="tableCell width-25per">Field</div>
            <div class="tableCell width-75per">Value</div>
        </div>
        <div class="tableRow">
            <div class="tableCell">
                <span class="label">Repository Name</span>
            </div>
            <div class="tableCell">
                <input type="text" name="name" class="value input width-100per" value="<?=isset($form['name']) ? htmlspecialchars($form['name']) : ''?>" placeholder="Enter repository name" />
            </div>
        </div>
        <div class="tableRow">
            <div class="tableCell">
                <span class="label">Status</span>
            </div>
            <div class="tableCell">
                <select id="status" name="status" class="value input width-100per">
                    <option value="NEW"<?php	if (isset($form['status']) && $form['status'] == "NEW") print " selected"; ?>>NEW</option>
                    <option value="ACTIVE"<?php	if (isset($form['status']) && $form['status'] == "ACTIVE") print " selected"; ?>>ACTIVE</option>
                    <option value="DISABLED"<?php	if (isset($form['status']) && $form['status'] == "DISABLED") print " selected"; ?>>DISABLED</option>
                </select>
            </div>
        </div>
        <div class="tableRow">
            <div class="tableCell">
                <span class="label">Type</span>
            </div>
            <div class="tableCell">
                <?php if ($repository->id) { ?>
                    <span class="value"><?= htmlspecialchars($repository->type) ?></span>
                <?php } else { ?>
                    <select id="type" name="type" class="value input width-100per" onchange="getValue(this)">
                        <?php if (isset($repository_types) && is_array($repository_types)) {
                            foreach($repository_types as $type => $name) { ?>
                            <option value="<?=$type?>" <?php	if (isset($form['type']) && $form['type'] == $type) print " selected"; ?>><?= htmlspecialchars($type) ?></option>
                        <?php } 
                        } ?>
                    </select>
                <?php } ?>
            </div>
        </div>
    </section>
    <!-- ============================================== -->
    <!-- REPOSITORY CONFIGURATION -->
    <!-- ============================================== -->
    <h3>Repository Configuration</h3>
    <?php if (isset($repository_types) && is_array($repository_types)) {
        foreach ($repository_types as $type => $name) { ?>
    <div id="<?=$type?>Settings"<?php if (isset($form['type']) && $form['type'] != $type) { print " class=\"display-none\""; } ?>>
        <h4><?= htmlspecialchars($name) ?> Settings</h4>
        <section class="tableBody clean min-tablet">
            <div class="tableRowHeader">
                <div class="tableCell width-25per">Configuration Field</div>
                <div class="tableCell width-75per">Value</div>
            </div>
            <?php if (isset($metadata_keys[$type]) && is_array($metadata_keys[$type])) { 
                foreach($metadata_keys[$type] as $key) { ?>
            <div class="tableRow">
                <div class="tableCell">
                    <span class="label"><?= ucfirst(str_replace('_', ' ', $key)) ?></span>
                </div>
                <div class="tableCell">
                    <input type="<?php if (preg_match('/secret/',$key)) print "password"; else print "text";?>" 
                           name="<?=$key?>" 
                           class="value input width-100per" 
                           value="<?=isset($form[$key]) ? htmlspecialchars($form[$key]) : ''?>" 
                           placeholder="Enter <?= str_replace('_', ' ', $key) ?>" />
                </div>
            </div>
            <?php } 
            } else { ?>
            <div class="tableRow">
                <div class="tableCell width-100per text-align-center">
                    <span class="value">No configuration fields available for this repository type.</span>
                </div>
            </div>
            <?php } ?>
        </section>
    </div>
    <?php } 
    } ?>
    <!-- ============================================== -->
    <!-- REPOSITORY PRIVILEGES -->
    <!-- ============================================== -->
    <h3>Repository Privileges</h3>
    <section class="tableBody clean min-tablet">
        <div class="tableRowHeader">
            <div class="tableCell width-20per">Entity Type</div>
            <div class="tableCell width-30per">Entity Name</div>
            <div class="tableCell width-30per">Permissions</div>
            <div class="tableCell width-20per">Actions</div>
        </div>
        <!-- Existing Privileges -->
        <?php if (isset($default_privileges) && is_array($default_privileges)) {
            foreach ($default_privileges as $privilege) {
                if (empty($privilege->entity_type)) continue;
        ?>
        <div class="tableRow">
            <div class="tableCell">
                <span class="value"><?= htmlspecialchars($privilege->entity_type_name()) ?></span>
            </div>
            <div class="tableCell">
                <span class="value"><?= htmlspecialchars($privilege->entity_name()) ?></span>
            </div>
            <div class="tableCell">
                <div class="checkbox-row">
                    <label class="checkbox-label">
                        <input type="checkbox" name="privilege['<?=$privilege->entity_type?>'][<?=$privilege->entity_id?>]['r']" value="1"<?php if ($privilege->read) print " checked"; ?> />
                        <span class="value">Read</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="privilege['<?=$privilege->entity_type?>'][<?=$privilege->entity_id?>]['w']" value="1"<?php if ($privilege->write) print " checked"; ?> />
                        <span class="value">Write</span>
                    </label>
                </div>
            </div>
            <div class="tableCell">
                <span class="value">Existing</span>
            </div>
        </div>
        <?php } 
        } ?>
        <!-- Add New Privilege -->
        <div class="tableRow">
            <div class="tableCell">
                <select name="new_privilege_entity_type" class="value input width-100per" onchange="updateIds(this,'new_privilege_entity_id')">
                    <option value="u">User</option>
                    <option value="o">Organization</option>
                    <option value="r">Role</option>
                </select>
            </div>
            <div class="tableCell">
                <select name="new_privilege_entity_id" class="value input width-100per"></select>
            </div>
            <div class="tableCell">
                <div class="checkbox-row">
                    <label class="checkbox-label">
                        <input type="checkbox" name="new_privilege_read" value="1" />
                        <span class="value">Read</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="new_privilege_write" value="1" />
                        <span class="value">Write</span>
                    </label>
                </div>
            </div>
            <div class="tableCell">
                <span class="value">New</span>
            </div>
        </div>
    </section>
    
    <!-- ============================================== -->
    <!-- FORM ACTIONS -->
    <!-- ============================================== -->
    <div class="form_footer marginTop_20">
        <input type="submit" name="btn_submit" class="button" value="Update" />
        <?php if ($repository->id) { ?>
        <input type="button" name="btn_files" class="button secondary" value="Browse Files" onclick="window.location.href='/_storage/browse?code=<?=$repository->code?>';" />
        <?php } ?>
        <input type="button" name="btn_back" class="button secondary" value="Back to Repositories" onclick="window.location.href='/_storage/repositories';" />
    </div>
</form>

<?php if ($repository->id) { ?>
    <!-- ============================================== -->
    <!-- FILE UPLOAD SECTION -->
    <!-- ============================================== -->
    <h3>Upload Files</h3>
    <form name="repoUpload" action="/_storage/file" method="post" enctype="multipart/form-data">
        <section class="tableBody clean min-tablet">
            <div class="tableRowHeader">
                <div class="tableCell width-50per">File Upload</div>
                <div class="tableCell width-50per">Instructions</div>
            </div>
            <div class="tableRow">
                <div class="tableCell">
                    <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
                    <input type="hidden" name="repository_id" value="<?=$repository->id?>" />
                    <div class="label">Choose File</div>
                    <input type="file" name="uploadFile" class="value input width-100per" accept="*/*" />
                    <div class="marginTop_10">
                        <input type="submit" name="btn_submit" class="button" value="Upload" />
                    </div>
                </div>
                <div class="tableCell">
                    <div class="label">Upload Guidelines</div>
                    <div class="value">
                        <ul style="margin: 0; padding-left: 20px;">
                            <li>Select a file from your computer</li>
                            <li>File will be uploaded to the repository root</li>
                            <li>Use the Browse Files button to organize files</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
    </form>
<?php } ?>
