<div class="title">Versions</div>
<script language="JavaScript">
   function publish(id) {
       document.forms[0].version_id.value = id;
       document.forms[0].dothis.value = 'publish';
       document.forms[0].code.value = '<?=$package->code?>';
       document.forms[0].submit();
   }
   function hide(id) {
       document.forms[0].version_id.value = id;
       document.forms[0].dothis.value = 'hide';
       document.forms[0].code.value = '<?=$package->code?>';
       document.forms[0].submit();
   }
   function download(id) {
       document.forms[0].version_id.value = id;
       document.forms[0].dothis.value = 'download';
       document.forms[0].code.value = '<?=$package->code?>';
       document.forms[0].submit();
   }
</script>
<?  if ($page->errorCount() > 0) { ?>
<div class="form_error"><?=$page->errorString()?></div>
<?  } ?>
<form name="versionListForm" method="GET" action="/_package/versions">
   <input type="hidden" name="version_id" value="" />
   <input type="hidden" name="dothis" value="" />
   <input type="hidden" name="code" value="" />
</form>
<table class="body">
   <tr>
      <th>Version</th>
      <th>Status</th>
      <th>User</th>
      <th>Date Created</th>
      <th>Date Published</th>
      <th>Repository</th>
      <th>File</th>
      <th>Action</th>
   </tr>
   <?  foreach ($versions as $version) { ?>
   <tr>
      <td><?=$version->version()?></td>
      <td><?=$version->status?></td>
      <td><?=$version->user->full_name()?></td>
      <td><?=$version->date_created?></td>
      <td><?=$version->date_published?></td>
      <td><?=$version->repository->name?></td>
      <td><?=$version->name()?></td>
      <td>
         <? if ($version->status != 'PUBLISHED') { ?><input type="button" name="btn_submit" class="button" value="Publish" onclick="publish(<?=$version->id?>);" /><? } ?>
         <? if ($version->status != 'HIDDEN') { ?><input type="button" name="btn_submit" class="button" value="Hide" onclick="hide(<?=$version->id?>);" /><? } ?>
         <input type="button" name="btn_submit" class="button" value="Download" onclick="download(<?=$version->id?>);" />
      </td>
   </tr>
   <?  } ?>
</table>
<div class="form_footer">
   <input type="button" name="btn_packages" class="button" value="Back" onclick="window.location.href='/_package/package/<?=$package->code?>';" />
</div>
