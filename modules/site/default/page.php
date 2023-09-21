<script language="Javascript">
	function updateMeta(idx) {
		document.forms[0].key.value = document.getElementById('key_'+idx).value;
		document.forms[0].value.value = document.getElementById('value_'+idx).value;
		document.forms[0].todo.value = 'update';
		document.forms[0].submit();
	}
	function dropMeta(idx) {
		document.forms[0].key.value = document.getElementById('key_'+idx).value;
		document.forms[0].todo.value = 'drop';
		document.forms[0].submit();
	}
	function addMeta() {
		document.forms[0].key.value = document.getElementById('key_').value;
		document.forms[0].value.value = document.getElementById('value_').value;
		document.forms[0].todo.value = 'add';
		document.forms[0].submit();
	}
</script>

<!-- Page Header -->
<?=$page->showAdminPageInfo()?>
<!-- End Page Header -->

<div class="container_narrow">
	<span class="label">Module</span><span class="label"><?=$module?></span>
</div>
<div class="container_narrow">
	<span class="label">View</span><span class="label"><?=$view?></span>
</div>
<div class="container_narrow">
	<span class="label">Index</span><span class="label"><?=$index?></span>
</div>
<form method="post" action="/_site/page">
    <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
    <input type="hidden" name="module" value="<?=$module?>" />
    <input type="hidden" name="view" value="<?=$view?>" />
    <input type="hidden" name="index" value="<?=$index?>" />
    <input type="hidden" name="key" value="" />
    <input type="hidden" name="value" value="" />
    <input type="hidden" name="todo" value="" />
    <div class="subheading">Metadata</div>
    <div class="table">
	    <div class="tableRowHeader">
		    <div class="tableCell">Key</div>
		    <div class="tableCell">Value</div>
		    <div class="tableCell">Actions</div>
	    </div>
    <?php	$idx = 0;
	    foreach ($metadata as $record) { 
    ?>
	    <div class="tableRow">
		    <div class="tableCell"><?=$record->key?><input id="key_<?=$idx?>" type="hidden" name="key_<?=$idx?>" value="<?=$record->key?>" /></div>
		    <div class="tableCell"><input id="value_<?=$idx?>" type="text" name="value_<?=$idx?>" value="<?=$record->value?>" /></div>
		    <div class="tableCell">
			    <input type="button" name="update_<?=$idx?>" value="Update" class="button" onclick="updateMeta('<?=$idx?>');" />
			    <input type="button" name="drop_<?=$idx?>" value="Drop" class="button" onclick="dropMeta('<?=$idx?>');" />
		    </div>
	    </div>
    <?php	
		    $idx ++;
	    }
    ?>
	    <div class="tableRow">
		    <div class="tableCell"><input type="text" id="key_" name="_key" class="value input" /></div>
		    <div class="tableCell"><input type="text" id="value_" name="_value" class="value input" /></div>
		    <div class="tableCell"><input type="button" name="add" value="Add" class="button" onclick="addMeta();" /></div>
	    </div>
    </div>
</form>
