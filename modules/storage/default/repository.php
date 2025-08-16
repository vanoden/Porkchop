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
    <h3>Name</h3>
    <input type="text" name="name" class="value input width-300px" value="<?=$form['name']?>" />
    <h3>Status</h3>
    <select id="status" name="status" class="value input width-300px">
        <option value="NEW"<?php	if ($form['status'] == "NEW") print " selected"; ?>>NEW</option>
        <option value="ACTIVE"<?php	if ($form['status'] == "ACTIVE") print " selected"; ?>>ACTIVE</option>
        <option value="DISABLED"<?php	if ($form['status'] == "DISABLED") print " selected"; ?>>DISABLED</option>
    </select>
    <h3>Type</h3>
<?php	 if ($repository->id) { ?>
    <span class="value"><?=$repository->type?></span>
<?php	 } else { ?>
    <select id="type" name="type" class="value input width-300px" onchange="getValue(this)">
<?php	if (isset($repository_types) && is_array($repository_types)) {
		foreach($repository_types as $type => $name) { ?>
        <option value="<?=$type?>" <?php	if ($form['type'] == "<?=$type?>") print " selected"; ?>><?=$type?></option>
<?php	 		} 
		} ?>
	</select>
<?php	} ?>
	<h3>Configuration</h3>
<?php	if (isset($repository_types) && is_array($repository_types)) {
		foreach ($repository_types as $type => $name) { ?>
    <div id="<?=$type?>Settings"<?php if ($form['type'] != $type) { print " class=\"display-none\""; } ?>>
		<h4><?=$name?></h4>
        <div class="container container-dashed-gray">
<?php	if (isset($metadata_keys[$type]) && is_array($metadata_keys[$type])) { 
		foreach($metadata_keys[$type] as $key) { ?>
            <span class="label"><?=ucfirst($key)?></span>
            <input type="<?php if (preg_match('/secret/',$key)) print "password"; else print "text";?>" name="<?=$key?>" class="value input width-300px" value="<?=$form[$key]?>" />
<?php		} 
	} ?>
        </div>
    </div>
<?php		} 
	} ?>
	<h3>Privileges</h3>
	<div class="tableBody clean min-tablet">
		<div class="tableRowHeader">
        	<div class="tableCell tableCell-width-25">Type</div>
        	<div class="tableCell tableCell-width-25">ID</div>
        	<div class="tableCell tableCell-width-50">Permissions</div>
    	</div>
    	<!-- end row header -->
		<!-- Existing Privileges -->
		<?php
			if (isset($default_privileges) && is_array($default_privileges)) {
				foreach ($default_privileges as $privilege) {
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
		<?php		} 
			} ?>
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
