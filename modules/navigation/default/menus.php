<script language="Javascript">
	function goTo(target) {
		window.location.href = target;
		return true;
	}
	
	function updateMenu(menuId) {
		// Set the form values for the specific menu
		document.forms[0].id.value = menuId;
		document.forms[0].code.value = document.querySelector('input[name="code_' + menuId + '"]').value;
		document.forms[0].title.value = document.querySelector('input[name="title_' + menuId + '"]').value;
		document.forms[0].submit();
	}
	
	function confirmDelete(menuCode) {
		if (confirm('Are you sure you want to delete this menu? This action cannot be undone.')) {
			// Add delete functionality if needed
			return true;
		}
		return false;
	}
</script>

<!-- Page Header -->
<?=$page->showAdminPageInfo()?>
<!-- End Page Header -->

<div class="form_instruction">Manage navigation menus and their menu items. Each menu can contain multiple navigation items organized in a hierarchy.</div>

<!-- ============================================== -->
<!-- EXISTING MENUS -->
<!-- ============================================== -->
<h3>Current Menus</h3>
<?php if (isset($menus) && count($menus) > 0) { ?>
<form name="menuForm" action="/_navigation/menus" method="post">
    <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
    <input type="hidden" name="id" value="" />
    <input type="hidden" name="code" value="" />
    <input type="hidden" name="title" value="" />
    <section class="tableBody clean min-tablet">
        <div class="tableRowHeader">
            <div class="tableCell width-20per">Menu Code</div>
            <div class="tableCell width-40per">Menu Title</div>
            <div class="tableCell width-20per">Status</div>
            <div class="tableCell width-20per">Actions</div>
        </div>
        <?php foreach ($menus as $menu) { ?>
        <div class="tableRow">
            <div class="tableCell">
                <input type="text" name="code_<?=$menu->id?>" class="value input width-100per" value="<?= htmlspecialchars($menu->code) ?>" placeholder="Enter menu code" />
            </div>
            <div class="tableCell">
                <input type="text" name="title_<?=$menu->id?>" class="value input width-100per" value="<?= htmlspecialchars($menu->title) ?>" placeholder="Enter menu title" />
            </div>
            <div class="tableCell">
                <span class="value">Active</span>
            </div>
            <div class="tableCell">
                <div class="button-group">
                    <input type="button" name="update_<?=$menu->id?>" value="Update" class="button" onclick="updateMenu(<?=$menu->id?>);" />
                    <input type="button" name="btn_menu" value="Manage Items" class="button secondary" onclick="goTo('/_navigation/items/<?= htmlspecialchars($menu->code) ?>')" />
                </div>
            </div>
        </div>
        <?php } ?>
    </section>
</form>
<?php } else { ?>
<section class="tableBody clean min-tablet">
    <div class="tableRow">
        <div class="tableCell width-100per text-align-center">
            <div class="value">No menus found.</div>
            <div class="label marginTop_10">Create your first menu using the form below.</div>
        </div>
    </div>
</section>
<?php } ?>

<!-- ============================================== -->
<!-- ADD NEW MENU -->
<!-- ============================================== -->
<h3>Add New Menu</h3>
<form name="menuForm" action="/_navigation/menus" method="post">
    <input type="hidden" name="id" value="0" />
    <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
    
    <section class="tableBody clean min-tablet">
        <div class="tableRowHeader">
            <div class="tableCell width-20per">Menu Code</div>
            <div class="tableCell width-40per">Menu Title</div>
            <div class="tableCell width-20per">Instructions</div>
            <div class="tableCell width-20per">Action</div>
        </div>
        <div class="tableRow">
            <div class="tableCell">
                <div class="label">Code</div>
                <input type="text" name="code" class="value input width-100per" value="" placeholder="Enter menu code" />
                <small class="help-text">Unique identifier for the menu</small>
            </div>
            <div class="tableCell">
                <div class="label">Title</div>
                <input type="text" name="title" class="value input width-100per" value="" placeholder="Enter menu title" />
                <small class="help-text">Display name for the menu</small>
            </div>
            <div class="tableCell">
                <div class="label">Next Steps</div>
                <div class="value">
                    <ul style="margin: 0; padding-left: 20px; font-size: 0.9em;">
                        <li>Enter a unique code</li>
                        <li>Add a descriptive title</li>
                        <li>Click "Add Menu" to create</li>
                        <li>Then manage menu items</li>
                    </ul>
                </div>
            </div>
            <div class="tableCell">
                <div class="button-group">
                    <input type="submit" name="btn_submit" value="Update" class="button" />
                </div>
            </div>
        </div>
    </section>
</form>
