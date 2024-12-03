<?= $page->showAdminPageInfo() ?>
<style>
	span.label {
		display: inline-block;
		width: 150px;
		font-weight: bold;
	}
</style>
<script language="Javascript">
	function updateIds(elem) {
		console.log("updateIds()");
		if (elem.value == 'u') {
			console.log("Populating users list");
			var customerlist = Object.create(CustomerList);
			var customers = customerlist.find();
			console.log(customers);
			for (i = 0; i < customers.length; i++) {
				console.log("Adding user " + customers[i].full_name + " to list");
				var option = document.createElement("option");
				option.text = customers[i].code;
				option.value = customers[i].id;
				elem.form.perm_id.add(option);
			}
		}
		else if (elem.value == 'o') {
			console.log("Populating organizations list");
			var organizationlist = Object.create(OrganizationList);
			var organizations = organizationlist.find();
			console.log(organizations);
			for (i = 0; i < organizations.length; i++) {
				console.log("Adding organization " + organizations[i].name + " to list");
				var option = document.createElement("option");
				option.text = organizations[i].name;
				option.value = organizations[i].id;
				elem.form.perm_id.add(option);
			}
		}
		else if (elem.value == 'r') {
			console.log("Populating roles list");
			var rolelist = Object.create(RoleList);
			var roles = rolelist.find();
			console.log(roles);
			for (i = 0; i < roles.length; i++) {
				console.log("Adding role " + roles[i].name + " to list");
				var option = document.createElement("option");
				option.text = roles[i].name;
				option.value = roles[i].id;
				elem.form.perm_id.add(option);
			}
		}
	}
</script>
<form name="fileForm" action="/_storage/file" method="post">
	<input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
	<input type="hidden" name="id" value="<?= $file->id ?>">
	<h3>File Details</h3>
	<div class="inline-block" style="width: 100%; float: left;">
		<div class="container fileDetailContainer">
			<span class="label">Code</span>
			<span class="value"><?= $file->code ?>
		</div>
		<div class="container fileDetailContainer">
			<span class="label">Display Name</span>
			<input type="text" class="value input" name="display_name" value="<?= $file->display_name ?>" />
		</div>
		<div class="container fileDetailContainer">
			<span class="label">Repository</span>
			<span class="value"><?= $file->repository()->name ?></span>
		</div>
		<div class="container fileDetailContainer">
			<span class="label">Name</span>
			<input type="text" class="value input" name="name" value="<?= $file->name() ?>" />
		</div>
		<div class="container fileDetailContainer">
			<span class="label">Path</span>
			<input class="value input" name="path" type="text" value="<?= $file->path() ?>" />
		</div>
		<div class="container fileDetailContainer">
			<span class="label">Size</span>
			<span class="value"><?= $file->size ?></span>
		</div>
		<div class="container fileDetailContainer">
			<span class="label">Mime-Type</span>
			<span class="value"><?= $file->mime_type ?></span>
		</div>
		<div class="container fileDetailContainer">
			<span class="label">Date</span>
			<span class="value"><?= $file->date_created ?></span>
		</div>
		<div class="container fileDetailContainer">
			<span class="label">Owner</span>
			<span class="value"><?= $file->owner()->code ?></span>
		</div>
		<div class="container fileDetailContainer">
			<span class="label">Download URI</span>
			<span class="value"><?= $file->downloadURI() ?></span>
		</div>
		<?php if ($file->mime_type == "image/jpeg" || $file->mime_type == "image/png" || $file->mime_type == "image/gif") { ?>
			<h3>Preview</h3>
			<div id="image_preview" style="float: left">
				<img src="<?= $file->downloadURI() ?>" id="image_preview" style="max-width: 300px; max-height: 300px; border: 1px solid black" />
			</div>
		<?php   } ?>
		<h3>Permissions</h3>
		<div class="tableBody min-tablet">
			<div class="tableRowHeader">
				<div class="tableCell">Type</div>
				<div class="tableCell">Name</div>
				<div class="tableCell">Read</div>
				<div class="tableCell">Write</div>
			</div>
			<?php foreach ($privileges as $privilege) {
			?>
				<div class="tableRow">
					<div class="tableCell"><?= $privilege->entity_type_name() ?></div>
					<div class="tableCell"><?= $privilege->entity_name() ?></div>
			        <div class="tableCell"><input type="checkbox" name="privilege['<?=$privilege->entity_type?>'][<?=$privilege->entity_id?>]['r']" value="1" <?php if ($privilege->read) { print "checked"; }?>/></div>
			        <div class="tableCell"><input type="checkbox" name="privilege['<?=$privilege->entity_type?>'][<?=$privilege->entity_id?>]['w']" value="1" <?php if ($privilege->write) { print "checked"; }?>/></div>
				</div>
			<?php   } ?>
			<div class="tableRow">
				<div class="tableCell">
					<select name="perm_level" onchange="updateIds(this)">
						<option value="">Select</option>
						<option value="u">User</option>
						<option value="r">Role</option>
						<option value="o">Organization</option>
					</select>
				</div>
				<div class="tableCell">
					<select name="perm_id">
					</select>
				</div>
				<div class="tableCell">
					<input type="checkbox" name="perm_read" value="1" />
				</div>
				<div class="tableCell">
					<input type="checkbox" name="perm_write" value="1" />
				</div>
			</div>
			<div class="form_footer">
				<input type="submit" class="button" name="btn_submit" value="Update" />
				<input type="submit" class="button" name="btn_submit" value="Download" />
			</div>
		</div>
</form>
