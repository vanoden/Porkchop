<script language="Javascript">
	function updateConfig(idx) {
		document.forms[0].key.value = document.getElementById('key_'+idx).value;
		document.forms[0].value.value = document.getElementById('value_'+idx).value;
		document.forms[0].todo.value = 'update';
		document.forms[0].submit();
	}
	
	function dropConfig(idx) {
		if (confirm('Are you sure you want to delete this configuration?')) {
			document.forms[0].key.value = document.getElementById('key_'+idx).value;
			document.forms[0].todo.value = 'drop';
			document.forms[0].submit();
		}
	}
	
	function addConfig() {
		var key = document.getElementById('key_').value;
		var value = document.getElementById('value_').value;
		
		if (!key.trim()) {
			alert('Please enter a configuration key');
			return;
		}
		
		document.forms[0].key.value = key;
		document.forms[0].value.value = value;
		document.forms[0].todo.value = 'add';
		document.forms[0].submit();
	}
</script>

<!-- Page Header -->
<?=$page->showAdminPageInfo()?>
<!-- End Page Header -->

<div class="form_instruction">Manage site configuration settings. Configuration keys are read-only and cannot be changed after creation.</div>

<!-- ============================================== -->
<!-- EXISTING CONFIGURATIONS -->
<!-- ============================================== -->
<h3>Current Configurations</h3>
<section class="tableBody clean min-tablet">
    <div class="tableRowHeader">
        <div class="tableCell width-25per">Configuration Key</div>
        <div class="tableCell width-50per">Value</div>
        <div class="tableCell width-25per">Actions</div>
    </div>
    <?php if (isset($configuration) && count($configuration) > 0) {
        $idx = 0;
        foreach ($configuration as $record) {
    ?>
    <div class="tableRow">
        <div class="tableCell">
            <input id="key_<?=$idx?>" class="value input width-100per" type="text" name="key_<?=$idx?>" readonly="readonly" value="<?= htmlspecialchars($record->key) ?>" />
        </div>
        <div class="tableCell">
            <input id="value_<?=$idx?>" type="text" name="value_<?=$idx?>" class="value input width-100per" value="<?= htmlspecialchars($record->value) ?>" placeholder="Enter configuration value" />
        </div>
        <div class="tableCell">
            <div class="button-group">
                <input type="button" name="update_<?=$idx?>" value="Update" class="button" onclick="updateConfig('<?=$idx?>');" />
                <input type="button" name="drop_<?=$idx?>" value="Delete" class="button secondary" onclick="dropConfig('<?=$idx?>');" />
            </div>
        </div>
    </div>
    <?php 
            $idx++;
        }
    } else { ?>
    <div class="tableRow">
        <div class="tableCell width-100per text-align-center">
            <div class="value">No configurations found.</div>
            <div class="label marginTop_10">Add your first configuration using the form below.</div>
        </div>
    </div>
    <?php } ?>
</section>

<!-- ============================================== -->
<!-- ADD NEW CONFIGURATION -->
<!-- ============================================== -->
<h3>Add New Configuration</h3>
<form method="post" action="/_site/configurations">
    <input type="hidden" name="key" value="" />
    <input type="hidden" name="value" value="" />
    <input type="hidden" name="todo" value="" />
    <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
    
    <section class="tableBody clean min-tablet">
        <div class="tableRowHeader">
            <div class="tableCell width-25per">Configuration Key</div>
            <div class="tableCell width-50per">Value</div>
            <div class="tableCell width-25per">Action</div>
        </div>
        <div class="tableRow">
            <div class="tableCell">
                <input type="text" id="key_" name="_key" class="value input width-100per" placeholder="Enter configuration key" />
                <small class="help-text">Key cannot be changed after creation</small>
            </div>
            <div class="tableCell">
                <input type="text" id="value_" name="_value" class="value input width-100per" placeholder="Enter configuration value" />
            </div>
            <div class="tableCell">
                <div class="button-group">
                    <input type="button" name="add" value="Add Configuration" class="button" onclick="addConfig();" />
                </div>
            </div>
        </div>
    </section>
</form>

