<?= $page->showAdminPageInfo(); ?>

<form method="post">
<input type="hidden" name="id" value="<?=$form->id?>" />
<input type="hidden" name="csrf_token" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>" />
<div class="section-grid grid-col-3">
  <div class="form-field">
  <label for="code">Code</label>
  <input type="text" name="code" value="<?=$form->code?>" />
  </div>
  <div class="form-field">
  <label for="title">Title</label>
  <input type="text" name="title" value="<?=$form->title?>" />
  </div>
  <div class="form-field">
  <label for="description">Description</label>
  <input type="text" name="description" value="<?=strip_tags($form->description)?>" />
  </div>
  <div class="form-field">
  <label for="action">Action</label>
  <input type="text" name="action" value="<?=$form->action?>" />
  </div>
  <div class="form-field">
  <label for="method">Method</label>
  <select name="method">
    <option value="post"<?php if ($form->method == "post") print " selected";?>>POST</option>
    <option value="get"<?php if ($form->method == "get") print " selected";?>>GET</option>
  </select>
  </div>
</div>

<button type="submit" name="submit" value="Save">Save</button>

<?php	if ($form->exists()) { ?>
<p class="section">
  <a class="button btn-secondary" href="/_form/admin_submissions/<?= htmlspecialchars((string)$form->code, ENT_QUOTES, 'UTF-8') ?>">View submissions &amp; answers</a>
</p>

  <table class="responsive-table">
    <thead>
      <tr>
        <th scope="col">Version</th>
        <th scope="col">Activated On</th>
        <th scope="col">Activated By</th>
      </tr>
    </thead>
    <tbody>
    <?php if (empty($versions)) { ?>
      <tr>
        <td data-label="Version">No versions yet. Use “Add New Version” below.</td>
        <td data-label="Activated On">—</td>
        <td data-label="Activated By">—</td>
      </tr>
    <?php } else { foreach ($versions as $version) { ?>
      <tr>
        <td data-label="Version">
          <a href="/_form/admin_version/<?=$version->id?>"><?=htmlspecialchars($version->name)?></a>
          <?php if ($version->active()) print " <em>(Published)</em>"; ?>
        </td>
        <td data-label="Activated On">
          <?= $version->date_activated ? htmlspecialchars((string)$version->date_activated) : '—' ?>
        </td>
        <td data-label="Activated By">
          <?= $version->user_id_activated ? htmlspecialchars($version->activatedByDisplayName(), ENT_QUOTES, 'UTF-8') : '—' ?>
        </td>
      </tr>
    <?php } } ?>
    </tbody>
  </table>

<div class="section">
	<a class="button btn-secondary" href="/_form/admin_version/<?=$form->code?>">Add New Version</a>
</div>
<?php } ?>
</form>