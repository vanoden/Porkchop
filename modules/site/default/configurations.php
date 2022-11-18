<script language="Javascript">
	function updateConfig(idx) {
		document.forms[0].key.value = document.getElementById('key_'+idx).value;
		document.forms[0].value.value = document.getElementById('value_'+idx).value;
		document.forms[0].todo.value = 'update';
		document.forms[0].submit();
	}
	
	function dropConfig(idx) {
		document.forms[0].key.value = document.getElementById('key_'+idx).value;
		document.forms[0].todo.value = 'drop';
		document.forms[0].submit();
	}
	
	function addConfig() {
		document.forms[0].key.value = document.getElementById('key_').value;
		document.forms[0].value.value = document.getElementById('value_').value;
		document.forms[0].todo.value = 'add';
		document.forms[0].submit();
	}
</script>
<span class="title">Edit Site Configuration</span>

<?php if ($page->errorCount() > 0) { ?>
<section id="form-message">
	<ul class="connectBorder errorText">
		<li><?=$page->errorString()?></li>
	</ul>
</section>

<?php	} else if ($page->success) { ?>
<section id="form-message">
	<ul class="connectBorder progressText">
		<li><?=$page->success?></li>
	</ul>
</section>
<?php	} ?>

<form method="post" action="/_site/configurations">
    <input type="hidden" name="key" value="" />
    <input type="hidden" name="value" value="" />
    <input type="hidden" name="todo" value="" />
    <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
    <div class="subheading">Manage Site Configurations</div>
    <div class="table">
	    <div class="tableRowHeader">
		    <div class="tableCell">Key</div>
		    <div class="tableCell">Value</div>
		    <div class="tableCell">Actions</div>
	    </div>
    <?php	
        $idx = 0;
	    foreach ($configuration as $record) {
    ?>
	    <div class="tableRow">
		    <div class="tableCell"><input id="key_<?=$idx?>" style="color: #969696;" type="text" name="key_<?=$idx?>" readonly="readonly" value="<?=$record->key?>" /></div>
		    <div class="tableCell"><input id="value_<?=$idx?>" type="text" name="value_<?=$idx?>" value="<?=$record->value?>" /></div>
		    <div class="tableCell">
			    <input type="button" name="update_<?=$idx?>" value="Update" class="button" onclick="updateConfig('<?=$idx?>');" />
			    <input type="button" name="drop_<?=$idx?>" value="Drop" class="button" onclick="dropConfig('<?=$idx?>');" />
		    </div>
	    </div>
    <?php	
		    $idx ++;
	    }
    ?>
	    <div class="tableRow">
		    <div class="tableCell"><input type="text" id="key_" name="_key" class="value input" /></div>
		    <div class="tableCell"><input type="text" id="value_" name="_value" class="value input" /></div>
		    <div class="tableCell"><input type="button" name="add" value="Add" class="button" onclick="addConfig();" /></div>
	    </div>
    </div>
</form>

