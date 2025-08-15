<script language="Javascript">
	function goToVersions() {
		window.location.href = "/_site/tou_versions?tou_id="+<?=$tou->id?>;
		return false;
	}
</script>

<?=$page->showAdminPageInfo()?>
<form method="post" action="/_site/term_of_use">
<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>"/>
<input type="hidden" name="id" value="<?=$tou->id?>"/>
<div class="tableBody">
	<div class="tableRowHeader">
		<div class="tableCell">Name</div>
	</div>
	<div class="tableRow">
		<div class="tableCell"><input type="text" name="name" class="value input site-term-of-use-input-value" value="<?=$tou->name?>" /></div>
	</div>
	<div class="tableRowHeader">
		<div class="tableCell">Description</div>
	</div>
	<div class="tableRow">
		<div class="tableCell"><input type="text" name="description" class="value input site-term-of-use-input-value" value="<?=strip_tags($tou->description)?>" /></div>
	</div>
	<div class="tableRow">
		<div class="tableCell">
			<input type="submit" name="btn_submit" class="button" value="Submit" />
<?php	if ($tou->id > 0) { ?>
			<input type="button" name="btn_versions" class="button" value="Versions" onclick="goToVersions();" />
<?php } ?>
		</div>
	</div>
</div>
</form>
