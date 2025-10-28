<script language="JavaScript">
	function showHidden() {
		var organization_id = document.forms[0].organization_id.value;
		window.location.href = "/_register/admin_organization_users/<?=$organization->code?>?organization_id="+organization_id+"&showAllUsers=<?=(isset($_REQUEST['showAllUsers']) && !empty($_REQUEST['showAllUsers'])) ? '0' : '1'?>";
		return true;
	}
</script>

<!-- Page Header -->
<?=$page->showAdminPageInfo()?>
<!-- End Page Header -->

<nav id="breadcrumb">
	<ul>
		<li><a href="/_register/organizations">Organizations</a></li>
		<li><a href="/_register/admin_organization?organization_id=<?=$organization->id?>"><?=$organization->name?></a></li>
		<li>Users</li>
	</ul>
</nav>

<?php $activeTab = 'users'; ?>
<?php
    // Show organization info container similar to product container
    $title = htmlspecialchars($organization->name ?: $organization->code);
?>
<div class="product-container">
    <div class="product-title"><?=$title?></div>
</div>
<?php
?>
<div class="tabs">
    <a href="/_register/admin_organization/<?= $organization->code ?>" class="tab <?= $activeTab==='details'?'active':'' ?>">Details</a>
    <a href="/_register/admin_organization_users/<?= $organization->code ?>" class="tab <?= $activeTab==='users'?'active':'' ?>">Users</a>
    <a href="/_register/admin_organization_tags/<?= $organization->code ?>" class="tab <?= $activeTab==='tags'?'active':'' ?>">Tags</a>
    <a href="/_register/admin_organization_locations/<?= $organization->code ?>" class="tab <?= $activeTab==='locations'?'active':'' ?>">Locations</a>
    <a href="/_register/admin_organization_audit_log/<?= $organization->code ?>" class="tab <?= $activeTab==='audit'?'active':'' ?>">Audit Log</a>
</div>

<form id="orgUsers" name="orgUsers" method="POST">
    <input type="hidden" name="organization_id" value="<?=$organization->id?>"/>
    <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
    
    <div class="form_instruction">Manage users for this organization.</div>

    <div class="user_accounts_container">
        <input type="checkbox" id="showAllUsers" name="showAllUsers" value="showAllUsers" onclick="showHidden()" <?=(isset($_REQUEST['showAllUsers']) && !empty($_REQUEST['showAllUsers'])) ? 'checked' : ''?>> SHOW ALL (Expired/Hidden/Deleted)
        <h3>Current Users</h3>
        <!--	Start First Row-->
        <div class="tableBody">
	        <div class="tableRowHeader">
		        <div class="tableCell value width-20per">Username</div>
		        <div class="tableCell value width-20per">First Name</div>
		        <div class="tableCell value width-20per">Last Name</div>
		        <div class="tableCell value width-10per">Status</div>
		        <div class="tableCell value width-30per">Last Active</div>
	        </div>
        <?php	foreach ($members as $member) { ?>
	        <div class="tableRow member_status_<?=strtolower($member->status)?>">
		        <div class="tableCell">
			        <a href="/_register/admin_account?customer_id=<?=$member->id?>"><?=$member->code?></a>
		        </div>
		        <div class="tableCell">
			        <?=$member->first_name?>
		        </div>
		        <div class="tableCell">
			        <?=$member->last_name?>
		        </div>
		        <div class="tableCell">
			        <?=$member->status?>
		        </div>
		        <div class="tableCell">
			        <?=$member->last_active()?>
		        </div>
	        </div>
        <?php	} ?>
        </div>
        <!--End first row-->

        <h3>Automation Users</h3>
        <!--	Start First Row-->
        <?php	if ($organization->id) { ?>
        <div class="tableBody min-tablet">
	        <div class="tableRowHeader">
		        <div class="tableCell value width-20per">Username</div>
		        <div class="tableCell value width-10per">Status</div>
		        <div class="tableCell value width-30per">Last Active</div>
	        </div>
        <?php	foreach ($automationMembers as $member) { ?>
	        <div class="tableRow member_status_<?=strtolower($member->status)?>">
		        <div class="tableCell">
			        <a href="/_register/admin_account?customer_id=<?=$member->id?>"><?=$member->code?></a>
		        </div>
		        <div class="tableCell">
			        <?=$member->status?>
		        </div>
		        <div class="tableCell">
			        <?=$member->last_active()?>
		        </div>
	        </div>
        <?php	} ?>
        </div>
        <!--End first row-->
        <?php	} ?>
    </div>
		    
    <h3>Add New User</h3>
    <!--	Start First Row-->
    <div class="tableBody">
	    <div class="tableRowHeader">
		    <div class="tableCell width-35per">Username</div>
		    <div class="tableCell width-30per">First Name</div>
		    <div class="tableCell width-35per">Last Name</div>
	    </div>
	    <div class="tableRow">
		    <div class="tableCell"><input type="text" class="width-100per" name="new_login" value="" /></div>
		    <div class="tableCell"><input type="text" class="width-100per" name="new_first_name" value="" /></div>
		    <div class="tableCell"><input type="text" class="width-100per" name="new_last_name" value="" /></div>
	    </div>
    </div>
    <div><input type="submit" name="method" value="Add User" class="button"/></div>
</form>
