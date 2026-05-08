<script language="JavaScript">
	// remove an organization tag by id
	function removeTagById(id) {
	    document.getElementById('removeTagId').value = id;
	    document.getElementById('orgTags').submit();
	}
</script>

<!-- Page Header -->
<?=$page->showAdminPageInfo()?>
<!-- End Page Header -->

<?php $activeTab = 'tags'; ?>

<div class="tabs">
    <a href="/_register/admin_organization/<?= $organization->code ?>" class="tab <?= $activeTab==='details'?'active':'' ?>">Details</a>
    <a href="/_register/admin_organization_users/<?= $organization->code ?>" class="tab <?= $activeTab==='users'?'active':'' ?>">Users</a>
    <a href="/_register/admin_organization_tags/<?= $organization->code ?>" class="tab <?= $activeTab==='tags'?'active':'' ?>">Tags</a>
    <a href="/_register/admin_organization_locations/<?= $organization->code ?>" class="tab <?= $activeTab==='locations'?'active':'' ?>">Locations</a>
    <a href="/_register/admin_organization_audit_log/<?= $organization->code ?>" class="tab <?= $activeTab==='audit'?'active':'' ?>">Audit Log</a>
	<a href="/_register/admin_organization_plans/<?= $organization->code ?>" class="tab <?= $activeTab==='plans'?'active':'' ?>">Plans</a>
</div>

<form id="orgTags" name="orgTags" method="POST">
    <input type="hidden" name="organization_id" value="<?=$organization->id?>"/>
    <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
    <input type="hidden" id="removeTagId" name="removeTagId" value=""/>

    <?php	if ($organization->id) { ?>
    <div class="tableBody min-tablet">
	    <div class="tableRowHeader">
		    <div class="tableCell width-35per">Tag</div>
	    </div>
        <?php
            if (!empty($organizationTags)) {
                foreach ($organizationTags as $tag) {
        ?>
	        <div class="tableRow">
		        <div class="tableCell">
			        <input type="button" onclick="removeTagById('<?= (int)$tag->xrefId ?>')" name="removeTag" value="Remove" class="button"/> <strong><?= htmlspecialchars($tag->name) ?></strong>
		        </div>
	        </div>
        <?php
                }
            } else {
        ?>
	        <div class="tableRow">
		        <div class="tableCell">No tags assigned to this organization.</div>
	        </div>
        <?php
            }
        ?>
	    
	    <div class="tableRow">
		    <div class="tableCell"><label>New Tag</label><input type="text" class="" name="newTag" value="" /></div>
	    </div>
    </div>
    <div><input type="submit" name="addTag" value="Add Tag" class="button"/></div>
    <?php	} ?>
</form>
