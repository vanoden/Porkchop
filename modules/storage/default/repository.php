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

<?=$page->showAdminPageInfo()?>

<form name="repositoryForm" action="/_storage/repository" method="post">
	<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
    <input type="hidden" name="id" value="<?=$repository->id?>" />
    <div class="container">
        <span class="label">Name</span>
        <input type="text" name="name" class="value input wide_xl" value="<?=$form['name']?>" />
    </div>
    <div class="container">
        <span class="label">Status</span>
        <select id="status" name="status" class="value input wide_xl">
            <option value="NEW"<?php	if ($form['status'] == "NEW") print " selected"; ?>>NEW</option>
            <option value="ACTIVE"<?php	if ($form['status'] == "ACTIVE") print " selected"; ?>>ACTIVE</option>
            <option value="DISABLED"<?php	if ($form['status'] == "DISABLED") print " selected"; ?>>DISABLED</option>
        </select>
    </div>
    <div class="container">
        <span class="label">Type</span>
    <?php	 if ($repository->id) { ?>
        <span class="value"><?=$repository->type?></span>
    <?php	 } else { ?>
        <select id="type" name="type" class="value input wide_xl" onchange="getValue(this)">
            <option value="local" <?php	if ($form['type'] == "local") print " selected"; ?>>Local</option>
            <option value="s3" <?php	if ($form['type'] == "s3") print " selected"; ?>>Amazon S3</option>
            <option value="drive" <?php	if ($form['type'] == "drive") print " selected"; ?>>Google Drive</option>
            <option value="dropBox" <?php	if ($form['type'] == "dropBox") print " selected"; ?>>DropBox</option>
        </select>
    <?php	 } ?>
    </div>

	<div id="localSettings"<?php if (!empty($repository->id) && $repository->type != 'local') { print "style=\"display:none;\""; } ?>>
		<div class="container" style="margin: 10px; padding: 20px; border:dashed 1px gray; display: inline-table;">
			<span class="label">Path</span>
			<input type="text" name="path" class="value input wide_xl" value="<?=$form['path']?>" />
		</div>
	</div>
    <div id="s3Settings"<?php if (!empty($repository->id) && $repository->type != 's3') { print "style=\"display:none;\""; } ?>>
        <div class="container" style="margin: 10px; padding: 20px; border:dashed 1px gray; display: inline-table;">
<?php	foreach($metadata_keys as $key) { ?>
            <span class="label"><?=ucfirst($key)?></span>
            <input type="<?php if (preg_match('/secret/',$key)) print "password"; else print "text";?>" name="<?=$key?>" class="value input wide_xl" value="<?=$form[$key]?>" />
<?php	} ?>
        </div>
    </div>
	<div class="tableBody clean min-tablet">
		<div class="tableRowHeader">
        	<div class="tableCell" style="width: 25%;">Type</div>
        	<div class="tableCell" style="width: 25%;">ID</div>
        	<div class="tableCell" style="width: 50%;">Permissions</div>
    	</div>
    	<!-- end row header -->
		<!-- Existing Privileges -->
		<?php foreach ($default_privileges as $privilege) {
			if (empty($privilege->entity_type)) continue;
		?>
    	<div class="tableRow">
    		<div class="tableCell">
	            <?=$privilege->entity_type_name()?>
    		</div>
    		<div class="tableCell">
				<?=$privilege->entity_name()?>
    		</div>
    		<div class="tableCell">
				Read<input type="checkbox" name="privilege['<?=$privilege->entity_type?>'][<?=$privilege->entity_id?>]['r']" value="1"<?php if ($privilege->read) print " checked"; ?> />
				Write<input type="checkbox" name="privilege['<?=$privilege->entity_type?>'][<?=$privilege->entity_id?>]['w']" value="1"<?php if ($privilege->write) print " checked"; ?> />
    		</div>
		</div>
		<?php	} ?>
		<!-- New Privilege -->
    	<div class="tableRow">
    		<div class="tableCell">
	            <select name="new_privilege_entity_type" onchange="updateIds(this,'new_privilege_entity_id')">
					<option value="u">User</option>
					<option value="o">Organization</option>
					<option value="r">Role</option>
				</select>
    		</div>
    		<div class="tableCell">
				<select name="new_privilege_entity_id" class="value input"></select>
    		</div>
    		<div class="tableCell">
				Read<input type="checkbox" name="new_privilege_read" value="1" />
				Write<input type="checkbox" name="new_privilege_write" value="1" />
    		</div>
		</div>
	</div>
    
    <div class="form_footer">
        <input type="submit" name="btn_submit" class="button" value="Update" />
        <input type="button" name="btn_files" class="button" value="Browse" onclick="window.location.href='/_storage/browse?code=<?=$repository->code?>';" />
        <input type="button" name="btn_back" class="button" value="Back" onclick="window.location.href='/_storage/repositories';" />
    </div>
</form>
<?php	if ($repository->id) { ?>
<form name="repoUpload" action="/_storage/file" method="post" enctype="multipart/form-data">
    <div class="container">
	    <span class="label">Upload File</span>
		<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
	    <input type="hidden" name="repository_id" value="<?=$repository->id?>" />
	    <input type="file" name="uploadFile" />
	    <input type="submit" name="btn_submit" class="button" value="Upload" />
    </div>
</form>
<?php	} ?>
