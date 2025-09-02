<?=$page->showAdminPageInfo()?>
<form method="post">
	<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
	<span class="label">RMA Number</span>
	<input type="text" name="rma_number" value="<?=isset($_REQUEST["rma_number"]) ? htmlspecialchars($_REQUEST["rma_number"]) : ''?>" placeholder="1234" />
	<input type="submit" name="btn_submit" value="Find" />
</form>