
<!-- Page Header -->
<?=$page->showAdminPageInfo()?>

<form method="post" action="/_site/pages">
  <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>"/>
  <div class="tableBody">
    <div class="tabledRowHeader">
      <div class="tableCell">Module</div>
      <div class="tableCell">View</div>
      <div class="tableCell">Index</div>
      <div class="tableCell">Template</div>
      <div class="tableCell">Metadata</div>
      <div class="tableCell">Sitemap</div>
		  <div class="tableCell">Terms of Use Required</div>
	  </div>
    <?php	foreach ($pages as $page) {
		  $metadata = $page->allMetadata(); ?>
	  <div class="tableRow">
		  <div class="tableCell">
        <label for="module" class="hiddenDesktop">Module</label>
        <a href="/_site/page?module=<?=$page->module()?>&view=<?=$page->view()?>&index=<?=$page->index?>"><?=$page->module()?></a>
      </div>
		  <div class="tableCell">
        <label for="View" class="hiddenDesktop">View</label>
        <?=$page->view?>
      </div>
      <?php		if (!empty($page->index) && ($GLOBALS['_SESSION_']->customer->has_privilege('edit content messages'))) { ?>
		  <div class="tableCell">
        <label for="Index" class="hiddenDesktop">Index</label>
        <a href="/_site/content_block/<?=$page->index?>"><?=$page->index?></a>
      </div>
      <?php		} elseif (!empty($page->index)) { ?>
		  <div class="tableCell">
        <label for="Index" class="hiddenDesktop">Index</label>
        <?=$page->index?>
      </div>
      <?php		} else { ?>
		  <div class="tableCell">&nbsp;</div>
      <?php		} ?>
		  <div class="tableCell">
        <label for="Template" class="hiddenDesktop">Template</label>
        <?=$page->template()?>
      </div>
		  <div class="tableCell">
        <label for="Metadata" class="hiddenDesktop">Metadata</label>
        <span>
        <?php	$first = true;
          foreach ($metadata as $data) {
            if (! $first) print ",";
            $first = false;
        ?>
        <?=$data->key?> = <?=$data->value?>
        <?php	} ?>
        </span>
		  </div>
		  <div class="tableCell">
        <label for="Sitemap" class="hiddenDesktop">Site Map</label>
			  <input type="checkbox" name="sitemap[<?=$page->id?>]" class="value input" value="1"<?php if ($page->sitemap) print " checked";?> />
		  </div>
		  <div class="tableCell">
        <label for="terms of use" class="hiddenDesktop">Terms of Use Req.</label>
			  <select name="tou_id[<?=$page->id?>]" class="value input">
			  	<option value="">None</option>
          <?php	foreach ($terms_of_use as $tou) { ?>
                  <option value="<?=$tou->id?>"<?php if ($page->tou_id == $tou->id) print " selected"; ?>><?=$tou->name?></option>
          <?php	} ?>
        </select>
		  </div>
	  </div>
    <?php	} ?>
  </div>
  <div class="button-bar"><input type="submit" name="button_submit" value="Add Account" class="input button"/></div>
</form>