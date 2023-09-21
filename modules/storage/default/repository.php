<script type="text/javascript">
    function getValue(type) {
        var selectedText = type.options[type.selectedIndex].innerHTML;
        var selectedValue = type.value;
        if (selectedValue == 's3') {
            document.getElementById('s3Settings').style.display = 'block';
        } else {
            document.getElementById('s3Settings').style.display = 'none';
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
        <span class="label">Type</span>
    <?php	 if ($repository->id) { ?>
        <span class="value"><?=$repository->type?></span>
    <?php	 } else { ?>
        <select id="type" name="type" class="value input wide_xl" onchange="getValue(this)">
            <option value="Local" <?php	if ($form['type'] == "local") print " selected"; ?>>Local</option>
            <option value="s3" <?php	if ($form['type'] == "s3") print " selected"; ?>>Amazon S3</option>
            <option value="Drive" <?php	if ($form['type'] == "Drive") print " selected"; ?>>Google Drive</option>
            <option value="DropBox" <?php	if ($form['type'] == "DropBox") print " selected"; ?>>DropBox</option>
        </select>
    <?php	 } ?>
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
        <span class="label">Path</span>
        <input type="text" name="path" class="value input wide_xl" value="<?=$form['path']?>" />
    </div>
    <div class="container">
        <span class="label">Endpoint</span>
        <input type="text" name="endpoint" class="value input wide_xl" value="<?=$form['endpoint']?>" />
    </div>
    
    <div id="s3Settings"<?php if ($form['type'] != "s3") { print "style=\"display:none;\""; } ?>>
        <div class="container" style="margin: 10px; padding: 20px; border:dashed 1px gray; display: inline-table;">
            <h4 style="padding: 0px; margin: 0px;">S3 Configuration</h4>
            <span class="label">Access Key</span>
            <input type="text" name="accessKey" class="value input wide_xl" value="<?=$form['accessKey']?>" />
            
            <span class="label">Secret Key</span>
            <input type="password" name="secretKey" class="value input wide_xl" value="<?=$form['secretKey']?>" />
            
            <span class="label">Bucket</span>
            <input type="text" name="bucket" class="value input wide_xl" value="<?=$form['bucket']?>" />
            
            <span class="label">Region</span>
            <input type="text" name="region" class="value input wide_xl" value="<?=$form['region']?>" />
        </div>
    </div>
	<div class="tableBody clean min-tablet">
		<div class="tableRowHeader">
        	<div class="tableCell" style="width: 25%;">Type</div>
        	<div class="tableCell" style="width: 25%;">ID</div>
        	<div class="tableCell" style="width: 50%;">Permissions</div>
    	</div>
    	<!-- end row header -->
		<?php foreach ($default_privileges as $privilege) { ?>
    	<div class="tableRow">
    		<div class="tableCell">
	            <select name="d_privilege_type[1]">
					<option value="">Select</option>
					<option value="all">All</option>
					<option value="user">User</option>
					<option value="organization">Organization</option>
					<option value="role">Role</option>
				</select>
    		</div>
    		<div class="tableCell">
				<input type="text" name="d_privilege_id[1]" class="value input" />
    		</div>
    		<div class="tableCell">
				r<input type="checkbox" name="d_w[1]" value="1" />
				w<input type="checkbox" value="d_r[1]" value="1" />
				g<input type="checkbox" value="d_g[1]" value="1" />
    		</div>
		</div>
		<?php	} ?>
    	<div class="tableRow">
    		<div class="tableCell">
	            <select name="privilege_type[0]">
					<option value="all">All</option>
					<option value="user">User</option>
					<option value="organization">Organization</option>
					<option value="role">Role</option>
				</select>
    		</div>
    		<div class="tableCell">
				<input type="text" name="privilege_id[0]" class="value input" />
    		</div>
    		<div class="tableCell">
				r<input type="checkbox" name="w[0]" value="1" />
				w<input type="checkbox" value="r[0]" value="1" />
				g<input type="checkbox" value="g[0]" value="1" />
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
